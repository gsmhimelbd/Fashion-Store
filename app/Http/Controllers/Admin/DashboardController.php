<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $totalRevenue = Order::where('status', 'delivered')->sum('grand_total');
        $lowStockProducts = Product::where('stock', '<=', 5)->count();

        $recentOrders = Order::with('items')->latest()->take(6)->get();
        $topProducts = Product::orderBy('reviews_count', 'desc')->take(5)->get();

        // Monthly revenue stats
        $monthlyRevenue = Order::where('status', 'delivered')
            ->selectRaw('MONTH(created_at) as month, SUM(grand_total) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'pendingOrders',
            'confirmedOrders',
            'processingOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalRevenue',
            'lowStockProducts',
            'recentOrders',
            'topProducts',
            'monthlyRevenue'
        ));
    }
}
