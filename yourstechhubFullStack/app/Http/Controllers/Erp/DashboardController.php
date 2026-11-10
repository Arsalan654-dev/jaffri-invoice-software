<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalVendors = Vendor::count();
        $totalInvoices = Invoice::count();
        $pendingInvoices = Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $totalPurchaseOrders = PurchaseOrder::count();
        $pendingPurchaseOrders = PurchaseOrder::whereIn('status', ['draft', 'sent', 'confirmed'])->count();
        $totalInventoryItems = InventoryItem::count();
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'min_stock_level')->count();

        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentPurchaseOrders = PurchaseOrder::with('vendor')
            ->latest()
            ->take(5)
            ->get();

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $overdueInvoices = Invoice::with('customer')
            ->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['sent'])
                    ->where('due_date', '<', Carbon::today());
            })
            ->take(5)
            ->get();

        $overdueCount = Invoice::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['sent'])
                    ->where('due_date', '<', Carbon::today());
            })
            ->count();

        $partialCount = 0; // No partial status in invoice enum

        return view('erp.dashboard.index', compact(
            'totalCustomers',
            'totalVendors',
            'totalInvoices',
            'pendingInvoices',
            'paidInvoices',
            'totalRevenue',
            'totalPurchaseOrders',
            'pendingPurchaseOrders',
            'totalInventoryItems',
            'lowStockItems',
            'recentInvoices',
            'recentPurchaseOrders',
            'monthlyRevenue',
            'overdueInvoices',
            'overdueCount',
            'partialCount',
        ));
    }

    public function analyticsData()
    {
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $pendingInvoices = Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->count();
        $overdueCount = Invoice::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['sent'])
                    ->where('due_date', '<', Carbon::today());
            })
            ->count();

        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($inv) => [
                'invoice_number' => $inv->invoice_number,
                'customer' => $inv->customer->company_name ?? 'N/A',
                'date' => $inv->created_at->format('M d, Y'),
                'status' => ucfirst($inv->status),
                'total' => number_format($inv->total, 2),
            ]);

        return response()->json([
            'total_revenue' => (float) Invoice::where('status', 'paid')->sum('total'),
            'paid_invoices' => $paidInvoices,
            'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['draft', 'sent', 'confirmed'])->count(),
            'total_customers' => Customer::count(),
            'total_inventory_items' => InventoryItem::count(),
            'low_stock_items' => InventoryItem::whereColumn('quantity', '<=', 'min_stock_level')->count(),
            'status' => [
                $paidInvoices,
                $pendingInvoices,
                0,
                $overdueCount,
            ],
            'recent_invoices' => $recentInvoices,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
