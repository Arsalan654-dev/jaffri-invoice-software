@extends('erp.layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1200px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.vendors.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Vendor: {{ $vendor->company_name }}</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Vendor Information</h2>
                    @can('client.update')
                        <a href="{{ route('erp.vendors.edit', $vendor) }}" style="font-size:0.875rem;font-weight:500;color:var(--primary-color);text-decoration:none">Edit</a>
                    @endcan
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Company Name</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->company_name }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Contact Person</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->contact_person ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Email</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->email }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Phone</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->phone ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">City</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->city ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Country</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->country ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Tax Number</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->tax_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Payment Terms</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->payment_terms ?: '-' }}</dd>
                    </div>
                    <div style="grid-column:span 2">
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Address</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->address ?: '-' }}</dd>
                    </div>
                    <div style="grid-column:span 2">
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Notes</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $vendor->notes ?: '-' }}</dd>
                    </div>
                </div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Status</h2>
                <span class="badge {{ $vendor->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Purchase Orders</h2>
                <span style="font-size:0.875rem;color:var(--text-muted)">{{ $vendor->purchaseOrders->count() }} total</span>
            </div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->purchaseOrders->take(10) as $po)
                            <tr>
                                <td style="font-size:0.875rem">
                                    <a href="{{ route('erp.purchase-orders.show', $po) }}" style="color:var(--primary-color);text-decoration:none">{{ $po->po_number }}</a>
                                </td>
                                <td style="font-size:0.875rem;color:var(--text-main)">${{ number_format($po->total, 2) }}</td>
                                <td>
                                    <span class="badge
                                        @if($po->status === 'received') bg-success
                                        @elseif($po->status === 'cancelled') bg-danger
                                        @elseif($po->status === 'sent') bg-primary
                                        @else bg-secondary @endif">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td style="font-size:0.875rem;color:var(--text-muted)">{{ $po->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="font-size:0.875rem;color:var(--text-muted);text-align:center">No purchase orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
