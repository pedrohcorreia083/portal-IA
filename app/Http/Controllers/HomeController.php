<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['bail', 'nullable', 'required_with:date_to', 'date_format:Y-m-d'],
            'date_to' => ['bail', 'nullable', 'required_with:date_from', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ], [
            'date_from.required_with' => 'Informe a data inicial.',
            'date_from.date_format' => 'Informe uma data inicial válida.',
            'date_to.required_with' => 'Informe a data final.',
            'date_to.date_format' => 'Informe uma data final válida.',
            'date_to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ]);

        $dates = [
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ];
        $hasPeriod = isset($dates['date_from'], $dates['date_to']);
        if (! $hasPeriod) {
            return view('dashboard', compact('dates', 'hasPeriod'));
        }

        $timezone = ServiceOrder::REGISTRATION_TIMEZONE;
        $today = CarbonImmutable::today($timezone);
        $start = CarbonImmutable::parse($dates['date_from'], $timezone)->startOfDay();
        $end = CarbonImmutable::parse($dates['date_to'], $timezone)->startOfDay();
        $groupByDay = $start->diffInDays($end) < 31;
        $periodKeyFormat = $groupByDay ? 'Y-m-d' : 'Y-m';
        $periodLabelFormat = $groupByDay ? 'd/m' : 'm/Y';

        $hasCompletedAt = Schema::hasColumn('technical_sheets', 'completed_at');
        $sheetColumns = 'id,service_order_item_id,status,updated_at'.($hasCompletedAt ? ',completed_at' : '');
        $orders = ServiceOrder::query()
            ->registeredDuring($dates['date_from'], $dates['date_to'])
            ->select(['id', 'order_date', 'created_at', 'origin', 'status'])
            ->with(['items:id,service_order_id,services,is_guarantee,repair_price', 'items.technicalSheet:'.$sheetColumns])
            ->lazyById(500);

        $services = ServiceOrderItem::SERVICES;
        $orderCounts = [];
        $registrationCounts = [];
        $originCounts = [];
        $serviceCounts = array_fill_keys(array_keys($services), 0);
        $warrantyStats = ['warranty' => 0, 'paid' => 0, 'pending_analysis' => 0];
        $completedItems = 0;
        $warrantyItems = 0;
        $repairTotal = 0.0;
        $pricedRepairs = 0;
        $branches = [];

        foreach ($orders as $order) {
            $orderCounts[$order->status] = ($orderCounts[$order->status] ?? 0) + 1;
            $originCounts[$order->origin] = ($originCounts[$order->origin] ?? 0) + 1;
            $periodKey = $order->created_at->setTimezone($timezone)->format($periodKeyFormat);
            $registrationCounts[$periodKey] = ($registrationCounts[$periodKey] ?? 0) + 1;

            foreach ($order->items as $item) {
                // The accessor also reads older items whose service array was encoded twice.
                $requestedServices = array_intersect($services, $item->services_names);
                foreach (array_keys($requestedServices) as $code) {
                    $serviceCounts[$code]++;
                }

                $warrantyStats[match ($item->is_guarantee) {
                    true => 'warranty',
                    false => 'paid',
                    null => 'pending_analysis',
                }]++;

                if ($order->status === ServiceOrder::STATUS_COMPLETED) {
                    $completedItems++;
                    $warrantyItems += (int) ($item->is_guarantee === true);

                    if ($item->is_guarantee === false && $item->repair_price !== null) {
                        $repairTotal += (float) $item->repair_price;
                        $pricedRepairs++;
                    }
                }

                $sheet = $item->technicalSheet;
                if ($sheet === null || (! $hasCompletedAt && ! in_array($sheet->status, ['completed', 'sent_to_store', 'delivered'], true))) {
                    continue;
                }

                $completedAt = $hasCompletedAt ? $sheet->completed_at : $sheet->updated_at;
                if ($completedAt === null) {
                    continue;
                }

                $days = (int) $order->order_date->diffInDays(CarbonImmutable::parse($completedAt)->startOfDay());
                $origin = $order->origin;
                $branch = $branches[$origin] ?? [
                    'origin' => $origin,
                    'total_completed' => 0,
                    'total_days' => 0,
                    'min_days' => $days,
                    'max_days' => $days,
                ];
                $branch['total_completed']++;
                $branch['total_days'] += $days;
                $branch['min_days'] = min($branch['min_days'], $days);
                $branch['max_days'] = max($branch['max_days'], $days);
                $branches[$origin] = $branch;
            }
        }

        $stats = [
            'total_os' => array_sum($orderCounts),
            'os_pending' => $orderCounts[ServiceOrder::STATUS_CREATED] ?? 0,
            'os_completed' => $orderCounts[ServiceOrder::STATUS_COMPLETED] ?? 0,
            'warranty_rate' => $completedItems ? round($warrantyItems / $completedItems * 100, 1) : 0,
            'avg_ticket' => $pricedRepairs ? $repairTotal / $pricedRepairs : 0,
        ];

        $registrationSeries = collect();
        $firstPeriod = $groupByDay ? $start : $start->startOfMonth();
        for ($period = $firstPeriod; $period <= $end; $period = $groupByDay ? $period->addDay() : $period->addMonth()) {
            $count = $registrationCounts[$period->format($periodKeyFormat)] ?? 0;
            if ($count === 0 && max($period, $start) > $today) {
                continue;
            }

            $registrationSeries->push([
                'label' => $period->format($periodLabelFormat),
                'count' => $count,
            ]);
        }

        $origins = collect([
            'SHOWROOM SP' => 'Showroom SP',
            'SHOWROOM LIMEIRA' => 'Showroom Limeira',
            'SHOWROOM ON-LINE' => 'On-line',
            'E-COMMERCE' => 'E-commerce',
            'EVENTOS' => 'Eventos',
        ])->mapWithKeys(fn ($label, $origin) => [$label => $originCounts[$origin] ?? 0]);

        $topIncidents = collect($serviceCounts)
            ->filter()
            ->sortDesc()
            ->take(10)
            ->map(fn ($count, $code) => ['label' => $services[$code], 'count' => $count]);

        $avgDaysByBranch = collect($branches)
            ->map(fn ($branch) => (object) [...$branch, 'avg_days' => $branch['total_days'] / $branch['total_completed']])
            ->sortBy('avg_days')
            ->values();

        return view('dashboard', [
            'stats' => $stats,
            'registrationSeries' => $registrationSeries,
            'groupByDay' => $groupByDay,
            'trend' => $groupByDay ? null : $this->monthlyTrend($registrationCounts, $start, min($end, $today->subDay())),
            'origins' => $origins,
            'warrantyStats' => $warrantyStats,
            'avgDaysByBranch' => $avgDaysByBranch,
            'avgDaysOverall' => array_sum(array_column($branches, 'total_days')) / max(array_sum(array_column($branches, 'total_completed')), 1),
            'fastest' => $avgDaysByBranch->first(),
            'slowest' => $avgDaysByBranch->last(),
            'dates' => $dates,
            'hasPeriod' => $hasPeriod,
            'topIncidents' => $topIncidents,
        ]);
    }

    private function monthlyTrend(array $counts, CarbonImmutable $start, CarbonImmutable $lastElapsedDate): ?array
    {
        $month = $lastElapsedDate->startOfMonth();
        if ($lastElapsedDate->day !== $lastElapsedDate->daysInMonth) {
            $month = $month->subMonth();
        }

        $previousMonth = $month->subMonth();
        $previousCount = $counts[$previousMonth->format('Y-m')] ?? 0;
        if ($previousMonth < $start || $previousCount === 0) {
            return null;
        }

        $count = $counts[$month->format('Y-m')] ?? 0;
        $change = ($count - $previousCount) / $previousCount * 100;

        return [
            'value' => round($change, 1),
            'up' => $change >= 0,
            'month' => $month->format('m/Y'),
            'previous_month' => $previousMonth->format('m/Y'),
        ];
    }
}
