@extends('erp.layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1024px;margin:0 auto;padding:0 24px">
        <div class="page-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <a href="{{ route('erp.purchase-orders.index') }}" style="color:var(--text-muted)" data-icon="arrow-left"></a>
                <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Purchase Order {{ $purchaseOrder->po_number }}</h1>
                <span class="badge
                    @if($purchaseOrder->status === 'received') bg-success
                    @elseif($purchaseOrder->status === 'cancelled') bg-danger
                    @elseif($purchaseOrder->status === 'sent') bg-primary
                    @else bg-secondary @endif">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            </div>
            <div style="display:flex;gap:8px">
                @can('purchase.update')
                    <a href="{{ route('erp.purchase-orders.edit', $purchaseOrder) }}" class="btn btn-secondary" style="font-size:0.875rem">Edit</a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px">
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Vendor</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->vendor->company_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Contact</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->vendor->contact_person ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Order Date</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Expected Delivery</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('M d, Y') : '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Reference</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">-</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Email</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->vendor->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Phone</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->vendor->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Address</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $purchaseOrder->vendor->address ?? '-' }}</dd>
                </div>
            </div>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
            <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Line Items</h2>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align:right">Qty</th>
                            <th style="text-align:right">Unit Price</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td style="font-size:0.875rem;color:var(--text-main)">{{ $item->description }}</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">{{ $item->quantity }}</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">${{ number_format($item->unit_price, 2) }}</td>
                                <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">Subtotal:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($purchaseOrder->subtotal, 2) }}</td>
                        </tr>
                        <tr style="border-top:2px solid var(--border)">
                            <td colspan="3" style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">Total:</td>
                            <td style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">${{ number_format($purchaseOrder->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($purchaseOrder->notes)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:8px">Notes</h2>
                <p style="font-size:0.875rem;color:var(--text-muted);white-space:pre-line">{{ $purchaseOrder->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
