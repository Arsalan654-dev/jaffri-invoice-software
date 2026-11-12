@extends('erp.layouts.app')

@section('title', 'Reports')
@section('subtitle', 'Financial Reports')

@section('content')
<div class="reports-page">
    <div class="reports-grid">
        <div class="report-card">
            <div class="report-icon-wrapper icon-bg-green" data-icon="chart"></div>
            <div class="report-info">
                <h3>Profit &amp; Loss Statement</h3>
                <p>Income and expenses summary over a period.</p>
            </div>
            <a href="{{ route('erp.reports.pnl.pdf') }}" class="btn-icon-text" data-icon="download"><span>Generate PDF</span></a>
        </div>

        <div class="report-card">
            <div class="report-icon-wrapper icon-bg-indigo" data-icon="bank"></div>
            <div class="report-info">
                <h3>Balance Sheet</h3>
                <p>Assets, liabilities, and equity at a specific date.</p>
            </div>
            <a href="{{ route('erp.reports.balance-sheet.pdf') }}" class="btn-icon-text" data-icon="download"><span>Generate PDF</span></a>
        </div>

        <div class="report-card">
            <div class="report-icon-wrapper icon-bg-amber" data-icon="cash"></div>
            <div class="report-info">
                <h3>Accounts Receivable Aging</h3>
                <p>Summary of outstanding customer invoices.</p>
            </div>
            <a href="{{ route('erp.reports.aging.pdf') }}" class="btn-icon-text" data-icon="download"><span>Generate PDF</span></a>
        </div>

        <div class="report-card">
            <div class="report-icon-wrapper icon-bg-rose" data-icon="box"></div>
            <div class="report-info">
                <h3>Inventory Valuation</h3>
                <p>Current stock value and quantity report.</p>
            </div>
            <a href="{{ route('erp.reports.inventory-valuation.pdf') }}" class="btn-icon-text" data-icon="download"><span>Generate PDF</span></a>
        </div>
    </div>

    <div class="analytics-preview">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Performance Analytics</h3>
            <small class="text-muted">Auto-refreshes every 30s</small>
        </div>

        <div class="analytics-kpis mb-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px">
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0">Total Revenue (12m)</p>
                <p id="kpi-revenue" style="font-size:1.4rem;font-weight:800;color:var(--success);margin:4px 0 0">Rs. 0</p>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px">
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0">Total Expenses (12m)</p>
                <p id="kpi-expense" style="font-size:1.4rem;font-weight:800;color:var(--danger);margin:4px 0 0">Rs. 0</p>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px">
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0">Net Profit (12m)</p>
                <p id="kpi-profit" style="font-size:1.4rem;font-weight:800;color:var(--text-main);margin:4px 0 0">Rs. 0</p>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px">
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0">Last Updated</p>
                <p id="kpi-updated" style="font-size:0.95rem;font-weight:600;color:var(--text-main);margin:4px 0 0">—</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px" class="analytics-grid">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px">
                <h4 style="font-size:1rem;margin-bottom:12px">Revenue vs Expenses (Last 12 Months)</h4>
                <div style="position:relative;height:320px"><canvas id="revenueExpenseChart"></canvas></div>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px">
                <h4 style="font-size:1rem;margin-bottom:12px">Invoices by Status</h4>
                <div style="position:relative;height:320px"><canvas id="invoiceStatusChart"></canvas></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    let revExpChart = null;
    let statusChart = null;
    const analyticsUrl = @json(route('erp.reports.analytics'));

    function formatRs(n) {
        return 'Rs. ' + Number(n || 0).toLocaleString('en-PK', { maximumFractionDigits: 0 });
    }

    async function refreshAnalytics() {
        try {
            const res = await fetch(analyticsUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();

            const totalRev = data.revenues.reduce((a, b) => a + b, 0);
            const totalExp = data.expenses.reduce((a, b) => a + b, 0);
            document.getElementById('kpi-revenue').textContent = formatRs(totalRev);
            document.getElementById('kpi-expense').textContent = formatRs(totalExp);
            document.getElementById('kpi-profit').textContent = formatRs(totalRev - totalExp);
            document.getElementById('kpi-updated').textContent = new Date(data.updated_at).toLocaleTimeString();

            const revCtx = document.getElementById('revenueExpenseChart');
            if (revExpChart) revExpChart.destroy();
            revExpChart = new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Revenue', data: data.revenues, backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 4 },
                        { label: 'Expenses', data: data.expenses, backgroundColor: 'rgba(239,68,68,0.75)', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => v >= 1000 ? (v/1000) + 'k' : v } } }
                }
            });

            const statusLabels = data.status.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1));
            const statusCounts = data.status.map(s => Number(s.count));
            const palette = ['#94a3b8', '#3b82f6', '#10b981', '#ef4444', '#f59e0b'];
            const stCtx = document.getElementById('invoiceStatusChart');
            if (statusChart) statusChart.destroy();
            statusChart = new Chart(stCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{ data: statusCounts, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '58%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        } catch (e) { console.error('analytics refresh failed', e); }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof injectIcons === 'function') injectIcons();
        refreshAnalytics();
        setInterval(refreshAnalytics, 30000);
    });
})();
</script>
@endpush
