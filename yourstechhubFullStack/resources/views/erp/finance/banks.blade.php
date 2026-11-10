@extends('erp.layouts.app')

@section('title', 'Banks')
@section('subtitle', 'Manage your bank accounts')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Bank Accounts</h2>
        <button class="btn btn-primary" data-icon="plus" onclick="openModal('bankModal')"><span>Add Bank Account</span></button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.finance.banks'), 'placeholder' => 'Search banks...'])
    </div>

    <div class="inventory-grid">
        @forelse($banks as $bank)
            <div class="inventory-card">
                <div style="padding:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                        <div>
                            <h3 style="font-size:1.125rem; font-weight:700; color:var(--text-main);">{{ $bank->name }}</h3>
                            <p style="font-size:.875rem; color:var(--text-muted);">{{ $bank->account_number }}</p>
                        </div>
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:.875rem; color:var(--text-muted);">Balance</span>
                            <span style="font-size:1.5rem; font-weight:700; color:{{ $bank->balance >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                Rs. {{ number_format($bank->balance, 2) }}
                            </span>
                        </div>
                        @if($bank->currency)
                            <p style="font-size:.75rem; color:var(--text-muted); margin-top:4px;">Currency: {{ $bank->currency }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:48px; color:var(--text-muted);">
                No bank accounts found. Add your first bank account to get started.
            </div>
        @endforelse
    </div>
    @include('erp.partials.pagination', ['paginator' => $banks])
</div>

<!-- Add Bank Modal -->
<div class="modal-overlay" id="bankModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Bank Account</h3>
            <button class="close-btn" onclick="closeModal('bankModal')">&times;</button>
        </div>
        <form action="{{ route('erp.finance.banks.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Bank Name *</label>
                <input type="text" name="name" required placeholder="Bank name">
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label>Account Number</label>
                <input type="text" name="account_number" placeholder="Account number">
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label>Currency</label>
                <input type="text" name="currency" value="PKR" placeholder="PKR">
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label>Opening Balance</label>
                <input type="number" name="balance" value="0" min="0" step="0.01">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bankModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
