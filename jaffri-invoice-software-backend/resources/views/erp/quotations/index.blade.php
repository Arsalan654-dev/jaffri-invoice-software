@extends('erp.layouts.app')

@section('title', 'Quotations')
@section('subtitle', 'Manage your quotations')

@section('content')
<div class="quotations-page">
    <div class="page-header">
        <h2>Quotations</h2>
        <a href="{{ route('erp.quotations.create') }}" class="btn btn-primary" data-icon="plus"><span>Create Quotation</span></a>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.quotations.index'), 'placeholder' => 'Search quotations...'])
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>QTN #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quotation)
                    <tr>
                        <td class="font-bold">{{ $quotation->quotation_number }}</td>
                        <td>{{ $quotation->customer->company_name ?? '-' }}</td>
                        <td>{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td class="amount">Rs. {{ number_format($quotation->total ?? 0, 2) }}</td>
                        <td>
                            <span class="status-badge {{ $quotation->status ?? 'pending' }}">{{ ucfirst($quotation->status ?? 'pending') }}</span>
                        </td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.quotations.show', $quotation) }}" class="btn-icon" title="View" data-icon="eye"></a>
                                <a href="{{ route('erp.quotations.pdf', $quotation) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" onclick="editQuotation({{ $quotation->id }}, '{{ $quotation->quotation_number }}', {{ $quotation->customer_id ?? 'null' }}, '{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '' }}', {{ $quotation->total ?? 0 }}, '{{ addslashes($quotation->notes ?? '') }}')" data-icon="edit"></button>
                                <form action="{{ route('erp.quotations.destroy', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-message text-center">No quotations yet. Click "Create Quotation" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $quotations])
</div>

<!-- Create Quotation Modal -->
<div class="modal-overlay" id="qtnModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="qtnModalTitle">Create Quotation</h3>
            <button class="close-btn" onclick="closeModal('qtnModal')">&times;</button>
        </div>
        <form id="qtnForm" method="POST" action="{{ route('erp.quotations.store') }}">
            @csrf
            <input type="hidden" name="_method" id="qtnMethod" value="POST">
            <input type="hidden" id="qtnId" name="quotation_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" id="qtnCustomer" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Quotation Date</label>
                    <input type="date" name="date" id="qtnDate" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" name="total" id="qtnTotal" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="qtnStatus">
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id="qtnNotes" rows="3" placeholder="Optional notes..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('qtnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editQuotation(id, number, customerId, date, total, notes) {
    document.getElementById('qtnModalTitle').textContent = 'Edit Quotation';
    document.getElementById('qtnId').value = id;
    document.getElementById('qtnCustomer').value = customerId;
    document.getElementById('qtnDate').value = date;
    document.getElementById('qtnTotal').value = total;
    document.getElementById('qtnNotes').value = notes;
    document.getElementById('qtnMethod').value = 'PUT';
    document.getElementById('qtnForm').action = '/quotations/' + id;
    openModal('qtnModal');
}

function refreshQtnCustomers(callback) {
    fetch('{{ route("erp.api.customers") }}')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var sel = document.getElementById('qtnCustomer');
            sel.innerHTML = '<option value="">-- Select Customer --</option>';
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.company_name;
                sel.appendChild(opt);
            });
            if (callback) callback();
        })
        .catch(function() {
            if (callback) callback();
        });
}

function openQtnModal() {
    refreshQtnCustomers(function() {
        openModal('qtnModal');
    });
}
</script>
@endpush
