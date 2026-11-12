@extends('erp.layouts.app')

@section('title', 'Edit Quotation')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:1024px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.quotations.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Edit Quotation: {{ $quotation->quotation_number }}</h1>
        </div>

        @if($errors->any())
            <div class="error-message">
                <ul style="margin:0;padding-left:20px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('erp.quotations.update', $quotation) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Basic Information</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer *</label>
                        <select name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $quotation->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="date" value="{{ old('date', $quotation->created_at->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>

            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main)">Line Items</h2>
                    <button type="button" onclick="addItem()" class="btn btn-primary" style="padding:6px 12px;font-size:0.875rem">+ Add Item</button>
                </div>

                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:40%">Description</th>
                                <th style="width:10%">Qty</th>
                                <th style="width:14%">Unit Price</th>
                                <th style="width:10%">Discount %</th>
                                <th style="width:10%">Tax %</th>
                                <th style="width:14%">Total</th>
                                <th style="width:4%"></th>
                            </tr>
                        </thead>
                        <tbody id="lineItems">
                            @foreach($quotation->items as $idx => $item)
                            <tr data-idx="{{ $idx }}">
                                <td>
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item->description }}" required style="width:100%;font-size:0.875rem" oninput="calcTotals()">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $item->qty }}" min="0" step="0.01" required style="width:100%;font-size:0.875rem" oninput="calcTotals()">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="0.01" required style="width:100%;font-size:0.875rem" oninput="calcTotals()">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][discount]" value="{{ $item->discount }}" min="0" max="100" step="0.01" style="width:100%;font-size:0.875rem" oninput="calcTotals()">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][tax]" value="{{ $item->tax }}" min="0" max="100" step="0.01" style="width:100%;font-size:0.875rem" oninput="calcTotals()">
                                </td>
                                <td style="font-size:0.875rem;font-weight:500;color:var(--text-main)" class="line-total">$0.00</td>
                                <td>
                                    <button type="button" onclick="removeItem(this)" class="btn-icon danger" data-icon="x"></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="totals-section">
                    <div class="totals-display">
                        <div class="total-row">
                            <span style="color:var(--text-muted)">Subtotal:</span>
                            <span style="font-weight:500" id="subtotal">$0.00</span>
                        </div>
                        <div class="total-row">
                            <span style="color:var(--text-muted)">Discount:</span>
                            <span style="font-weight:500;color:#dc3545" id="discountTotal">-$0.00</span>
                        </div>
                        <div class="total-row">
                            <span style="color:var(--text-muted)">Tax:</span>
                            <span style="font-weight:500" id="taxTotal">$0.00</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total:</span>
                            <span id="grandTotal">$0.00</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="subtotal" id="hiddenSubtotal" value="0">
                <input type="hidden" name="discount_amount" id="hiddenDiscount" value="0">
                <input type="hidden" name="tax_amount" id="hiddenTax" value="0">
                <input type="hidden" name="total" id="hiddenTotal" value="0">
            </div>

            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-main);margin-bottom:16px">Notes</h2>
                <div>
                    <textarea name="notes" rows="3">{{ old('notes', $quotation->notes) }}</textarea>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px">
                <a href="{{ route('erp.quotations.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var itemCounter = {{ $quotation->items->count() }};

function addItem() {
    var container = document.getElementById('lineItems');
    var idx = itemCounter;
    var html = '<tr data-idx="' + idx + '">' +
        '<td><input type="text" name="items[' + idx + '][description]" required style="width:100%;font-size:0.875rem" oninput="calcTotals()"></td>' +
        '<td><input type="number" name="items[' + idx + '][quantity]" value="1" min="0" step="0.01" required style="width:100%;font-size:0.875rem" oninput="calcTotals()"></td>' +
        '<td><input type="number" name="items[' + idx + '][unit_price]" value="0" min="0" step="0.01" required style="width:100%;font-size:0.875rem" oninput="calcTotals()"></td>' +
        '<td><input type="number" name="items[' + idx + '][discount]" value="0" min="0" max="100" step="0.01" style="width:100%;font-size:0.875rem" oninput="calcTotals()"></td>' +
        '<td><input type="number" name="items[' + idx + '][tax]" value="0" min="0" max="100" step="0.01" style="width:100%;font-size:0.875rem" oninput="calcTotals()"></td>' +
        '<td style="font-size:0.875rem;font-weight:500;color:var(--text-main)" class="line-total">$0.00</td>' +
        '<td><button type="button" onclick="removeItem(this)" class="btn-icon danger" data-icon="x"></button></td>' +
        '</tr>';
    container.insertAdjacentHTML('beforeend', html);
    var last = container.querySelector('tr:last-child');
    injectIcons(last);
    itemCounter++;
    calcTotals();
}

function removeItem(btn) {
    var rows = document.querySelectorAll('#lineItems tr');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    calcTotals();
}

function calcTotals() {
    var subtotal = 0;
    var totalDiscount = 0;
    var totalTax = 0;
    document.querySelectorAll('#lineItems tr').forEach(function(row) {
        var qty = parseFloat(row.querySelector('input[name$="[quantity]"]').value) || 0;
        var price = parseFloat(row.querySelector('input[name$="[unit_price]"]').value) || 0;
        var discount = parseFloat(row.querySelector('input[name$="[discount]"]').value) || 0;
        var tax = parseFloat(row.querySelector('input[name$="[tax]"]').value) || 0;
        var lineSubtotal = qty * price;
        var lineDiscount = lineSubtotal * (discount / 100);
        var afterDiscount = lineSubtotal - lineDiscount;
        var lineTax = afterDiscount * (tax / 100);
        var lineTotal = afterDiscount + lineTax;
        row.querySelector('.line-total').textContent = '$' + lineTotal.toFixed(2);
        subtotal += lineSubtotal;
        totalDiscount += lineDiscount;
        totalTax += lineTax;
    });
    var grandTotal = subtotal - totalDiscount + totalTax;
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('discountTotal').textContent = '-$' + totalDiscount.toFixed(2);
    document.getElementById('taxTotal').textContent = '$' + totalTax.toFixed(2);
    document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    document.getElementById('hiddenSubtotal').value = subtotal.toFixed(2);
    document.getElementById('hiddenDiscount').value = totalDiscount.toFixed(2);
    document.getElementById('hiddenTax').value = totalTax.toFixed(2);
    document.getElementById('hiddenTotal').value = grandTotal.toFixed(2);
}

calcTotals();
</script>
@endpush
