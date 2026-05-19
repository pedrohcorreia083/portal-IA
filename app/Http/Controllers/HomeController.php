<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
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

        // OS por mês (últimos 12 meses)
        $osByMonth = ServiceOrder::selectRaw("
            DATE_FORMAT(created_at, '%b/%Y') as month,
            DATE_FORMAT(created_at, '%Y-%m') as month_order,
            COUNT(*) as count
        ")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'month_order')
            ->orderBy('month_order')
            ->get();

        // Origem das OS
        $origins = [
            'showroom_sp' => ServiceOrder::where('origin', 'Showroom SP')->count(),
            'limeira' => ServiceOrder::where('origin', 'Limeira')->count(),
            'online' => ServiceOrder::where('origin', 'Online')->count(),
            'ecommerce' => ServiceOrder::where('origin', 'Ecommerce')->count(),
            'events' => ServiceOrder::where('origin', 'Eventos')->count(),
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
        $recentOs = ServiceOrder::with('items')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'osByMonth',
            'origins',
            'topConsultants',
            'warrantyStats',
            'recentOs'
        ));
    }
}
