@extends('erp.layouts.app')

@section('title', 'Cash Book')
@section('subtitle', 'Manage your cash entries')

@section('content')
<div class="cashbook-page">
    <div class="page-header">
        <h2>Cash Book</h2>
        <div class="header-actions">
            <button class="btn btn-secondary" data-icon="download" onclick="window.print()"><span>Download PDF</span></button>
            <button class="btn btn-primary" data-icon="plus" onclick="openModal('entryModal')"><span>Add Entry</span></button>
        </div>
    </div>

    <div class="cash-summary">
        <div class="summary-box">
            <div class="icon-wrapper cash" data-icon="cash"></div>
            <div><p>Cash In Hand</p><h3>Rs. {{ number_format($totalIncome, 2) }}</h3></div>
        </div>
        <div class="summary-box">
            <div class="icon-wrapper net" data-icon="bank"></div>
            <div><p>Total Net Balance</p><h3>Rs. {{ number_format($netBalance, 2) }}</h3></div>
        </div>
    </div>

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.finance.cash-book'), 'placeholder' => 'Search cash entries...'])
    </div>

    <div class="table-container">
        <table class="data-table cashbook-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Bank</th>
                    <th>Reference</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="entriesBody">
                @forelse($cashEntries as $entry)
                    <tr>
                        <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                        <td class="desc-cell">{{ $entry->description ?? '—' }}</td>
                        <td>
                            <span class="status-badge {{ $entry->type === 'income' ? 'paid' : 'overdue' }}">
                                {{ ucfirst($entry->type) }}
                            </span>
                        </td>
                        <td class="amount {{ $entry->type === 'income' ? 'receipt' : 'payment' }}">
                            Rs. {{ number_format($entry->amount, 2) }}
                        </td>
                        <td>{{ $entry->bank->name ?? '—' }}</td>
                        <td>{{ $entry->reference_number ?? '—' }}</td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="{{ route('erp.finance.cash-book.pdf', $entry) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                                <button class="btn-icon" title="Edit" data-icon="edit" onclick="editEntry({{ $entry->id }}, '{{ $entry->entry_date->format('Y-m-d') }}', '{{ $entry->type }}', '{{ addslashes($entry->description ?? '') }}', {{ $entry->amount }}, '{{ addslashes($entry->reference_number ?? '') }}', {{ $entry->bank_id ?? 'null' }})"></button>
                                <form action="{{ route('erp.finance.cash-book.destroy', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-message text-center">No cash entries yet. Click "Add Entry" to record a transaction.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('erp.partials.pagination', ['paginator' => $cashEntries])
</div>

<!-- Add Entry Modal -->
<div class="modal-overlay" id="entryModal">
    <div class="modal large">
        <div class="modal-header">
            <h3 id="entryModalTitle">Add Cash Entry</h3>
            <button class="close-btn" onclick="closeModal('entryModal')">&times;</button>
        </div>
        <form id="entryForm" method="POST" action="{{ route('erp.finance.cash-book.store') }}">
            @csrf
            <input type="hidden" name="_method" id="entryMethod" value="POST">
            <input type="hidden" id="entryId" name="entry_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="entry_date" id="entryDate" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Type *</label>
                    <select name="type" id="entryType" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" id="entryDesc" rows="2" placeholder="Payment for..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="entryAmount" placeholder="e.g. 5000" step="0.01" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Reference Number</label>
                    <input type="text" name="reference_number" id="entryRef" placeholder="e.g. REF-001">
                </div>
                <div class="form-group">
                    <label>Bank</label>
                    <select name="bank_id" id="entryBank">
                        <option value="">Select Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('entryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editEntry(id, date, type, description, amount, reference, bankId) {
    document.getElementById('entryModalTitle').textContent = 'Edit Cash Entry';
    document.getElementById('entryId').value = id;
    document.getElementById('entryDate').value = date;
    document.getElementById('entryType').value = type;
    document.getElementById('entryDesc').value = description;
    document.getElementById('entryAmount').value = amount;
    document.getElementById('entryRef').value = reference;
    document.getElementById('entryBank').value = bankId || '';
    document.getElementById('entryMethod').value = 'PUT';
    document.getElementById('entryForm').action = '/finance/cash-book/' + id;
    openModal('entryModal');
}

document.getElementById('entryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    injectIcons();
});
</script>
@endpush
