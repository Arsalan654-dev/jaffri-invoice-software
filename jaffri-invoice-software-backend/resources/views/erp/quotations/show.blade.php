@extends('erp.layouts.app')

@section('title', 'Quotation Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1024px;margin:0 auto;padding:0 24px">
        <div class="page-header" style="justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <a href="{{ route('erp.quotations.index') }}" style="color:var(--text-muted)" data-icon="arrow-left"></a>
                <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Quotation {{ $quotation->quotation_number }}</h1>
                <span class="badge
                    @if($quotation->status === 'accepted') bg-success
                    @elseif($quotation->status === 'rejected') bg-danger
                    @elseif($quotation->status === 'sent') bg-primary
                    @else bg-secondary @endif">
                    {{ ucfirst($quotation->status) }}
                </span>
            </div>
            <div style="display:flex;gap:8px">
                @can('quotation.update')
                    @if($quotation->status === 'draft')
                        <form action="{{ route('erp.quotations.send', $quotation) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="font-size:0.875rem">Send to Customer</button>
                        </form>
                    @endif
                    @if($quotation->status === 'sent')
                        <form action="{{ route('erp.quotations.convert', $quotation) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="font-size:0.875rem">Convert to Invoice</button>
                        </form>
                    @endif
                    <a href="{{ route('erp.quotations.edit', $quotation) }}" class="btn btn-secondary" style="font-size:0.875rem">Edit</a>
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
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->customer->company_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Contact</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->customer->contact_person ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Date</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Valid Until</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Reference</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">-</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Email</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->customer->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Phone</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->customer->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt style="font-size:0.875rem;color:var(--text-muted)">Address</dt>
                    <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $quotation->customer->address ?? '-' }}</dd>
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
                        @foreach($quotation->items as $item)
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
                            <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($quotation->subtotal, 2) }}</td>
                        </tr>
                        @if($quotation->discount_amount > 0)
                        <tr>
                            <td colspan="5" style="font-size:0.875rem;font-weight:500;color:#dc3545;text-align:right">Discount:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:#dc3545;text-align:right">-${{ number_format($quotation->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($quotation->tax_amount > 0)
                        <tr>
                            <td colspan="5" style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">Tax:</td>
                            <td style="font-size:0.875rem;font-weight:500;color:var(--text-main);text-align:right">${{ number_format($quotation->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top:2px solid var(--border)">
                            <td colspan="5" style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">Total:</td>
                            <td style="font-size:1.125rem;font-weight:700;color:var(--text-main);text-align:right">${{ number_format($quotation->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($quotation->notes)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:8px">Notes</h2>
                <p style="font-size:0.875rem;color:var(--text-muted);white-space:pre-line">{{ $quotation->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
