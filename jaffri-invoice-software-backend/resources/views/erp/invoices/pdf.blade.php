<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #0f172a; line-height: 1.5; }
        .page { padding: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #4f46e5; padding-bottom: 18px; margin-bottom: 24px; }
        .brand h1 { font-size: 22px; color: #4f46e5; margin: 0; letter-spacing: -0.5px; }
        .brand p { color: #64748b; font-size: 11px; margin-top: 4px; }
        .invoice-meta { text-align: right; }
        .invoice-meta .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .invoice-meta .value { font-size: 16px; font-weight: bold; color: #0f172a; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 6px; }
        .status-draft { background: #f1f5f9; color: #475569; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #fee2e2; color: #b91c1c; }
        .status-cancelled { background: #fef3c7; color: #b45309; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .party { width: 48%; }
        .party-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; }
        .party-box h3 { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        .party-box .name { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 4px; }
        .party-box p { font-size: 11px; color: #475569; margin: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #4f46e5; color: #fff; text-align: left; padding: 10px 8px; font-size: 11px; font-weight: 600; }
        table.items th.right, table.items td.right { text-align: right; }
        table.items td { padding: 9px 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table.items tbody tr:nth-child(even) { background: #f8fafc; }
        .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals { width: 280px; }
        .totals .row { display: flex; justify-content: space-between; padding: 7px 12px; font-size: 12px; }
        .totals .row.grand { background: #4f46e5; color: #fff; font-weight: bold; font-size: 14px; border-radius: 4px; margin-top: 4px; }
        .totals .row .lbl { color: #64748b; }
        .totals .row.grand .lbl { color: #fff; }
        .notes { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; margin-bottom: 20px; }
        .notes h3 { font-size: 11px; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }
        .notes p { font-size: 11px; color: #334155; white-space: pre-wrap; }
        .footer { text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 30px; }
        .meta-grid { display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .meta-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; min-width: 140px; flex: 1; }
        .meta-item .label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-item .value { font-size: 12px; font-weight: 600; color: #0f172a; margin-top: 2px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="brand">
            <h1>Jaffri Invoice Software</h1>
            <p>Your's Tech Hub &mdash; Invoice Document</p>
        </div>
        <div class="invoice-meta">
            <div class="label">Invoice</div>
            <div class="value">{{ $invoice->invoice_number }}</div>
            <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="party-box">
                <h3>From</h3>
                <div class="name">Jaffri Invoice Software</div>
                <p>Your's Tech Hub</p>
                <p>+92 335 2352689</p>
            </div>
        </div>
        <div class="party">
            <div class="party-box">
                <h3>Bill To</h3>
                <div class="name">{{ $invoice->customer->company_name ?? 'N/A' }}</div>
                @if($invoice->customer?->contact_person)<p>{{ $invoice->customer->contact_person }}</p>@endif
                @if($invoice->customer?->email)<p>{{ $invoice->customer->email }}</p>@endif
                @if($invoice->customer?->phone)<p>{{ $invoice->customer->phone }}</p>@endif
                @if($invoice->customer?->address)<p>{{ $invoice->customer->address }}</p>@endif
            </div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <div class="label">Invoice Date</div>
            <div class="value">{{ $invoice->created_at->format('M d, Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="label">Due Date</div>
            <div class="value">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="label">Created By</div>
            <div class="value">{{ $invoice->creator->name ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="label">Currency</div>
            <div class="value">PKR (Rs.)</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:6%">#</th>
                <th style="width:46%">Description</th>
                <th class="right" style="width:12%">Qty</th>
                <th class="right" style="width:16%">Unit Price</th>
                <th class="right" style="width:10%">Discount</th>
                <th class="right" style="width:10%">Tax</th>
                <th class="right" style="width:16%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ $item->discount ? number_format($item->discount, 2) : '—' }}</td>
                <td class="right">{{ $item->tax ? number_format($item->tax, 2) : '—' }}</td>
                <td class="right"><strong>Rs. {{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#94a3b8">No line items</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-wrap">
        <div class="totals">
            <div class="row">
                <span class="lbl">Subtotal</span>
                <span>Rs. {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="row">
                <span class="lbl">Discount</span>
                <span>- Rs. {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="row">
                <span class="lbl">Tax ({{ number_format($invoice->tax_rate, 2) }}%)</span>
                <span>Rs. {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="row grand">
                <span class="lbl">Total</span>
                <span>Rs. {{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->amount_paid > 0)
            <div class="row">
                <span class="lbl">Amount Paid</span>
                <span>Rs. {{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            <div class="row">
                <span class="lbl">Balance Due</span>
                <span><strong>Rs. {{ number_format(max($invoice->total - $invoice->amount_paid, 0), 2) }}</strong></span>
            </div>
            @endif
        </div>
    </div>

    @if($invoice->notes)
    <div class="notes">
        <h3>Notes</h3>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        Created By Jaffri Invoice Software &middot; {{ now()->format('M d, Y h:i A') }}
    </div>
</div>
</body>
</html>
