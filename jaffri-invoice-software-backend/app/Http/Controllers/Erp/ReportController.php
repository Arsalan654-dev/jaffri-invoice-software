<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\CashEntry;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $monthly = Invoice::where('created_at', '>=', Carbon::now()->startOfMonth()->subMonths(11))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month => (float) $row->revenue]);

        $expenseMonthly = PurchaseOrder::where('created_at', '>=', Carbon::now()->startOfMonth()->subMonths(11))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month => (float) $row->expense]);

        $labels = [];
        $revenues = [];
        $expenses = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::parse($m)->format('M Y');
            $revenues[] = $monthly->get($m, 0);
            $expenses[] = $expenseMonthly->get($m, 0);
        }

        $invoiceStatus = Invoice::selectRaw('status, COUNT(*) as count, SUM(total) as total')
            ->groupBy('status')
            ->get();

        return view('erp.reports.index', compact('labels', 'revenues', 'expenses', 'invoiceStatus'));
    }

    public function analyticsData()
    {
        $monthly = Invoice::where('created_at', '>=', Carbon::now()->startOfMonth()->subMonths(11))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue")
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month => (float) $row->revenue]);

        $expenseMonthly = PurchaseOrder::where('created_at', '>=', Carbon::now()->startOfMonth()->subMonths(11))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as expense")
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month => (float) $row->expense]);

        $labels = [];
        $revenues = [];
        $expenses = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::parse($m)->format('M Y');
            $revenues[] = $monthly->get($m, 0);
            $expenses[] = $expenseMonthly->get($m, 0);
        }

        $statusCounts = Invoice::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();

        return response()->json([
            'labels' => $labels,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'status' => $statusCounts,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function salesReport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->toDateString();

        $base = Invoice::with('customer')
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);

        if ($request->filled('search')) {
            $search = $request->search;
            $base->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'total_invoices' => (clone $base)->count(),
            'total_amount' => (clone $base)->sum('total'),
            'total_paid' => (clone $base)->where('status', 'paid')->sum('total'),
            'total_pending' => (clone $base)->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'total_draft' => (clone $base)->where('status', 'draft')->sum('total'),
        ];

        $pageTotals = [
            'subtotal' => (clone $base)->sum('subtotal'),
            'tax_amount' => (clone $base)->sum('tax_amount'),
            'total' => (clone $base)->sum('total'),
        ];

        $invoices = $base->latest('created_at')->paginate(15)->withQueryString();

        return view('erp.reports.sales', compact('invoices', 'summary', 'pageTotals', 'dateFrom', 'dateTo'));
    }

    public function purchaseReport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->toDateString();

        $base = PurchaseOrder::with('vendor')
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);

        if ($request->filled('search')) {
            $search = $request->search;
            $base->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($vq) use ($search) {
                        $vq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'total_orders' => (clone $base)->count(),
            'total_amount' => (clone $base)->sum('total'),
            'total_received' => 0,
        ];

        $pageTotals = [
            'subtotal' => (clone $base)->sum('subtotal'),
            'tax_amount' => (clone $base)->sum('tax_amount'),
            'total' => (clone $base)->sum('total'),
        ];

        $purchaseOrders = $base->latest('created_at')->paginate(15)->withQueryString();

        return view('erp.reports.purchases', compact('purchaseOrders', 'summary', 'pageTotals', 'dateFrom', 'dateTo'));
    }

    public function profitLoss(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->endOfYear()->toDateString();

        $revenue = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
            ->sum('total');

        $expenses = PurchaseOrder::where('status', 'received')
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
            ->sum('total');

        $profit = $revenue - $expenses;
        $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

        $monthlyProfit = Invoice::whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($row) {
                $monthExpenses = PurchaseOrder::where('status', 'received')
                    ->whereBetween('created_at', [
                        Carbon::parse($row->month)->startOfMonth(),
                        Carbon::parse($row->month)->endOfMonth(),
                    ])
                    ->sum('total');
                $row->expenses = $monthExpenses;
                $row->profit = $row->revenue - $monthExpenses;

                return $row;
            });

        return view('erp.reports.profit-loss', compact('revenue', 'expenses', 'profit', 'margin', 'dateFrom', 'dateTo', 'monthlyProfit'));
    }

    public function pnlPdf()
    {
        $revenue = Invoice::where('status', 'paid')->sum('total');
        $expenses = PurchaseOrder::where('status', 'received')->sum('total');
        $profit = $revenue - $expenses;
        $cashIn = CashEntry::where('type', 'income')->sum('amount');
        $cashOut = CashEntry::where('type', 'expense')->sum('amount');

        $data = compact('revenue', 'expenses', 'profit', 'cashIn', 'cashOut');
        $pdf = Pdf::loadView('erp.reports.pdfs.pnl', $data);

        return $pdf->download('profit-and-loss.pdf');
    }

    public function balanceSheetPdf()
    {
        $cash = CashEntry::where('type', 'income')->sum('amount') - CashEntry::where('type', 'expense')->sum('amount');
        $receivable = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $payable = PurchaseOrder::whereIn('status', ['draft', 'sent'])->sum('total');
        $totalAssets = max($cash + $receivable, 0);
        $equity = $totalAssets - $payable;

        $data = compact('cash', 'receivable', 'payable', 'totalAssets', 'equity');
        $pdf = Pdf::loadView('erp.reports.pdfs.balance-sheet', $data);

        return $pdf->download('balance-sheet.pdf');
    }

    public function agingPdf()
    {
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['sent', 'overdue'])
            ->get()
            ->map(function ($inv) {
                $days = $inv->due_date ? Carbon::parse($inv->due_date)->diffInDays(now()) : 0;

                return [
                    'number' => $inv->invoice_number,
                    'customer' => $inv->customer->company_name ?? 'N/A',
                    'date' => $inv->created_at->format('M d, Y'),
                    'due' => $inv->due_date ? $inv->due_date->format('M d, Y') : '—',
                    'aging' => $days.' days',
                    'amount' => $inv->total - ($inv->amount_paid ?? 0),
                ];
            });

        $totalDue = $invoices->sum('amount');
        $data = compact('invoices', 'totalDue');
        $pdf = Pdf::loadView('erp.reports.pdfs.aging', $data);

        return $pdf->download('ar-aging.pdf');
    }

    public function inventoryValuationPdf()
    {
        $items = InventoryItem::all()->map(function ($item) {
            return [
                'name' => $item->name,
                'sku' => $item->sku,
                'qty' => $item->quantity,
                'price' => $item->purchase_price,
                'value' => $item->quantity * $item->purchase_price,
            ];
        });

        $totalValue = $items->sum('value');
        $data = compact('items', 'totalValue');
        $pdf = Pdf::loadView('erp.reports.pdfs.inventory-valuation', $data);

        return $pdf->download('inventory-valuation.pdf');
    }
}
