<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('vendor');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($vq) use ($search) {
                        $vq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $purchaseOrders = $query->latest()->paginate(15)->withQueryString();
        $vendors = Vendor::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.purchase-orders.index', compact('purchaseOrders', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.purchase-orders.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'date' => ['nullable', 'date'],
            'expected_delivery' => ['nullable', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
            }

            $total = $subtotal;

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(),
                'vendor_id' => $validated['vendor_id'],
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total' => $total,
                'status' => 'draft',
                'order_date' => $validated['date'] ?? now()->toDateString(),
                'expected_delivery' => $validated['expected_delivery'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];

                $purchaseOrder->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('erp.purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items', 'creator']);

        return view('erp.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot edit a received or cancelled purchase order.');
        }

        $purchaseOrder->load('items');
        $vendors = Vendor::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.purchase-orders.edit', compact('purchaseOrder', 'vendors'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot update a received or cancelled purchase order.');
        }

        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'date' => ['nullable', 'date'],
            'expected_delivery' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
            }

            $purchaseOrder->update([
                'vendor_id' => $validated['vendor_id'],
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'order_date' => $validated['date'] ?? $purchaseOrder->order_date,
                'expected_delivery' => $validated['expected_delivery'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];

                $purchaseOrder->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('erp.purchase-orders.index')
            ->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Cannot delete a received purchase order.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
        });

        return redirect()->route('erp.purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items', 'creator']);

        $fields = [
            'PO Number' => e($purchaseOrder->po_number),
            'Date' => $purchaseOrder->order_date ? $purchaseOrder->order_date->format('M d, Y') : '—',
            'Expected Delivery' => $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('M d, Y') : '—',
            'Vendor' => e($purchaseOrder->vendor->company_name ?? 'N/A'),
            'Created By' => e($purchaseOrder->creator->name ?? '—'),
            'Status' => ucfirst($purchaseOrder->status ?? 'pending'),
        ];

        $items = $purchaseOrder->items->map(fn ($item) => [
            ['value' => e($item->description)],
            ['value' => $item->quantity, 'right' => true],
            ['value' => 'Rs. '.number_format($item->unit_price, 2), 'right' => true],
            ['value' => 'Rs. '.number_format($item->total, 2), 'right' => true],
        ]);

        $totals = [
            'Subtotal' => 'Rs. '.number_format($purchaseOrder->subtotal ?? 0, 2),
        ];
        if (($purchaseOrder->tax_amount ?? 0) > 0) {
            $totals['Tax'] = 'Rs. '.number_format($purchaseOrder->tax_amount, 2);
        }
        $totals['Total'] = ['value' => 'Rs. '.number_format($purchaseOrder->total ?? 0, 2), 'grand' => true];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Purchase Order',
            'reference' => $purchaseOrder->po_number,
            'status' => ucfirst($purchaseOrder->status ?? 'pending'),
            'fields' => $fields,
            'items' => $items,
            'itemColumns' => ['Description', 'Qty', 'Unit Price', 'Amount'],
            'totals' => $totals,
            'notes' => $purchaseOrder->notes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($purchaseOrder->po_number.'.pdf');
    }

    private function generatePONumber(): string
    {
        $lastPO = PurchaseOrder::latest('id')->first();
        $number = $lastPO ? intval(substr($lastPO->po_number, 3)) + 1 : 1;

        return 'PO-'.str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
