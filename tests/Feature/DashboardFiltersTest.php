<?php

namespace Tests\Feature;

use App\Exports\ServiceOrdersExport;
use App\Models\ServiceOrder;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class DashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-27T12:00:00Z'));
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_shows_overall_totals_without_prefilling_the_period(): void
    {
        $this->createOrders();
        $olderOrder = $this->order('2024-09-20', '2024-09-20 15:00:00', 'E-COMMERCE');
        $olderOrder->items()->create(['identified_product_name' => 'Pulseira']);
        $undatedOrder = $this->order('2026-11-03', '2026-11-03 15:00:00', 'SHOWROOM SP');
        $undatedOrder->forceFill(['created_at' => null])->save();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('dates', ['date_from' => null, 'date_to' => null])
            ->assertViewHas('hasPeriod', false)
            ->assertSee('Exibindo os totais gerais.')
            ->assertSee('<canvas id="registrationsChart"', false)
            ->assertSee(route('service-orders.index', ['status' => 'created']))
            ->assertSee(route('service-orders.index', ['status' => 'completed']))
            ->assertViewHas('stats', [
                'total_os' => 6,
                'os_pending' => 4,
                'os_completed' => 2,
                'warranty_rate' => 33.3,
                'avg_ticket' => 340,
            ])
            ->assertViewHas('origins', fn (Collection $origins) => $origins->values()->all() === [2, 1, 1, 1, 1])
            ->assertViewHas('warrantyStats', ['warranty' => 1, 'paid' => 2, 'pending_analysis' => 4])
            ->assertViewHas('identifiedProductTotal', 1)
            ->assertViewHas('registrationSeries', fn (Collection $series) => $series->first() === ['label' => '09/2024', 'count' => 1]
                && $series->last() === ['label' => '11/2026', 'count' => 2]
                && $series->sum('count') === 5);
    }

    public function test_one_registration_period_applies_to_every_dashboard_indicator(): void
    {
        $this->createOrders();
        $dates = ['date_from' => '2026-10-31', 'date_to' => '2026-11-01'];

        $this->get(route('dashboard', $dates))
            ->assertOk()
            ->assertSee('10 maiores incidentes')
            ->assertDontSee('Últimas OS abertas')
            ->assertDontSee('Consultoras com mais OS')
            ->assertViewHas('stats', [
                'total_os' => 2,
                'os_pending' => 1,
                'os_completed' => 1,
                'warranty_rate' => 50,
                'avg_ticket' => 80,
            ])
            ->assertViewHas('warrantyStats', ['warranty' => 1, 'paid' => 1, 'pending_analysis' => 2])
            ->assertViewHas('registrationSeries', fn (Collection $series) => $series->all() === [
                ['label' => '31/10', 'count' => 1], ['label' => '01/11', 'count' => 1],
            ])
            ->assertViewHas('origins', fn (Collection $origins) => $origins->values()->all() === [1, 1, 0, 0, 0])
            ->assertViewHas('avgDaysOverall', 6.5)
            ->assertViewHas('avgDaysByBranch', fn (Collection $branches) => $branches->pluck('origin')->all() === ['SHOWROOM SP'])
            ->assertViewHas('topIncidents', fn (Collection $incidents) => $incidents->pluck('count')->all() === [3, 2]
                && $incidents->keys()->all() === ['S', 'F']);
    }

    public function test_dates_are_the_only_dashboard_filters(): void
    {
        $this->createOrders();
        $dates = ['date_from' => '2026-10-31', 'date_to' => '2026-11-01'];

        $this->get(route('dashboard', $dates + ['service' => 'F']))
            ->assertOk()
            ->assertSee('name="date_from"', false)
            ->assertSee('name="date_to"', false)
            ->assertDontSee('name="service"', false)
            ->assertDontSee('service=F', false)
            ->assertViewHas('stats', fn (array $stats) => $stats['total_os'] === 2 && $stats['os_pending'] === 1)
            ->assertViewHas('warrantyStats', ['warranty' => 1, 'paid' => 1, 'pending_analysis' => 2])
            ->assertViewHas('topIncidents', fn (Collection $incidents) => $incidents->keys()->all() === ['S', 'F']);
    }

    public function test_identified_products_are_ranked_and_counted_with_the_dashboard_period(): void
    {
        $selectedOrder = $this->order('2026-11-10', '2026-11-14 05:00:00', 'SHOWROOM SP');
        $otherOrder = $this->order('2026-11-14', '2026-11-15 03:00:00', 'EVENTOS');

        foreach (['Anel', ' Colar ', 'Colar', 'Brinco', 'Brinco', null, '', " \t "] as $name) {
            $selectedOrder->items()->create(['identified_product_name' => $name, 'services' => ['F']]);
        }
        foreach (['Broche', 'Broche', 'Broche', 'Broche', 'Anel', 'Brinco'] as $name) {
            $otherOrder->items()->create(['identified_product_name' => $name, 'services' => ['B']]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('10 maiores incidentes')
            ->assertSee('Produtos identificados')
            ->assertViewHas('identifiedProductTotal', 11)
            ->assertViewHas('identifiedProducts', fn (Collection $products) => $products->all() === [
                ['label' => 'Broche', 'count' => 4],
                ['label' => 'Brinco', 'count' => 3],
                ['label' => 'Anel', 'count' => 2],
                ['label' => 'Colar', 'count' => 2],
            ]);

        $this->get(route('dashboard', ['date_from' => '2026-11-14', 'date_to' => '2026-11-14']))
            ->assertOk()
            ->assertViewHas('identifiedProductTotal', 5)
            ->assertViewHas('identifiedProducts', fn (Collection $products) => $products->all() === [
                ['label' => 'Brinco', 'count' => 2],
                ['label' => 'Colar', 'count' => 2],
                ['label' => 'Anel', 'count' => 1],
            ])
            ->assertViewHas('topIncidents', fn (Collection $incidents) => $incidents->keys()->all() === ['F']);

        $this->get(route('dashboard', ['date_from' => '2026-11-13', 'date_to' => '2026-11-13']))
            ->assertOk()
            ->assertViewHas('identifiedProductTotal', 0)
            ->assertSee('Nenhum produto identificado.');
    }

    public function test_dashboard_and_linked_lists_include_the_full_registration_dates_in_brasilia(): void
    {
        $completed = $this->order('2026-10-28', '2026-11-01 03:00:00', 'SHOWROOM SP', ServiceOrder::STATUS_COMPLETED);
        $pending = $this->order('2026-10-29', '2026-11-06 02:59:59', 'SHOWROOM SP');
        $before = $this->order('2026-11-01', '2026-11-01 02:59:59', 'SHOWROOM SP', ServiceOrder::STATUS_COMPLETED);
        $after = $this->order('2026-11-05', '2026-11-06 03:00:00', 'SHOWROOM SP');
        foreach ([$completed, $pending, $before, $after] as $order) {
            $order->items()->create(['services' => ['F']]);
        }

        $dates = ['date_from' => '2026-11-01', 'date_to' => '2026-11-05'];
        $registeredDates = ['registered_from' => '2026-11-01', 'registered_to' => '2026-11-05'];
        $completedFilters = $registeredDates + ['status' => 'completed'];
        $pendingFilters = $registeredDates + ['status' => 'created'];

        $this->get(route('dashboard', $dates))
            ->assertOk()
            ->assertSee('Ordens cadastradas por dia')
            ->assertSee(route('service-orders.index', $completedFilters))
            ->assertSee(route('service-orders.index', $pendingFilters))
            ->assertViewHas('registrationSeries', fn (Collection $series) => $series->pluck('count')->all() === [1, 0, 0, 0, 1])
            ->assertViewHas('stats', fn (array $stats) => $stats['total_os'] === 2
                && $stats['os_pending'] === 1
                && $stats['os_completed'] === 1);

        $this->get(route('dashboard', ['date_from' => '2026-11-05', 'date_to' => '2026-11-05']))
            ->assertOk()
            ->assertViewHas('registrationSeries', fn (Collection $series) => $series->all() === [['label' => '05/11', 'count' => 1]])
            ->assertViewHas('stats', fn (array $stats) => $stats['total_os'] === 1 && $stats['os_pending'] === 1);

        $this->get(route('service-orders.index', $completedFilters))
            ->assertOk()
            ->assertViewHas('serviceOrders', fn ($orders) => $orders->getCollection()->modelKeys() === [$completed->id]);
        $this->get(route('service-orders.index', $pendingFilters))
            ->assertOk()
            ->assertSee('Data de cadastro')
            ->assertSee('05/11/2026')
            ->assertViewHas('serviceOrders', fn ($orders) => $orders->getCollection()->modelKeys() === [$pending->id]);
        $this->get(route('service-orders.index', $dates))
            ->assertOk()
            ->assertViewHas('serviceOrders', fn ($orders) => $orders->getCollection()->modelKeys() === [$after->id, $before->id]);

        Excel::fake();
        $this->get(route('service-orders.export', $completedFilters))->assertOk();
        Excel::assertDownloaded('ordens_servico.xlsx', fn (ServiceOrdersExport $export) => $export->collection()
            ->pluck('N° OS')->all() === [$completed->order_number]);
    }

    public function test_monthly_chart_skips_empty_future_months_and_compares_only_complete_months(): void
    {
        foreach (['2026-09-02', '2026-09-10', '2026-10-01', '2026-10-15', '2026-10-30', '2026-11-05'] as $date) {
            $this->order($date, $date.' 15:00:00', 'SHOWROOM SP');
        }

        $this->get(route('dashboard', ['date_from' => '2026-09-01', 'date_to' => '2026-12-01']))
            ->assertOk()
            ->assertSee('Ordens cadastradas por mês')
            ->assertSee('dash-kpi-trend')
            ->assertSee('10/2026 vs. 09/2026')
            ->assertViewHas('registrationSeries', fn (Collection $series) => $series->all() === [
                ['label' => '09/2026', 'count' => 2],
                ['label' => '10/2026', 'count' => 3],
                ['label' => '11/2026', 'count' => 1],
            ])
            ->assertViewHas('trend', fn (array $trend) => $trend['value'] === 50.0);

        $this->get(route('dashboard', ['date_from' => '2026-09-02', 'date_to' => '2026-12-01']))
            ->assertOk()
            ->assertDontSee('dash-kpi-trend');
    }

    public function test_invalid_date_ranges_are_rejected(): void
    {
        $dashboard = route('dashboard');

        $this->from($dashboard)->get(route('dashboard', ['date_from' => '2026-11-01']))
            ->assertRedirect($dashboard)
            ->assertSessionHasErrors('date_to');

        $this->from($dashboard)->get(route('dashboard', ['date_from' => '2026-11-02', 'date_to' => '2026-11-01']))
            ->assertRedirect($dashboard)
            ->assertSessionHasErrors('date_to');
    }

    private function createOrders(): void
    {
        $first = $this->order('2026-10-28', '2026-10-31 03:00:00', 'SHOWROOM SP', ServiceOrder::STATUS_COMPLETED);
        $underWarranty = $first->items()->create(['services' => ['F', 'S', 'S'], 'is_guarantee' => true]);
        $underWarranty->technicalSheet()->create(['status' => 'delivered', 'updated_at' => '2026-11-02 15:00:00']);
        $paid = $first->items()->create(['services' => ['F'], 'is_guarantee' => false, 'repair_price' => 80]);
        $paid->technicalSheet()->create(['status' => 'completed', 'updated_at' => '2026-11-05 16:00:00']);

        $last = $this->order('2026-10-30', '2026-11-02 02:59:59', 'SHOWROOM LIMEIRA');
        $last->items()->create(['services' => json_encode(['S'])]);
        $last->items()->create(['services' => ['S']]);

        $before = $this->order('2026-10-31', '2026-10-31 02:59:59', 'SHOWROOM ON-LINE', ServiceOrder::STATUS_COMPLETED);
        $before->forceFill(['updated_at' => '2026-11-01 15:00:00'])->save();
        $outsidePeriod = $before->items()->create(['services' => ['B'], 'is_guarantee' => false, 'repair_price' => 600]);
        $outsidePeriod->technicalSheet()->create(['status' => 'completed', 'updated_at' => '2026-11-01 15:00:00']);
        $this->order('2026-11-01', '2026-11-02 03:00:00', 'EVENTOS')->items()->create(['services' => ['B']]);
    }

    private function order(string $date, string $registeredAt, string $origin, string $status = ServiceOrder::STATUS_CREATED): ServiceOrder
    {
        $order = new ServiceOrder([
            'client_name' => 'Cliente teste',
            'consultant_name' => 'Consultora teste',
            'client_code' => '123',
            'origin' => $origin,
            'order_date' => $date,
            'status' => $status,
        ]);
        $order->created_at = $registeredAt;
        $order->save();

        return $order;
    }
}
