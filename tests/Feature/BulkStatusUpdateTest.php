<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regressão: a atualização de status em massa precisa respeitar as peças
 * selecionadas e nunca atingir as demais peças da OS.
 */
class BulkStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    private function orderWithTwoCompletedItems(): array
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $this->actingAs($admin);

        $order = ServiceOrder::create([
            'client_name' => 'Cliente teste', 'consultant_name' => 'Consultora',
            'client_code' => '123', 'origin' => 'SHOWROOM SP', 'order_date' => '2026-11-17',
        ]);

        $items = collect(['Anel A', 'Anel B'])->map(function (string $name) use ($order) {
            $item = $order->items()->create([
                'identified_product_name' => $name, 'services' => ['B'], 'coating' => 'DO',
            ]);

            foreach ([
                ['action' => 'triage', 'is_guarantee' => '1', 'treatment' => 'assembly',
                    'diagnosis' => 'Montagem', 'changes_coating' => '0', 'changes_color' => '0'],
                ['action' => 'start_service'],
                ['action' => 'finish_service'],
                ['action' => 'pass'],
            ] as $payload) {
                $this->patch(route('technical-sheets.update', $item), $payload + [
                    'version' => $item->technicalSheet()->value('version') ?? 0,
                    'notes' => 'Teste',
                ])->assertSessionHasNoErrors();
            }

            return $item;
        });

        return [$order, $items];
    }

    public function test_bulk_action_only_touches_the_selected_items(): void
    {
        [$order, $items] = $this->orderWithTwoCompletedItems();

        $this->assertSame('completed', $order->fresh()->status);

        $this->patch(route('service-orders.items.update-status', $order), [
            'action' => 'send_store',
            'item_ids' => [$items[0]->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('sent_to_store', $items[0]->technicalSheet()->first()->status);
        $this->assertSame('completed', $items[1]->technicalSheet()->first()->status);
    }

    public function test_bulk_action_without_selection_does_not_touch_any_item(): void
    {
        [$order, $items] = $this->orderWithTwoCompletedItems();

        $this->patch(route('service-orders.items.update-status', $order), [
            'action' => 'deliver',
        ])->assertSessionHas('error');

        $this->assertSame('completed', $items[0]->technicalSheet()->first()->status);
        $this->assertSame('completed', $items[1]->technicalSheet()->first()->status);
    }
}
