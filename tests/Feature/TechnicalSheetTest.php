<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\TechnicalSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalSheetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    private function admin(): User
    {
        return User::factory()->create(['type' => 'admin']);
    }

    private function item(?ServiceOrder $order = null): ServiceOrderItem
    {
        $order ??= ServiceOrder::create([
            'client_name' => 'Cliente teste', 'consultant_name' => 'Consultora',
            'client_code' => '123', 'origin' => 'SHOWROOM SP', 'order_date' => '2026-10-26',
        ]);

        return $order->items()->create([
            'identified_product_name' => 'Anel teste', 'services' => ['B'], 'coating' => 'DO',
        ]);
    }

    private function act(ServiceOrderItem $item, string $action, array $extra = [])
    {
        return $this->patch(route('technical-sheets.update', $item), $extra + [
            'action' => $action,
            'version' => $item->technicalSheet()->value('version') ?? 0,
            'notes' => 'Confirmação de teste / referência 123',
        ]);
    }

    private function triage(ServiceOrderItem $item, array $extra = [])
    {
        return $this->act($item, 'triage', $extra + [
            'is_guarantee' => '1', 'treatment' => 'assembly', 'diagnosis' => 'Requer montagem',
            'changes_coating' => '0', 'changes_color' => '0',
        ]);
    }

    public function test_existing_pieces_appear_without_catalog_or_persisted_sheet(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin())->get(route('technical-sheets.index'))
            ->assertOk()->assertSee('Anel teste')->assertSee('Aguardando triagem');
        $this->get(route('technical-sheets.show', $item))->assertOk()->assertSee('Diagnóstico técnico');
        $this->assertDatabaseCount('technical_sheets', 0);
        $this->get(route('service-orders.show', $item->serviceOrder))->assertOk()
            ->assertSee('Status da peça')->assertDontSee('analysisModal')->assertDontSee('Salvar Análise');
    }

    public function test_customer_must_approve_paid_work_and_os_waits_for_every_piece(): void
    {
        $item = $this->item();
        $other = $this->item($item->serviceOrder);
        $this->actingAs($this->admin());
        $this->triage($item, ['is_guarantee' => '0', 'repair_price' => '25.50'])->assertSessionHasNoErrors();
        $this->assertSame('awaiting_customer', $item->technicalSheet()->first()->status);
        $this->act($item, 'start_service')->assertSessionHasErrors('action');
        $this->act($item, 'approve')->assertSessionHasNoErrors();
        $this->act($item, 'start_service')->assertSessionHasNoErrors();
        $this->act($item, 'finish_service')->assertSessionHasNoErrors();
        $this->act($item, 'pass')->assertSessionHasNoErrors();
        $this->assertSame('under_analysis', $item->serviceOrder->fresh()->status);
        $this->triage($other)->assertSessionHasNoErrors();
        foreach (['start_service', 'finish_service', 'pass'] as $action) {
            $this->act($other, $action)->assertSessionHasNoErrors();
        }
        $this->assertSame('completed', $item->serviceOrder->fresh()->status);
        $this->act($item, 'send_store')->assertSessionHasNoErrors();
        $this->act($item, 'deliver')->assertSessionHasNoErrors();
        $this->assertSame('delivered', $item->technicalSheet()->first()->status);
        $this->assertDatabaseHas('technical_sheet_events', ['action' => 'approve', 'notes' => 'Confirmação de teste / referência 123']);
    }

    public function test_bath_return_and_quality_rejection_require_another_service_cycle(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin());
        $this->triage($item, ['treatment' => 'bath'])->assertSessionHasNoErrors();
        foreach (['start_service', 'send_out', 'receive'] as $action) {
            $this->act($item, $action)->assertSessionHasNoErrors();
        }
        $this->act($item, 'rework', ['notes' => ''])->assertSessionHasErrors('action');
        $this->act($item, 'rework')->assertSessionHasNoErrors();
        $this->assertSame('ready_for_service', $item->technicalSheet()->first()->status);
        $this->act($item, 'pass')->assertSessionHasErrors('action');
        foreach (['start_service', 'send_out', 'receive', 'pass'] as $action) {
            $this->act($item, $action)->assertSessionHasNoErrors();
        }
        $this->assertSame('completed', $item->serviceOrder->fresh()->status);
    }

    public function test_exchange_and_credit_require_manual_confirmation(): void
    {
        $item = $this->item();
        $credit = $this->item($item->serviceOrder);
        $this->actingAs($this->admin());
        $this->triage($item, ['treatment' => 'exchange'])->assertSessionHasNoErrors();
        $this->act($item, 'exchange', ['notes' => ''])->assertSessionHasErrors('action');
        $this->act($item, 'exchange')->assertSessionHasNoErrors();
        $this->assertSame('inspection', $item->technicalSheet()->first()->status);
        $this->act($item, 'pass')->assertSessionHasNoErrors();
        $this->triage($credit, ['treatment' => 'credit'])->assertSessionHasNoErrors();
        $this->assertSame('credit_pending', $credit->technicalSheet()->first()->status);
        $this->act($credit, 'confirm_credit')->assertSessionHasNoErrors();
        $this->assertSame('GCG', $credit->fresh()->technical_status);
        $this->assertSame('completed', $credit->serviceOrder->fresh()->status);
    }

    public function test_return_without_repair_skips_service_and_can_only_be_finalized_or_retriaged(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin());

        $this->triage($item, ['is_guarantee' => '0', 'treatment' => 'return_without_repair', 'repair_price' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertSame('return_pending', $item->technicalSheet()->first()->status);
        $this->act($item, 'start_service')->assertSessionHasErrors('action');
        $this->act($item, 'complete_return', ['notes' => ''])->assertSessionHasErrors('action');
        $this->act($item, 'revise_triage', ['notes' => 'Decisão corrigida'])->assertSessionHasNoErrors();
        $this->assertSame('in_analysis', $item->technicalSheet()->first()->status);

        $this->triage($item, ['is_guarantee' => '0', 'treatment' => 'return_without_repair', 'repair_price' => '0'])
            ->assertSessionHasNoErrors();
        $this->act($item, 'complete_return', ['notes' => 'Devolução sem reparo confirmada'])->assertSessionHasNoErrors();
        $this->assertSame('completed', $item->technicalSheet()->first()->status);
        $this->assertSame('SC', $item->fresh()->technical_status);
    }

    public function test_access_and_stale_updates_are_blocked(): void
    {
        $item = $this->item();
        $this->get(route('technical-sheets.index'))->assertRedirect(route('login'));
        $viewer = User::factory()->create(['type' => 'user']);
        $this->actingAs($viewer)->get(route('technical-sheets.show', $item))->assertOk()->assertDontSee('Concluir triagem');
        $this->act($item, 'start_analysis')->assertForbidden();
        $this->actingAs($this->admin());
        $this->act($item, 'start_analysis')->assertSessionHasNoErrors();
        $this->triage($item, ['version' => 0])->assertSessionHasErrors('action');
        $this->assertSame('in_analysis', $item->technicalSheet()->first()->status);
        $this->assertDatabaseCount('technical_sheet_events', 1);
    }

    public function test_invalid_triage_cannot_create_partial_sheet(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin());
        $this->triage($item, ['is_guarantee' => '0'])->assertSessionHasErrors('repair_price');
        $this->triage($item, ['changes_coating' => '1'])->assertSessionHasErrors('new_finish');
        $this->triage($item, ['changes_color' => '1'])->assertSessionHasErrors('new_color');
        $this->triage($item, ['is_guarantee' => '0', 'repair_price' => 12, 'treatment' => 'credit'])->assertSessionHasErrors('action');
        $this->assertDatabaseCount('technical_sheets', 0);

        $itemWithAlteration = $this->item();
        $this->triage($itemWithAlteration, [
            'changes_coating' => '1', 'new_finish' => 'matte',
            'changes_color' => '1', 'new_color' => 'golden',
        ])->assertSessionHasNoErrors();
        $this->assertSame('matte', $itemWithAlteration->technicalSheet()->first()->new_finish);
        $this->assertSame('golden', $itemWithAlteration->technicalSheet()->first()->new_color);
    }

    public function test_decline_cancellation_and_legacy_routes_cannot_bypass_workflow(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin());
        $this->triage($item, ['is_guarantee' => '0', 'repair_price' => 10])->assertSessionHasNoErrors();
        $this->act($item, 'decline')->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $item->technicalSheet()->first()->status);
        $this->post(route('service-orders.save-analysis', $item->serviceOrder), [])->assertStatus(410);
        $this->delete(route('service-orders.destroy', $item->serviceOrder))->assertSessionHas('error');
        $this->assertDatabaseHas('service_orders', ['id' => $item->service_order_id]);
        $item->serviceOrder->update(['status' => 'cancelled']);
        $this->act($item, 'start_analysis')->assertSessionHasErrors('action');
    }

    public function test_filters_include_unstarted_pieces_and_do_not_leak_other_orders(): void
    {
        $item = $this->item();
        $other = $this->item();
        $other->update(['identified_product_name' => 'Brinco exclusivo']);
        $this->actingAs($this->admin());
        $this->triage($item)->assertSessionHasNoErrors();
        $this->get(route('technical-sheets.index', ['status' => 'pending']))->assertOk()->assertSee('Brinco exclusivo')->assertDontSee('Anel teste');
        $this->get(route('technical-sheets.index', ['order_number' => $item->serviceOrder->order_number]))->assertOk()->assertSee('Anel teste')->assertDontSee('Brinco exclusivo');
        $this->get(route('technical-sheets.show', $item))->assertOk()->assertSee('Histórico da ficha')->assertSee('Requer montagem');
    }

    public function test_no_stock_allows_retriage_and_preserves_original_decision_in_history(): void
    {
        $item = $this->item();
        $this->actingAs($this->admin());
        $this->triage($item, ['treatment' => 'exchange'])->assertSessionHasNoErrors();
        $this->act($item, 'revise_triage', ['notes' => 'Sem estoque'])->assertSessionHasNoErrors();
        $this->triage($item, ['treatment' => 'credit'])->assertSessionHasNoErrors();
        $this->assertSame('credit_pending', $item->technicalSheet()->first()->status);
        $events = $item->technicalSheet()->first()->events()->where('action', 'triage')->orderBy('id')->get();
        $this->assertSame('exchange', $events->last()->details['treatment']);
        $this->assertDatabaseHas('technical_sheet_events', ['action' => 'revise_triage', 'notes' => 'Sem estoque']);
    }
}
