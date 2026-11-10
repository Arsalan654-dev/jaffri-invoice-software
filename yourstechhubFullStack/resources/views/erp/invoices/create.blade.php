@extends('erp.layouts.app')

@section('title', 'Create Invoice')
@section('subtitle', 'Create a new invoice')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Create Invoice</h2>
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

    <form action="{{ route('erp.invoices.store') }}" method="POST" onsubmit="return prepareInvoiceForm()">
        @csrf
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:20px;">Invoice Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" id="invParty" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Invoice Date *</label>
                    <input type="date" name="created_at" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Due Date *</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="cash">Cash</option>
                        <option value="online">Online</option>
                        <option value="none">None</option>
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
                    <div class="item-row" data-idx="0">
                        <input type="text" class="item-desc" name="items[0][description]" placeholder="Description" oninput="calcTotals()" />
                        <input type="number" class="item-qty" name="items[0][quantity]" value="1" min="0" oninput="calcTotals()" />
                        <input type="number" class="item-price" name="items[0][unit_price]" value="0" min="0" step="0.01" oninput="calcTotals()" />
                        <input type="number" class="amount-input item-amount" value="0" readonly />
                        <button type="button" class="btn-icon danger" title="Remove" onclick="removeItem(this)" data-icon="trash"></button>
                    </div>
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
            <input type="hidden" name="total" id="hiddenTotal" value="0">
            <input type="hidden" name="tax" id="hiddenTax" value="0">
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Optional notes..."></textarea>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('erp.invoices.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Invoice</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
var itemCounter = 1;

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
