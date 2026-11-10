@extends('erp.layouts.app')

@section('title', 'General Ledger')
@section('subtitle', 'View and manage ledger entries')

@section('content')
<div class="ledger-page">
    <div class="page-header" style="margin-bottom:2rem">
        <div class="d-flex align-items-center gap-3">
            <h2>General Ledger</h2>
        </div>
        <div class="header-actions d-flex align-items-center gap-3">
            <button class="btn btn-secondary" data-icon="download" onclick="window.print()"><span>Download PDF</span></button>
            <button class="btn btn-primary" data-icon="plus" onclick="openModal('ledgerModal')"><span>Add Entry</span></button>
        </div>
    </div>

    <div class="ledger-summary">
        <div class="summary-card">
            <span class="label">Total Debit</span>
            <span class="value debit" id="glTotalDebit">Rs. {{ number_format($totalDebit, 2) }}</span>
        </div>
        <div class="summary-card">
            <span class="label">Total Credit</span>
            <span class="value credit" id="glTotalCredit">Rs. {{ number_format($totalCredit, 2) }}</span>
        </div>
    </div>

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.finance.ledger'), 'placeholder' => 'Search ledger entries...'])
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="glBody">
                @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                        <td>{{ $entry->account_name }}</td>
                        <td>
                            <span class="status-badge {{ $entry->type === 'debit' ? 'overdue' : 'paid' }}">
                                {{ ucfirst($entry->type) }}
                            </span>
                        </td>
                        <td class="{{ $entry->type === 'debit' ? 'amount-debit' : 'amount-credit' }}">
                            Rs. {{ number_format($entry->amount, 2) }}
                        </td>
                        <td>{{ $entry->description ?? '—' }}</td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.finance.ledger.pdf', $entry) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" data-icon="edit" onclick="editLedgerEntry({{ $entry->id }}, '{{ $entry->entry_date->format('Y-m-d') }}', '{{ addslashes($entry->account_name) }}', '{{ $entry->type }}', {{ $entry->amount }}, '{{ addslashes($entry->description ?? '') }}')"></button>
                                <form action="{{ route('erp.finance.ledger.destroy', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-message text-center">No ledger entries found. Click "Add Entry" to record a transaction.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $ledgerEntries])
</div>

<!-- Add Entry Modal -->
<div class="modal-overlay" id="ledgerModal">
    <div class="modal large">
        <div class="modal-header">
            <h3 id="ledgerModalTitle">Add Ledger Entry</h3>
            <button class="close-btn" onclick="closeModal('ledgerModal')">&times;</button>
        </div>
        <form id="ledgerForm" method="POST" action="{{ route('erp.finance.ledger.store') }}">
            @csrf
            <input type="hidden" name="_method" id="ledgerMethod" value="POST">
            <input type="hidden" id="ledgerId" name="ledger_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="entry_date" id="ledgerDate" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Account *</label>
                    <input type="text" name="account_name" id="ledgerAccount" list="accounts-list" placeholder="e.g. 1001 - Cash" required>
                    <datalist id="accounts-list">
                        @foreach($accounts as $account)
                            <option value="{{ $account }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Type *</label>
                    <select name="type" id="ledgerType" required>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="ledgerAmount" placeholder="e.g. 5000" step="0.01" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="ledgerDesc" rows="2" placeholder="Transaction description..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('ledgerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editLedgerEntry(id, date, account, type, amount, description) {
    document.getElementById('ledgerModalTitle').textContent = 'Edit Ledger Entry';
    document.getElementById('ledgerId').value = id;
    document.getElementById('ledgerDate').value = date;
    document.getElementById('ledgerAccount').value = account;
    document.getElementById('ledgerType').value = type;
    document.getElementById('ledgerAmount').value = amount;
    document.getElementById('ledgerDesc').value = description;
    document.getElementById('ledgerMethod').value = 'PUT';
    document.getElementById('ledgerForm').action = '/finance/ledger/' + id;
    openModal('ledgerModal');
}

document.getElementById('ledgerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    injectIcons();
});
</script>
@endpush
