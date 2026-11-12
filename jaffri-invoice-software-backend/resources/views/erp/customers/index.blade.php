@extends('erp.layouts.app')

@section('title', 'Customers')
@section('subtitle', 'Manage your customers')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Customers</h2>
        <button class="btn btn-primary" data-icon="plus" onclick="openAddCustomer()"><span>Add Customer</span></button>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.customers.index'), 'placeholder' => 'Search customers...'])
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
            <tbody id="customersBody">
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->company_name }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td class="amount">Rs. {{ number_format($customer->balance ?? 0, 2) }}</td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.customers.pdf', $customer) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                @can('user.manage')
                                    @if($customer->user)
                                    <button class="btn-icon" title="Set Permissions" data-icon="key" style="background:#eef2ff;color:var(--primary);border-color:#c7d2fe" onclick="openModal('setPermissionModal{{ $customer->id }}')"></button>
                                    @else
                                    <button class="btn-icon" title="No login user yet - set a password to enable permissions" data-icon="key" disabled style="opacity:.35;cursor:not-allowed"></button>
                                    @endif
                                @endcan
                                <button class="btn-icon" title="Edit" onclick="editCustomer({{ $customer->id }}, '{{ addslashes($customer->company_name) }}', '{{ addslashes($customer->email ?? '') }}', '{{ addslashes($customer->phone ?? '') }}', {{ $customer->balance ?? 0 }}, '{{ $customer->user?->roles->first()?->slug ?? '' }}')" data-icon="edit"></button>
                                <form action="{{ route('erp.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @if($customer->user)
                        @include('erp.partials.set-permission-modal', [
                            'modalId' => 'setPermissionModal'.$customer->id,
                            'targetUser' => $customer->user,
                            'targetLabel' => $customer->company_name,
                            'groupedPermissions' => $groupedPermissions,
                        ])
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="empty-message text-center">No customers yet. Click "Add Customer" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $customers])
</div>

<!-- Add Customer Modal -->
<div class="modal-overlay" id="customerModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="customerModalTitle">Add Customer</h3>
            <button class="close-btn" onclick="closeModal('customerModal')">&times;</button>
        </div>
        <form id="customerForm" method="POST" action="{{ route('erp.customers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="customerMethod" value="POST">
            <input type="hidden" id="customerId" name="customer_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="company_name" id="custName" required placeholder="Customer name">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="custPhone" placeholder="+92 3xx xxxxxxx">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="custEmail" placeholder="customer@email.com">
                </div>
                <div class="form-group">
                    <label>Opening Balance</label>
                    <input type="number" name="balance" id="custBalance" value="0" step="0.01">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Login Password</label>
                    <input type="password" name="password" id="custPassword" minlength="8" placeholder="Min 8 chars (creates login user)">
                    <input type="password" name="password_confirmation" id="custPasswordConfirm" minlength="8" placeholder="Confirm password" style="margin-top:8px">
                    <span style="font-size:0.75rem;color:var(--text-muted)">Leave blank to skip login user. On edit, blank keeps current.</span>
                </div>
                <div class="form-group">
                    <label>Role <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(identity only)</span></label>
                    <select name="role" id="custRole">
                        <option value="">No role (simple user)</option>
                        @foreach(($roles ?? []) as $role)
                            <option value="{{ $role->slug }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('customerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddCustomer() {
    document.getElementById('customerModalTitle').textContent = 'Add Customer';
    document.getElementById('customerId').value = '';
    document.getElementById('customerForm').reset();
    document.getElementById('custBalance').value = 0;
    document.getElementById('custRole').value = '';
    document.getElementById('customerMethod').value = 'POST';
    document.getElementById('customerForm').action = '{{ route('erp.customers.store') }}';
    openModal('customerModal');
}

function editCustomer(id, name, email, phone, balance, roleSlug) {
    document.getElementById('customerModalTitle').textContent = 'Edit Customer';
    document.getElementById('customerId').value = id;
    document.getElementById('custName').value = name;
    document.getElementById('custEmail').value = email;
    document.getElementById('custPhone').value = phone;
    document.getElementById('custBalance').value = balance;
    document.getElementById('custRole').value = roleSlug || '';
    document.getElementById('custPassword').value = '';
    document.getElementById('custPasswordConfirm').value = '';
    document.getElementById('customerMethod').value = 'PUT';
    document.getElementById('customerForm').action = '/customers/' + id;
    openModal('customerModal');
}

document.getElementById('customerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
    }
});
</script>
@endpush
