<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_os' => ServiceOrder::count(),
            'os_pending' => ServiceOrder::where('status', 'pending')->count(),
            'os_in_progress' => ServiceOrder::where('status', 'in_progress')->count(),
            'os_completed' => ServiceOrder::where('status', 'completed')->count(),
            'os_cancelled' => ServiceOrder::where('status', 'cancelled')->count(),
            'total_users' => User::count(),
        ];

        // OS Recentes (últimas 5)
        $recentOs = ServiceOrder::with('items')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentOs'));
    }
}
