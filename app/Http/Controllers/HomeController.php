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
        $services = ServiceOrderItem::SERVICES;
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

        $timezone = ServiceOrder::REGISTRATION_TIMEZONE;
        $today = CarbonImmutable::today($timezone);
        $start = isset($filters['date_from']) ? CarbonImmutable::parse($filters['date_from'], $timezone)->startOfDay() : $today->subMonths(11)->startOfMonth();
        $end = isset($filters['date_to']) ? CarbonImmutable::parse($filters['date_to'], $timezone)->startOfDay() : $today;
        $dates = ['date_from' => $start->toDateString(), 'date_to' => $end->toDateString()];

        $hasCompletedAt = Schema::hasColumn('technical_sheets', 'completed_at');
        $sheetColumns = 'id,service_order_item_id,status,updated_at'.($hasCompletedAt ? ',completed_at' : '');
        $orders = ServiceOrder::query()
            ->registeredDuring($dates['date_from'], $dates['date_to'])
            ->select(['id', 'order_date', 'created_at', 'origin', 'status'])
            ->with(['items:id,service_order_id,services,is_guarantee,repair_price', 'items.technicalSheet:'.$sheetColumns])
            ->lazyById(500);

        $orderCounts = [];
        $monthlyCounts = [];
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
            $month = $order->created_at->setTimezone($timezone)->format('Y-m');
            $monthlyCounts[$month] = ($monthlyCounts[$month] ?? 0) + 1;

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

        $osByMonth = collect();
        for ($month = $start->startOfMonth(); $month <= $end; $month = $month->addMonth()) {
            $osByMonth->push([
                'month' => $month->format('m/Y'),
                'count' => $monthlyCounts[$month->format('Y-m')] ?? 0,
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
            'osByMonth' => $osByMonth,
            'origins' => $origins,
            'warrantyStats' => $warrantyStats,
            'avgDaysByBranch' => $avgDaysByBranch,
            'avgDaysOverall' => array_sum(array_column($branches, 'total_days')) / max(array_sum(array_column($branches, 'total_completed')), 1),
            'fastest' => $avgDaysByBranch->first(),
            'slowest' => $avgDaysByBranch->last(),
            'dates' => $dates,
            'topIncidents' => $topIncidents,
        ]);
    }
}
