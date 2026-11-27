<?php

namespace Tests\Feature;

use App\Exports\ServiceOrdersExport;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ServiceOrderIndexStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_finalized_analysis_filter_combines_with_dates_and_survives_pagination(): void
    {
        $first = $this->order(ServiceOrder::STATUS_APPROVED, '2026-11-02');
        $second = $this->order(ServiceOrder::STATUS_APPROVED, '2026-11-03');
        $this->order(ServiceOrder::STATUS_APPROVED, '2026-10-30');
        $this->order(ServiceOrder::STATUS_APPROVED, '2026-11-03', 'EVENTOS');
        $this->order(ServiceOrder::STATUS_COMPLETED, '2026-11-02');
        $this->order(ServiceOrder::STATUS_UNDER_ANALYSIS, '2026-11-02');

        $filters = [
            'status' => ServiceOrder::STATUS_APPROVED,
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-05',
            'origin' => 'SHOWROOM SP',
            'sort' => 'order_date',
            'direction' => 'asc',
            'per_page' => 1,
        ];
        $secondPage = route('service-orders.index', $filters + ['page' => 2]);

        $this->get(route('service-orders.index', $filters))
            ->assertOk()
            ->assertSee('value="approved" selected', false)
            ->assertSee('<strong>Análise finalizada</strong>', false)
            ->assertSee($secondPage)
            ->assertViewHas('serviceOrders', fn ($orders) => $orders->total() === 2
                && $orders->getCollection()->modelKeys() === [$first->id]);

        $this->get($secondPage)
            ->assertOk()
            ->assertSee('value="approved" selected', false)
            ->assertViewHas('serviceOrders', fn ($orders) => $orders->getCollection()->modelKeys() === [$second->id]);
    }

    public function test_status_filters_match_current_and_legacy_orders(): void
    {
        $orders = [];
        foreach ([
            ServiceOrder::STATUS_CREATED,
            'pending',
            ServiceOrder::STATUS_UNDER_ANALYSIS,
            ServiceOrder::STATUS_LEGACY_IN_PROGRESS,
            ServiceOrder::STATUS_APPROVED,
            ServiceOrder::STATUS_COMPLETED,
            ServiceOrder::STATUS_CANCELLED,
        ] as $status) {
            $orders[$status] = $this->order($status);
        }

        $this->get(route('service-orders.index', ['status' => '']))
            ->assertOk()
            ->assertViewHas('serviceOrders', fn ($page) => $page->total() === count($orders));

        $inServiceIds = [$orders[ServiceOrder::STATUS_UNDER_ANALYSIS]->id, $orders[ServiceOrder::STATUS_LEGACY_IN_PROGRESS]->id];

        foreach ($orders as $status => $order) {
            $expectedIds = in_array($order->id, $inServiceIds, true) ? $inServiceIds : [$order->id];

            $this->get(route('service-orders.index', ['status' => $status, 'sort' => 'order_number', 'direction' => 'asc']))
                ->assertOk()
                ->assertViewHas('serviceOrders', fn ($page) => $page->getCollection()->modelKeys() === $expectedIds);
        }

        $this->get(route('service-orders.index', ['status' => ServiceOrder::STATUS_LEGACY_IN_PROGRESS]))
            ->assertOk()
            ->assertSee('value="under_analysis" selected', false)
            ->assertSee('<strong>Em atendimento</strong>', false);
    }

    public function test_export_respects_finalized_analysis_filter_without_dates(): void
    {
        Excel::fake();
        $approved = $this->order(ServiceOrder::STATUS_APPROVED);
        $approved->items()->create(['identified_product_name' => 'Anel']);
        $this->order(ServiceOrder::STATUS_COMPLETED)->items()->create(['identified_product_name' => 'Colar']);

        $this->get(route('service-orders.export', ['status' => ServiceOrder::STATUS_APPROVED]))->assertOk();

        Excel::assertDownloaded('ordens_servico.xlsx', fn (ServiceOrdersExport $export) => $export->collection()
            ->pluck('N° OS')->all() === [$approved->order_number]);
    }

    public function test_index_and_export_validate_the_status_filter(): void
    {
        $this->getJson(route('service-orders.index', ['status' => 'status-desconhecido']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->getJson(route('service-orders.export', ['status' => ['approved', 'completed']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    private function order(string $status, string $date = '2026-11-02', string $origin = 'SHOWROOM SP'): ServiceOrder
    {
        return ServiceOrder::create([
            'client_name' => 'Cliente teste',
            'consultant_name' => 'Consultora teste',
            'client_code' => '123',
            'origin' => $origin,
            'order_date' => $date,
            'status' => $status,
        ]);
    }
}
