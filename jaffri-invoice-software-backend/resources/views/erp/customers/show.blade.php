@extends('erp.layouts.app')

@section('title', 'Customer Details')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1200px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.customers.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Customer: {{ $customer->company_name }}</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Customer Information</h2>
                    @can('client.update')
                        <a href="{{ route('erp.customers.edit', $customer) }}" style="font-size:0.875rem;font-weight:500;color:var(--primary-color);text-decoration:none">Edit</a>
                    @endcan
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Company Name</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->company_name }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Contact Person</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->contact_person ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Email</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->email }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Phone</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->phone ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">City</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->city ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Country</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->country ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Tax Number</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->tax_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Payment Terms</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->payment_terms ?: '-' }}</dd>
                    </div>
                    <div style="grid-column:span 2">
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Address</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->address ?: '-' }}</dd>
                    </div>
                    <div style="grid-column:span 2">
                        <dt style="font-size:0.875rem;color:var(--text-muted)">Notes</dt>
                        <dd style="font-size:0.875rem;font-weight:500;color:var(--text-main)">{{ $customer->notes ?: '-' }}</dd>
                    </div>
                </div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Status</h2>
                <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Invoices</h2>
                    <span style="font-size:0.875rem;color:var(--text-muted)">{{ $customer->invoices->count() }} total</span>
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
                            @forelse($customer->invoices->take(5) as $invoice)
                                <tr>
                                    <td style="font-size:0.875rem">
                                        <a href="{{ route('erp.invoices.show', $invoice) }}" style="color:var(--primary-color);text-decoration:none">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td style="font-size:0.875rem;color:var(--text-main)">${{ number_format($invoice->total, 2) }}</td>
                                    <td>
                                        <span class="badge
                                            @if($invoice->status === 'paid') bg-success
                                            @elseif($invoice->status === 'overdue') bg-danger
                                            @elseif($invoice->status === 'sent') bg-primary
                                            @else bg-secondary @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td style="font-size:0.875rem;color:var(--text-muted)">{{ $invoice->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="font-size:0.875rem;color:var(--text-muted);text-align:center">No invoices yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Quotations</h2>
                    <span style="font-size:0.875rem;color:var(--text-muted)">{{ $customer->quotations->count() }} total</span>
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
                            @forelse($customer->quotations->take(5) as $quotation)
                                <tr>
                                    <td style="font-size:0.875rem">
                                        <a href="{{ route('erp.quotations.show', $quotation) }}" style="color:var(--primary-color);text-decoration:none">{{ $quotation->quotation_number }}</a>
                                    </td>
                                    <td style="font-size:0.875rem;color:var(--text-main)">${{ number_format($quotation->total, 2) }}</td>
                                    <td>
                                        <span class="badge
                                            @if($quotation->status === 'accepted') bg-success
                                            @elseif($quotation->status === 'rejected') bg-danger
                                            @elseif($quotation->status === 'sent') bg-primary
                                            @else bg-secondary @endif">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td style="font-size:0.875rem;color:var(--text-muted)">{{ $quotation->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="font-size:0.875rem;color:var(--text-muted);text-align:center">No quotations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
