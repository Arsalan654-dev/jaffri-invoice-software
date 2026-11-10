@extends('erp.layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1024px;margin:0 auto;padding:0 24px">
        <div class="page-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <a href="{{ route('erp.invoices.index') }}" style="color:var(--text-muted)" data-icon="arrow-left"></a>
                <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Invoice {{ $invoice->invoice_number }}</h1>
                <span class="badge
                    @if($invoice->status === 'paid') bg-success
                    @elseif($invoice->status === 'overdue') bg-danger
                    @elseif($invoice->status === 'sent') bg-primary
                    @else bg-secondary @endif">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div style="display:flex;gap:8px">
                @can('invoice.update')
                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                        <form action="{{ route('erp.invoices.paid', $invoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="font-size:0.875rem">Mark as Paid</button>
                        </form>
                        <form action="{{ route('erp.invoices.send', $invoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="font-size:0.875rem">Send to Customer</button>
                        </form>
                        <form action="{{ route('erp.invoices.cancel', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this invoice?')">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="font-size:0.875rem;background:#dc3545;color:#fff">Cancel</button>
                        </form>
                    @endif
                    <a href="{{ route('erp.invoices.edit', $invoice) }}" class="btn btn-secondary" style="font-size:0.875rem">Edit</a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px">
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Customer</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->customer->company_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Contact</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->customer->contact_person ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Invoice Date</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Due Date</dt>
                    <dd style="font-size:0.875rem;font-weight:500;{{ $invoice->status === 'overdue' ? 'color:#dc3545' : 'color:var(--text-main)' }}">{{ $invoice->due_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Reference</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">-</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Email</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->customer->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Phone</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->customer->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Address</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $invoice->customer->address ?? '-' }}</dd>
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
                            <th style="text-align:right">Discount</th>
                            <th style="text-align:right">Tax</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td style="font-size:0.875rem;color:var(--text-main)">{{ $item->description }}</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">{{ $item->quantity }}</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">${{ number_format($item->unit_price, 2) }}</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">{{ $item->discount }}%</td>
                                <td style="font-size:0.875rem;color:var(--text-main);text-align:right">{{ $item->tax }}%</td>
                                <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">Subtotal:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <td colspan="5" style="font-size:0.875rem;font-weight:500;color:#dc3545;text-align:right">Discount:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:#dc3545;text-align:right">-${{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->tax_amount > 0)
                        <tr>
                            <td colspan="5" style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">Tax:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top:2px solid var(--border)">
                            <td colspan="5" style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">Total:</td>
                            <td style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">${{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($invoice->notes)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:8px">Notes</h2>
                <p style="font-size:0.875rem;color:var(--text-muted);white-space:pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
