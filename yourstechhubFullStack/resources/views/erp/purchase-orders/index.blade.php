@extends('erp.layouts.app')

@section('title', 'Purchase Orders')
@section('subtitle', 'Manage your purchase orders')

@section('content')
<div class="purchase-orders-page">
    <div class="page-header">
        <h2>Purchase Orders</h2>
        <a href="{{ route('erp.purchase-orders.create') }}" class="btn btn-primary" data-icon="plus"><span>New PO</span></a>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.purchase-orders.index'), 'placeholder' => 'Search purchase orders...'])
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td class="font-bold">{{ $po->po_number }}</td>
                        <td>{{ $po->vendor->company_name ?? '-' }}</td>
                        <td>{{ $po->order_date ? $po->order_date->format('M d, Y') : '-' }}</td>
                        <td class="amount">Rs. {{ number_format($po->total ?? 0, 2) }}</td>
                        <td>
                            <span class="status-badge {{ $po->status ?? 'pending' }}">{{ ucfirst($po->status ?? 'pending') }}</span>
                        </td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.purchase-orders.show', $po) }}" class="btn-icon" title="View" data-icon="eye"></a>
                                <a href="{{ route('erp.purchase-orders.pdf', $po) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" onclick="editPO({{ $po->id }}, '{{ $po->po_number }}', {{ $po->vendor_id ?? 'null' }}, '{{ $po->order_date ? $po->order_date->format('Y-m-d') : '' }}', {{ $po->total ?? 0 }}, '{{ addslashes($po->notes ?? '') }}')" data-icon="edit"></button>
                                <form action="{{ route('erp.purchase-orders.destroy', $po) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-message text-center">No purchase orders yet. Click "New PO" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $purchaseOrders])
</div>

<!-- New PO Modal -->
<div class="modal-overlay" id="poModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="poModalTitle">New Purchase Order</h3>
            <button class="close-btn" onclick="closeModal('poModal')">&times;</button>
        </div>
        <form id="poForm" method="POST" action="{{ route('erp.purchase-orders.store') }}">
            @csrf
            <input type="hidden" name="_method" id="poMethod" value="POST">
            <input type="hidden" id="poId" name="purchase_order_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Vendor *</label>
                    <select name="vendor_id" id="poVendor" required>
                        <option value="">-- Select Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>PO Date</label>
                    <input type="date" name="date" id="poDate" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" name="total" id="poTotal" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="poStatus">
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id="poNotes" rows="3" placeholder="Optional notes..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('poModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save PO</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editPO(id, number, vendorId, date, total, notes) {
    document.getElementById('poModalTitle').textContent = 'Edit Purchase Order';
    document.getElementById('poId').value = id;
    document.getElementById('poVendor').value = vendorId;
    document.getElementById('poDate').value = date;
    document.getElementById('poTotal').value = total;
    document.getElementById('poNotes').value = notes;
    document.getElementById('poMethod').value = 'PUT';
    document.getElementById('poForm').action = '/purchase-orders/' + id;
    openModal('poModal');
}

function refreshPoVendors(callback) {
    fetch('{{ route("erp.api.vendors") }}')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var sel = document.getElementById('poVendor');
            sel.innerHTML = '<option value="">-- Select Vendor --</option>';
            data.forEach(function(v) {
                var opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.company_name;
                sel.appendChild(opt);
            });
            if (callback) callback();
        })
        .catch(function() {
            if (callback) callback();
        });
}

function openPoModal() {
    refreshPoVendors(function() {
        openModal('poModal');
    });
}
</script>
@endpush
