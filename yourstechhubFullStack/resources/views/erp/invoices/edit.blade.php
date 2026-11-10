@extends('erp.layouts.app')

@section('title', 'Edit Invoice')
@section('subtitle', 'Edit invoice #{{ $invoice->invoice_number }}')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Edit Invoice #{{ $invoice->invoice_number }}</h2>
        <a href="{{ route('erp.invoices.index') }}" class="btn btn-secondary">Back to Invoices</a>
    </div>

    @if($errors->any())
        <div class="error-message" style="display:block; margin-bottom:20px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('erp.invoices.update', $invoice) }}" method="POST" onsubmit="return prepareInvoiceForm()">
        @csrf
        @method('PUT')
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:20px;">Invoice Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ $invoice->status == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="cash" {{ ($invoice->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="online" {{ ($invoice->payment_method ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="none" {{ ($invoice->payment_method ?? '') == 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:20px;">Items</h3>
            <div class="items-section">
                <div class="items-header-row">
                    <span>Description</span>
                    <span>Qty</span>
                    <span class="text-right">Price</span>
                    <span class="text-right">Amount</span>
                    <span></span>
                </div>
                <div id="invoiceItems">
                    @foreach($invoice->items as $idx => $item)
                        <div class="item-row" data-idx="{{ $idx }}">
                            <input type="text" class="item-desc" name="items[{{ $idx }}][description]" value="{{ $item->description }}" placeholder="Description" oninput="calcTotals()" />
                            <input type="number" class="item-qty" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" min="0" oninput="calcTotals()" />
                            <input type="number" class="item-price" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="0.01" oninput="calcTotals()" />
                            <input type="number" class="amount-input item-amount" value="{{ $item->quantity * $item->unit_price }}" readonly />
                            <button type="button" class="btn-icon danger" title="Remove" onclick="removeItem(this)" data-icon="trash"></button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-secondary" style="margin-top:12px" data-icon="plus" onclick="addItem()"><span>Add Item</span></button>
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:20px;">Totals</h3>
            <div class="totals-section">
                <div class="totals-display">
                    <div class="total-row"><span>Subtotal:</span><span id="tSubtotal">Rs. 0.00</span></div>
                    <div class="total-row"><span>Tax (17%):</span><span id="tTax">Rs. 0.00</span></div>
                    <div class="total-row grand-total"><span>Total:</span><span id="tTotal">Rs. 0.00</span></div>
                </div>
            </div>
            <input type="hidden" name="total" id="hiddenTotal" value="{{ $invoice->total }}">
            <input type="hidden" name="tax" id="hiddenTax" value="{{ $invoice->tax_amount }}">
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Optional notes...">{{ $invoice->notes }}</textarea>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('erp.invoices.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Invoice</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
var itemCounter = {{ $invoice->items->count() }};

function addItem() {
    var container = document.getElementById('invoiceItems');
    var idx = itemCounter;
    var html = '<div class="item-row" data-idx="' + idx + '">' +
        '<input type="text" class="item-desc" name="items[' + idx + '][description]" placeholder="Description" oninput="calcTotals()" />' +
        '<input type="number" class="item-qty" name="items[' + idx + '][quantity]" value="1" min="0" oninput="calcTotals()" />' +
        '<input type="number" class="item-price" name="items[' + idx + '][unit_price]" value="0" min="0" step="0.01" oninput="calcTotals()" />' +
        '<input type="number" class="amount-input item-amount" value="0" readonly />' +
        '<button type="button" class="btn-icon danger" title="Remove" onclick="removeItem(this)" data-icon="trash"></button>' +
        '</div>';
    container.insertAdjacentHTML('beforeend', html);
    var last = container.querySelector('.item-row:last-child');
    injectIcons(last);
    itemCounter++;
    calcTotals();
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
    calcTotals();
}

function calcTotals() {
    var subtotal = 0;
    document.querySelectorAll('#invoiceItems .item-row').forEach(function(row) {
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var amount = qty * price;
        row.querySelector('.item-amount').value = amount.toFixed(2);
        subtotal += amount;
    });
    var tax = subtotal * 0.17;
    var total = subtotal + tax;
    document.getElementById('tSubtotal').textContent = formatMoney(subtotal);
    document.getElementById('tTax').textContent = formatMoney(tax);
    document.getElementById('tTotal').textContent = formatMoney(total);
    document.getElementById('hiddenTotal').value = total.toFixed(2);
    document.getElementById('hiddenTax').value = tax.toFixed(2);
}

function prepareInvoiceForm() {
    calcTotals();
    return true;
}

calcTotals();
</script>
@endpush
