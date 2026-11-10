@extends('erp.layouts.app')

@section('title', 'Purchase Report')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Purchase Report</h2>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
        <form action="{{ route('erp.reports.purchases') }}" method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="PO # or vendor...">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('erp.reports.purchases') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Purchases</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--text-main)">Rs. {{ number_format($summary['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Orders</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--text-main)">{{ $summary['total_orders'] ?? 0 }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Amount Paid</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--success)">Rs. {{ number_format($summary['total_received'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th style="text-align:right">Subtotal</th>
                    <th style="text-align:right">Tax</th>
                    <th style="text-align:right">Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $order)
                <tr>
                    <td style="font-weight:600">{{ $order->po_number }}</td>
                    <td>{{ $order->vendor->company_name ?? 'N/A' }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td style="text-align:right">Rs. {{ number_format($order->subtotal, 2) }}</td>
                    <td style="text-align:right">Rs. {{ number_format($order->tax_amount, 2) }}</td>
                    <td style="text-align:right;font-weight:600">Rs. {{ number_format($order->total, 2) }}</td>
                    <td>
                        <span class="status-badge
                            {{ $order->status === 'received' ? 'paid' : ($order->status === 'pending' ? 'pending' : 'partial') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons d-flex gap-2">
                            <a href="{{ route('erp.purchase-orders.pdf', $order) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-message text-center">No purchase orders found for the selected period</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot style="background:var(--bg-main)">
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:700">Total:</td>
                    <td style="text-align:right;font-weight:700">Rs. {{ number_format($pageTotals['subtotal'] ?? 0, 2) }}</td>
                    <td style="text-align:right;font-weight:700">Rs. {{ number_format($pageTotals['tax_amount'] ?? 0, 2) }}</td>
                    <td style="text-align:right;font-weight:700">Rs. {{ number_format($pageTotals['total'] ?? 0, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $purchaseOrders])
</div>
@endsection
