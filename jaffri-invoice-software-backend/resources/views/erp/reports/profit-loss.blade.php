@extends('erp.layouts.app')

@section('title', 'Profit & Loss Report')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Profit & Loss Report</h2>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
        <form action="{{ route('erp.reports.profit-loss') }}" method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('erp.reports.profit-loss') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Income</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--success)">Rs. {{ number_format($revenue ?? 0, 2) }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Total Expenses</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--danger)">Rs. {{ number_format($expenses ?? 0, 2) }}</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Net Profit/Loss</p>
            <p style="font-size:1.5rem;font-weight:800;color:{{ ($profit ?? 0) >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                Rs. {{ number_format($profit ?? 0, 2) }}
            </p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:4px">Margin</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--text-main)">{{ number_format($margin ?? 0, 1) }}%</p>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th style="text-align:right">Revenue</th>
                    <th style="text-align:right">Expenses</th>
                    <th style="text-align:right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyProfit as $entry)
                <tr>
                    <td>{{ $entry['month'] }}</td>
                    <td style="text-align:right;color:var(--success)">Rs. {{ number_format($entry['revenue'], 2) }}</td>
                    <td style="text-align:right;color:var(--danger)">Rs. {{ number_format($entry['expenses'], 2) }}</td>
                    <td style="text-align:right;font-weight:600;color:{{ $entry['profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                        Rs. {{ number_format($entry['profit'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="empty-message text-center">No data found for the selected period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
