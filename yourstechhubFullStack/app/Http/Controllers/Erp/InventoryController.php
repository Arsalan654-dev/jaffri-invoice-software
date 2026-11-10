<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('vendor');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('quantity', '<=', 'min_stock_level');
        }

        $inventoryItems = $query->latest()->paginate(15)->withQueryString();
        $categories = InventoryItem::distinct()->whereNotNull('category')->pluck('category')->sort()->values();

        return view('erp.inventory.index', compact('inventoryItems', 'categories'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.inventory.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:inventory_items,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        InventoryItem::create($validated);

        return redirect()->route('erp.inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $inventoryItem)
    {
        $inventoryItem->load('vendor');

        return view('erp.inventory.show', compact('inventoryItem'));
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('company_name')->get();

        return view('erp.inventory.edit', compact('inventoryItem', 'vendors'));
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:inventory_items,sku,'.$inventoryItem->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        $inventoryItem->update($validated);

        return redirect()->route('erp.inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return redirect()->route('erp.inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function downloadPdf(InventoryItem $inventoryItem)
    {
        $inventoryItem->load('vendor');

        $fields = [
            'SKU' => e($inventoryItem->sku),
            'Name' => e($inventoryItem->name),
            'Category' => e($inventoryItem->category ?? '—'),
            'Unit' => e($inventoryItem->unit ?? '—'),
            'Quantity' => number_format($inventoryItem->quantity ?? 0, 2),
            'Min Stock Level' => number_format($inventoryItem->min_stock_level ?? 0),
            'Purchase Price' => 'Rs. '.number_format($inventoryItem->purchase_price ?? 0, 2),
            'Selling Price' => 'Rs. '.number_format($inventoryItem->selling_price ?? 0, 2),
            'Stock Value' => 'Rs. '.number_format(($inventoryItem->quantity ?? 0) * ($inventoryItem->purchase_price ?? 0), 2),
            'Vendor' => e($inventoryItem->vendor->company_name ?? '—'),
            'Created' => $inventoryItem->created_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Inventory Item',
            'reference' => $inventoryItem->sku,
            'fields' => $fields,
            'notes' => $inventoryItem->description,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('inventory-'.$inventoryItem->sku.'.pdf');
    }
}
