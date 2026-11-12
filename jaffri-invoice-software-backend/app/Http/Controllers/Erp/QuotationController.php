<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.quotations.index', compact('quotations', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.quotations.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxRate = $validated['tax_rate'] ?? 0;
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $total = $subtotal - $discountAmount + $taxAmount;

            $quotation = Quotation::create([
                'quotation_number' => $this->generateQuotationNumber(),
                'customer_id' => $validated['customer_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => 'draft',
                'valid_until' => $validated['valid_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);

                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => 0,
                    'total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('erp.quotations.index')
            ->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'creator']);

        return view('erp.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');
        $customers = Customer::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.quotations.edit', compact('quotation', 'customers'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if (in_array($quotation->status, ['converted', 'rejected', 'expired'])) {
            return back()->with('error', 'Cannot edit this quotation.');
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'valid_until' => ['nullable', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $quotation) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxRate = $validated['tax_rate'] ?? 0;
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $total = $subtotal - $discountAmount + $taxAmount;

            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'valid_until' => $validated['valid_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $quotation->items()->delete();

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);

                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => 0,
                    'total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('erp.quotations.index')
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted quotation.');
        }

        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });

        return redirect()->route('erp.quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    public function send(Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return back()->with('error', 'Only draft quotations can be sent.');
        }

        $quotation->update(['status' => 'sent']);

        return back()->with('success', 'Quotation marked as sent.');
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if (! in_array($quotation->status, ['draft', 'sent', 'accepted'])) {
            return back()->with('error', 'Only draft, sent, or accepted quotations can be converted.');
        }

        DB::transaction(function () use ($quotation) {
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_rate' => $quotation->tax_rate,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'status' => 'draft',
                'notes' => $quotation->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($quotation->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax' => $item->tax,
                    'total' => $item->total,
                ]);
            }

            $quotation->update(['status' => 'converted']);
        });

        return redirect()->route('erp.invoices.index')
            ->with('success', 'Quotation converted to invoice successfully.');
    }

    public function downloadPdf(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'creator']);

        $fields = [
            'Date' => $quotation->created_at->format('M d, Y'),
            'Valid Until' => $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : '—',
            'Customer' => e($quotation->customer->company_name ?? 'N/A'),
            'Created By' => e($quotation->creator->name ?? '—'),
            'Status' => ucfirst($quotation->status ?? 'pending'),
        ];

        $items = $quotation->items->map(fn ($item) => [
            ['value' => e($item->description)],
            ['value' => $item->quantity, 'right' => true],
            ['value' => 'Rs. '.number_format($item->unit_price, 2), 'right' => true],
            ['value' => 'Rs. '.number_format($item->total, 2), 'right' => true],
        ]);

        $totals = [
            'Subtotal' => 'Rs. '.number_format($quotation->subtotal ?? 0, 2),
        ];
        if (($quotation->discount_amount ?? 0) > 0) {
            $totals['Discount'] = '- Rs. '.number_format($quotation->discount_amount, 2);
        }
        if (($quotation->tax_amount ?? 0) > 0) {
            $totals['Tax'] = 'Rs. '.number_format($quotation->tax_amount, 2);
        }
        $totals['Total'] = ['value' => 'Rs. '.number_format($quotation->total ?? 0, 2), 'grand' => true];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Quotation',
            'reference' => $quotation->quotation_number,
            'status' => ucfirst($quotation->status ?? 'pending'),
            'fields' => $fields,
            'items' => $items,
            'itemColumns' => ['Description', 'Qty', 'Unit Price', 'Amount'],
            'totals' => $totals,
            'notes' => $quotation->notes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($quotation->quotation_number.'.pdf');
    }

    private function generateQuotationNumber(): string
    {
        $lastQuotation = Quotation::latest('id')->first();
        $number = $lastQuotation ? intval(substr($lastQuotation->quotation_number, 4)) + 1 : 1;

        return 'QUO-'.str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNumber(): string
    {
        $lastInvoice = Invoice::latest('id')->first();
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 4)) + 1 : 1;

        return 'INV-'.str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
