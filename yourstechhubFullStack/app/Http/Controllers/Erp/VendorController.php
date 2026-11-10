<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $vendors = $query->latest()->paginate(15)->withQueryString();

        return view('erp.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('erp.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:vendors,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        Vendor::create($validated);

        return redirect()->route('erp.vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load([
            'purchaseOrders' => fn ($q) => $q->latest()->limit(10),
        ]);

        $totalPurchases = $vendor->purchaseOrders()->sum('total');
        $pendingOrders = $vendor->purchaseOrders()->whereIn('status', ['draft', 'sent', 'confirmed'])->sum('total');

        return view('erp.vendors.show', compact('vendor', 'totalPurchases', 'pendingOrders'));
    }

    public function edit(Vendor $vendor)
    {
        return view('erp.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:vendors,email,'.$vendor->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $vendor->update($validated);

        return redirect()->route('erp.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete vendor with existing purchase orders. Remove related orders first.');
        }

        $vendor->delete();

        return redirect()->route('erp.vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    public function downloadPdf(Vendor $vendor)
    {
        $fields = [
            'Company Name' => e($vendor->company_name),
            'Contact Person' => e($vendor->contact_person ?? '—'),
            'Email' => e($vendor->email ?? '—'),
            'Phone' => e($vendor->phone ?? '—'),
            'Address' => e($vendor->address ?? '—'),
            'City' => e($vendor->city ?? '—'),
            'Country' => e($vendor->country ?? '—'),
            'Tax Number' => e($vendor->tax_number ?? '—'),
            'Payment Terms' => e($vendor->payment_terms ?? '—'),
            'Balance' => 'Rs. '.number_format($vendor->balance ?? 0, 2),
            'Status' => $vendor->is_active ? 'Active' : 'Inactive',
            'Created' => $vendor->created_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Vendor Profile',
            'reference' => $vendor->company_name,
            'status' => $vendor->is_active ? 'Active' : 'Inactive',
            'fields' => $fields,
            'notes' => $vendor->notes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('vendor-'.$vendor->id.'.pdf');
    }
}
