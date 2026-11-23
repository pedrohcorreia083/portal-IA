<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {

        // ─── Tempo médio de atendimento por filial ──────────────────────────────
        $periodDays = request()->integer('period', 90);
        $since = now()->subDays($periodDays);

        $hasCompletedAt = Schema::hasColumn('technical_sheets', 'completed_at');
        $dateColumn = $hasCompletedAt ? 'ts.completed_at' : 'ts.updated_at';

        $query = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'so.id', '=', 'soi.service_order_id')
            // 👇 AQUI ESTÁ A CORREÇÃO: Invertemos a ordem do JOIN
            ->join('technical_sheets as ts', 'ts.service_order_item_id', '=', 'soi.id')
            ->whereNotNull($dateColumn)
            ->where($dateColumn, '>=', $since);

        if (!$hasCompletedAt) {
            $query->whereIn('ts.status', ['completed', 'sent_to_store', 'delivered']);
        }

        $avgDaysByBranch = $query
            ->select(
                'so.origin',
                DB::raw("AVG(DATEDIFF({$dateColumn}, COALESCE(so.order_date, soi.created_at))) as avg_days"),
                DB::raw('COUNT(*) as total_completed'),
                DB::raw("MIN(DATEDIFF({$dateColumn}, COALESCE(so.order_date, soi.created_at))) as min_days"),
                DB::raw("MAX(DATEDIFF({$dateColumn}, COALESCE(so.order_date, soi.created_at))) as max_days")
            )
            ->groupBy('so.origin')
            ->orderBy('avg_days')
            ->get();

        $avgDaysOverall = $avgDaysByBranch->sum(fn($r) => $r->avg_days * $r->total_completed)
            / max($avgDaysByBranch->sum('total_completed'), 1);

        $fastest = $avgDaysByBranch->sortBy('avg_days')->first();
        $slowest = $avgDaysByBranch->sortByDesc('avg_days')->first();

        // Stats básicos
        $totalOs = ServiceOrder::count();
        $osCreated = ServiceOrder::where('status', ServiceOrder::STATUS_CREATED)->count();
        $osUnderAnalysis = ServiceOrder::where('status', ServiceOrder::STATUS_UNDER_ANALYSIS)->count();
        $osApproved = ServiceOrder::where('status', ServiceOrder::STATUS_APPROVED)->count();
        $osInProgress = $osUnderAnalysis + $osApproved;
        $osCompleted = ServiceOrder::where('status', ServiceOrder::STATUS_COMPLETED)->count();
        $osCancelled = ServiceOrder::where('status', ServiceOrder::STATUS_CANCELLED)->count();

        // Finalizadas no mês
        $osCompletedMonth = ServiceOrder::where('status', ServiceOrder::STATUS_COMPLETED)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Taxa de garantia (itens garantia vs total de itens concluídos)
        $totalCompletedItems = ServiceOrderItem::whereHas('serviceOrder', function ($query) {
            $query->where('status', ServiceOrder::STATUS_COMPLETED);
        })->count();

        $guaranteeItems = ServiceOrderItem::whereHas('serviceOrder', function ($query) {
            $query->where('status', ServiceOrder::STATUS_COMPLETED);
        })->where('is_guarantee', true)->count();

        $warrantyRate = $totalCompletedItems > 0
            ? round(($guaranteeItems / $totalCompletedItems) * 100, 1)
            : 0;

        // Ticket médio de reparo (apenas itens pagos de OS concluídas)
        $avgTicket = ServiceOrderItem::whereHas('serviceOrder', function ($query) {
            $query->where('status', ServiceOrder::STATUS_COMPLETED);
        })->where('is_guarantee', false)
            ->avg('repair_price') ?? 0;

        $stats = [
            'total_os' => $totalOs,
            'os_pending' => $osCreated,           // Pendentes = Criadas
            'os_in_progress' => $osInProgress,     // Em andamento = Análise + Aprovadas
            'os_completed_month' => $osCompletedMonth,
            'warranty_rate' => $warrantyRate,
            'avg_ticket' => $avgTicket,
            // Para gráfico de pizza
            'os_analysis' => $osUnderAnalysis,
            'os_repair' => $osApproved,
            'os_completed' => $osCompleted,
            'os_cancelled' => $osCancelled,
        ];

        // OS por mês (últimos 12 meses).
        // Agregação em PHP para não depender de funções específicas de um SGBD
        // (DATE_FORMAT é exclusivo do MySQL e quebrava em SQLite/Postgres).
        // Retorna arrays: a view acessa os itens com colchetes.
        $osByMonth = ServiceOrder::where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn($order) => $order->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn($group, $month) => [
                'month' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M/Y'),
                'month_order' => $month,
                'count' => $group->count(),
            ])
            ->values();

        // Origem das OS
        $origins = [
            'showroom_sp' => ServiceOrder::where('origin', 'SHOWROOM SP')->count(),
            'limeira' => ServiceOrder::where('origin', 'SHOWROOM LIMEIRA')->count(),
            'online' => ServiceOrder::where('origin', 'SHOWROOM ON-LINE')->count(),
            'ecommerce' => ServiceOrder::where('origin', 'E-COMMERCE')->count(),
            'events' => ServiceOrder::where('origin', 'EVENTOS')->count(),
        ];

        // Top consultores
        $topConsultants = ServiceOrder::select('consultant_name', DB::raw('COUNT(*) as total_os'))
            ->whereNotNull('consultant_name')
            ->where('consultant_name', '!=', '')
            ->groupBy('consultant_name')
            ->orderByDesc('total_os')
            ->limit(10)
            ->get();

        // Garantia vs Pago vs Pendente (baseado nos itens)
        $warrantyStats = [
            'warranty' => ServiceOrderItem::where('is_guarantee', true)->count(),
            'paid' => ServiceOrderItem::where('is_guarantee', false)->count(),
            'pending_analysis' => ServiceOrderItem::whereNull('is_guarantee')->count(),
        ];

        // OS recentes (para manter a lista rápida)
        $recentOs = ServiceOrder::withCount('items')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'osByMonth',
            'origins',
            'warrantyStats',
            'avgDaysByBranch',
            'avgDaysOverall',
            'fastest',
            'slowest',
            'periodDays',
            'recentOs',
            'topConsultants',
        ));
    }
}
