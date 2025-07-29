<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalVendors = Vendor::count();
        $totalMenus = Menu::count();
        $totalOrders = Order::count();
        
        $activeVendors = Vendor::where('is_active', true)->count();
        $availableMenus = Menu::where('is_available', true)->count();
        
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $onDeliveryOrders = Order::where('status', 'on_delivery')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        
        $unpaidOrders = Order::where('payment_status', 'unpaid')->count();
        $paidOrders = Order::where('payment_status', 'paid')->count();
        
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price');
        
        $dailyRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_price');
        
        $topMenus = OrderItem::select('menu_id', DB::raw('SUM(quantity) as total_quantity'))
            ->with('menu')
            ->groupBy('menu_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
        
        $recentOrders = Order::with(['user', 'orderItems.menu'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $ordersByPaymentStatus = Order::select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();
        
        $dailyOrders = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('content.dashboard.index', compact(
            'totalUsers',
            'totalVendors',
            'totalMenus',
            'totalOrders',
            'activeVendors',
            'availableMenus',
            'pendingOrders',
            'processingOrders',
            'onDeliveryOrders',
            'completedOrders',
            'unpaidOrders',
            'paidOrders',
            'monthlyRevenue',
            'dailyRevenue',
            'topMenus',
            'recentOrders',
            'ordersByStatus',
            'ordersByPaymentStatus',
            'dailyOrders'
        ));
    }
}