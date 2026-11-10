@extends('erp.layouts.app')

@section('title', 'Vendors')
@section('subtitle', 'Manage your vendors')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Vendors</h2>
        <button class="btn btn-primary" data-icon="plus" onclick="openModal('vendorModal')"><span>Add Vendor</span></button>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.vendors.index'), 'placeholder' => 'Search vendors...'])
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Current Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $vendor->company_name }}</td>
                        <td>{{ $vendor->email ?? '—' }}</td>
                        <td>{{ $vendor->phone ?? '—' }}</td>
                        <td class="amount">Rs. {{ number_format($vendor->balance ?? 0, 2) }}</td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.vendors.pdf', $vendor) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" onclick="editVendor({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}', '{{ addslashes($vendor->email ?? '') }}', '{{ addslashes($vendor->phone ?? '') }}', {{ $vendor->balance ?? 0 }})" data-icon="edit"></button>
                                <form action="{{ route('erp.vendors.destroy', $vendor) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-message text-center">No vendors yet. Click "Add Vendor" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $vendors])
</div>

<!-- Add Vendor Modal -->
<div class="modal-overlay" id="vendorModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="vendorModalTitle">Add Vendor</h3>
            <button class="close-btn" onclick="closeModal('vendorModal')">&times;</button>
        </div>
        <form id="vendorForm" method="POST" action="{{ route('erp.vendors.store') }}">
            @csrf
            <input type="hidden" name="_method" id="vendorMethod" value="POST">
            <input type="hidden" id="vendorId" name="vendor_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="company_name" id="vendName" required placeholder="Vendor name">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="vendPhone" placeholder="+92 3xx xxxxxxx">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="vendEmail" placeholder="vendor@email.com">
                </div>
                <div class="form-group">
                    <label>Opening Balance</label>
                    <input type="number" name="balance" id="vendBalance" value="0" step="0.01">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('vendorModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Vendor</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editVendor(id, name, email, phone, balance) {
    document.getElementById('vendorModalTitle').textContent = 'Edit Vendor';
    document.getElementById('vendorId').value = id;
    document.getElementById('vendName').value = name;
    document.getElementById('vendEmail').value = email;
    document.getElementById('vendPhone').value = phone;
    document.getElementById('vendBalance').value = balance;
    document.getElementById('vendorMethod').value = 'PUT';
    document.getElementById('vendorForm').action = '/vendors/' + id;
    openModal('vendorModal');
}
</script>
@endpush
