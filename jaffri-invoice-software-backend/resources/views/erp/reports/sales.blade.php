@extends('erp.layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Sales Report</h2>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
        <form action="{{ route('erp.reports.sales') }}" method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer...">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('erp.reports.sales') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Sales</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--text-main)">Rs. {{ number_format($summary['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Invoices</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--text-main)">{{ $summary['total_invoices'] ?? 0 }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Amount Received</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--success)">Rs. {{ number_format($summary['total_paid'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th style="text-align:right">Subtotal</th>
                    <th style="text-align:right">Tax</th>
                    <th style="text-align:right">Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td style="font-weight:600">{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->customer->company_name ?? 'N/A' }}</td>
                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                    <td style="text-align:right">Rs. {{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="text-align:right">Rs. {{ number_format($invoice->tax_amount, 2) }}</td>
                    <td style="text-align:right;font-weight:600">Rs. {{ number_format($invoice->total, 2) }}</td>
                    <td>
                        <span class="status-badge
                            {{ $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'pending' ? 'pending' : 'overdue') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons d-flex gap-2">
                            <a href="{{ route('erp.invoices.pdf', $invoice) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-message text-center">No invoices found for the selected period</td>
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
    @include('erp.partials.pagination', ['paginator' => $invoices])
</div>

<script>
function dateRangePicker() {
    return {
        init() {}
    }
}
</script>
@endsection
