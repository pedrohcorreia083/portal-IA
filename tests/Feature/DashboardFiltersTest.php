<?php

namespace Tests\Feature;

use App\Exports\ServiceOrdersExport;
use App\Models\ServiceOrder;
use App\Models\User;
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
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
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
            ->assertViewHas('osByMonth', fn (Collection $months) => $months->all() === [
                ['month' => '10/2026', 'count' => 1], ['month' => '11/2026', 'count' => 1],
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
            ->assertSee(route('service-orders.index', $completedFilters))
            ->assertSee(route('service-orders.index', $pendingFilters))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_os'] === 2
                && $stats['os_pending'] === 1
                && $stats['os_completed'] === 1);

        $this->get(route('dashboard', ['date_from' => '2026-11-05', 'date_to' => '2026-11-05']))
            ->assertOk()
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
