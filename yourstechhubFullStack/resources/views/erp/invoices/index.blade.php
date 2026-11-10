@extends('erp.layouts.app')

@section('title', 'Invoices')
@section('subtitle', 'Manage your invoices')

@section('content')
<div class="invoices-page">
    <div class="page-header">
        <h2>Invoices</h2>
        <button class="btn btn-primary" data-icon="plus" onclick="openInvoiceModal()"><span>Create Invoice</span></button>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.invoices.index'), 'placeholder' => 'Search invoices...'])
        <div class="filter-tabs">
            <a href="{{ route('erp.invoices.index') }}" class="{{ !request('status') ? 'active' : '' }}">All</a>
            <a href="{{ route('erp.invoices.index', array_filter(['status' => 'sent', 'search' => request('search')])) }}" class="{{ request('status') === 'sent' ? 'active' : '' }}">Sent</a>
            <a href="{{ route('erp.invoices.index', array_filter(['status' => 'paid', 'search' => request('search')])) }}" class="{{ request('status') === 'paid' ? 'active' : '' }}">Paid</a>
            <a href="{{ route('erp.invoices.index', array_filter(['status' => 'overdue', 'search' => request('search')])) }}" class="{{ request('status') === 'overdue' ? 'active' : '' }}">Overdue</a>
        </div>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="invoicesBody">
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="font-bold">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer->company_name ?? 'N/A' }}</td>
                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</td>
                        <td class="amount">Rs. {{ number_format($invoice->total, 2) }}</td>
                        <td class="amount">Rs. {{ number_format($invoice->amount_paid ?? 0, 2) }}</td>
                        <td><span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.invoices.show', $invoice) }}" class="btn-icon" title="View" data-icon="eye"></a>
                                <a href="{{ route('erp.invoices.pdf', $invoice) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" onclick="editInvoice({{ $invoice->id }})" data-icon="edit"></button>
                                <form action="{{ route('erp.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-message text-center">No invoices yet. Click "Create Invoice" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $invoices])
</div>

<!-- Create Invoice Modal -->
<div class="modal-overlay" id="invoiceModal">
    <div class="modal large">
        <div class="modal-header">
            <h3>Create New Invoice</h3>
            <button class="close-btn" onclick="closeModal('invoiceModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('erp.invoices.store') }}" onsubmit="return prepareInvoiceForm()">
            @csrf
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
                    <input type="date" name="created_at" id="invDate" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Due Date *</label>
                    <input type="date" name="due_date" id="invDueDate" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" id="invMethod">
                        <option value="cash">Cash</option>
                        <option value="online">Online</option>
                        <option value="none">None</option>
                    </select>
                </div>
            </div>

            <div class="items-section">
                <h4>Items</h4>
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
                        <button type="button" class="btn-icon danger" title="Remove" onclick="removeInvoiceItem(this)" data-icon="trash"></button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" style="margin-top:12px" data-icon="plus" onclick="addInvoiceItem()"><span>Add Item</span></button>
            </div>

            <div class="totals-section">
                <div class="totals-display">
                    <div class="total-row"><span>Subtotal:</span><span id="tSubtotal">Rs. 0.00</span></div>
                    <div class="total-row"><span>Tax (17%):</span><span id="tTax">Rs. 0.00</span></div>
                    <div class="total-row grand-total"><span>Total:</span><span id="tTotal">Rs. 0.00</span></div>
                </div>
            </div>

            <input type="hidden" name="total" id="hiddenTotal" value="0">
            <input type="hidden" name="tax" id="hiddenTax" value="0">

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Optional notes..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('invoiceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Invoice</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var itemCounter = 1;

function addInvoiceItem() {
    var container = document.getElementById('invoiceItems');
    var idx = itemCounter;
    var html = '<div class="item-row" data-idx="' + idx + '">' +
        '<input type="text" class="item-desc" name="items[' + idx + '][description]" placeholder="Description" oninput="calcTotals()" />' +
        '<input type="number" class="item-qty" name="items[' + idx + '][quantity]" value="1" min="0" oninput="calcTotals()" />' +
        '<input type="number" class="item-price" name="items[' + idx + '][unit_price]" value="0" min="0" step="0.01" oninput="calcTotals()" />' +
        '<input type="number" class="amount-input item-amount" value="0" readonly />' +
        '<button type="button" class="btn-icon danger" title="Remove" onclick="removeInvoiceItem(this)" data-icon="trash"></button>' +
        '</div>';
    container.insertAdjacentHTML('beforeend', html);
    var last = container.querySelector('.item-row:last-child');
    injectIcons(last);
    itemCounter++;
    calcTotals();
}

function removeInvoiceItem(btn) {
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

function editInvoice(id) {
    window.location.href = '/invoices/' + id + '/edit';
}

function openInvoiceModal() {
    openModal('invoiceModal');
}

// Init
calcTotals();
</script>
@endpush
