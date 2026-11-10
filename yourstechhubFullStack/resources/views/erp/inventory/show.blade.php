@extends('erp.layouts.app')

@section('title', 'Inventory Item Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1024px;margin:0 auto;padding:0 24px">
        <div class="page-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <a href="{{ route('erp.inventory.index') }}" style="color:var(--text-muted)" data-icon="arrow-left"></a>
                <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">{{ $inventoryItem->name }}</h1>
                @if($inventoryItem->quantity <= $inventoryItem->min_stock_level)
                    <span class="badge bg-danger">Low Stock</span>
                @endif
            </div>
            <div style="display:flex;gap:8px">
                @can('inventory.update')
                    <a href="{{ route('erp.inventory.edit', $inventoryItem) }}" class="btn btn-secondary" style="font-size:0.875rem">Edit</a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Item Information</h2>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">SKU</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->sku }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Name</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->name }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Category</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->category ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Unit</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->unit ?? '-' }}</dd>
                    </div>
                    <div style="grid-column:span 2">
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Description</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->description ?? '-' }}</dd>
                    </div>
                </div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Stock & Pricing</h2>
                <div style="display:flex;flex-direction:column;gap:16px">
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Current Stock</dt>
                        <dd style="font-size:1.5rem;font-weight:700;{{ $inventoryItem->quantity <= $inventoryItem->min_stock_level ? 'color:#dc3545' : 'color:var(--text-main)' }}">{{ $inventoryItem->quantity }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Min Stock Level</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $inventoryItem->min_stock_level }}</dd>
                    </div>
                    <hr style="border:0;border-top:1px solid var(--border);margin:0">
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Purchase Price</dt>
                        <dd style="font-size:1.125rem;font-weight:500;color:var(--text-main)">PKR {{ number_format($inventoryItem->purchase_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Selling Price</dt>
                        <dd style="font-size:1.125rem;font-weight:500;color:var(--text-main)">PKR {{ number_format($inventoryItem->selling_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Margin</dt>
                        <dd style="font-size:1.125rem;font-weight:500;color:#198754">
                            @if($inventoryItem->purchase_price > 0)
                                {{ number_format((($inventoryItem->selling_price - $inventoryItem->purchase_price) / $inventoryItem->purchase_price) * 100, 1) }}%
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
