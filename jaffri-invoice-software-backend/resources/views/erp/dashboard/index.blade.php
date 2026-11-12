@extends('erp.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Manage your project dashboard and performance')

@section('content')
<div class="dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Welcome back, {{ auth()->user()->name ?? 'Admin' }}! 👋</h1>
            <p>Here's what's happening with your accounts today.</p>
        </div>
        <div class="welcome-actions">
            <a href="{{ route('erp.invoices.create') }}" class="btn btn-primary" data-icon="plus"><span>New Invoice</span></a>
            <a href="{{ route('erp.reports.index') }}" class="btn btn-secondary" data-icon="chart"><span>View Reports</span></a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card receivable">
            <div class="stat-icon" data-icon="cash"></div>
            <div class="stat-info">
                <div class="stat-label">Accounts Receivable</div>
                <div class="stat-value" id="kpi-revenue">Rs. {{ number_format($totalRevenue ?? 0, 2) }}</div>
                <div class="stat-trend positive" id="kpi-paid">{{ $paidInvoices ?? 0 }} paid invoices</div>
            </div>
        </div>
        <div class="stat-card payable">
            <div class="stat-icon" data-icon="wallet"></div>
            <div class="stat-info">
                <div class="stat-label">Accounts Payable</div>
                <div class="stat-value" id="kpi-pending-po">Rs. {{ number_format($pendingPurchaseOrders ?? 0, 2) }}</div>
                <div class="stat-trend negative" id="kpi-pending-po-trend">{{ $pendingPurchaseOrders ?? 0 }} pending orders</div>
            </div>
        </div>
        <div class="stat-card customers">
            <div class="stat-icon" data-icon="customers"></div>
            <div class="stat-info">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value" id="kpi-customers">{{ $totalCustomers ?? 0 }}</div>
                <div class="stat-trend positive">Active customers</div>
            </div>
        </div>
        <div class="stat-card inventory">
            <div class="stat-icon" data-icon="box"></div>
            <div class="stat-info">
                <div class="stat-label">Inventory Items</div>
                <div class="stat-value" id="kpi-inventory">{{ $totalInventoryItems ?? 0 }}</div>
                <div class="stat-trend" id="kpi-low-stock">{{ $lowStockItems ?? 0 }} items low in stock</div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Invoice Status Distribution</h3>
            <div style="height:300px;position:relative"><canvas id="statusChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="mb-0">Recent Invoices</h3>
                <small class="text-muted" id="dash-updated" style="font-size:.75rem">Auto-refreshes 30s</small>
            </div>
            <div class="recent-invoices" id="recent-invoices">
                @forelse($recentInvoices ?? [] as $inv)
                    <div class="invoice-item">
                        <div class="invoice-info">
                            <span class="invoice-number">{{ $inv->invoice_number ?? 'N/A' }} · {{ $inv->customer->company_name ?? 'N/A' }}</span>
                            <span class="text-muted-c" style="font-size:.8rem">{{ $inv->created_at->format('M d, Y') }} · {{ ucfirst($inv->status) }}</span>
                        </div>
                        <span class="invoice-amount">Rs. {{ number_format($inv->total, 2) }}</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon" data-icon="file"></div>
                        <h3>No invoices yet</h3>
                        <p>Create your first invoice to see it here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof injectIcons === 'function') injectIcons(document);

    var analyticsUrl = @json(route('erp.dashboard.analytics'));
    var statusChart = null;

    function formatRs(n) {
        return 'Rs. ' + Number(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderRecentInvoices(list) {
        var container = document.getElementById('recent-invoices');
        if (!container || !list || !list.length) return;
        container.innerHTML = list.map(function(inv) {
            return '<div class="invoice-item">' +
                '<div class="invoice-info">' +
                    '<span class="invoice-number">' + inv.invoice_number + ' · ' + inv.customer + '</span>' +
                    '<span class="text-muted-c" style="font-size:.8rem">' + inv.date + ' · ' + inv.status + '</span>' +
                '</div>' +
                '<span class="invoice-amount">Rs. ' + inv.total + '</span>' +
            '</div>';
        }).join('');
        if (typeof injectIcons === 'function') injectIcons(container);
    }

    function drawStatusChart(status) {
        var ctx = document.getElementById('statusChart');
        if (!ctx || typeof Chart === 'undefined') return;
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Partial', 'Overdue'],
                datasets: [{
                    data: status,
                    backgroundColor: ['#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: 'Plus Jakarta Sans', weight: 600, size: 13 }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    async function refreshDashboard() {
        try {
            var res = await fetch(analyticsUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            var data = await res.json();

            var rev = document.getElementById('kpi-revenue');
            if (rev) rev.textContent = formatRs(data.total_revenue);
            var paid = document.getElementById('kpi-paid');
            if (paid) paid.textContent = data.paid_invoices + ' paid invoices';
            var po = document.getElementById('kpi-pending-po');
            if (po) po.textContent = formatRs(data.pending_purchase_orders);
            var poTrend = document.getElementById('kpi-pending-po-trend');
            if (poTrend) poTrend.textContent = data.pending_purchase_orders + ' pending orders';
            var cust = document.getElementById('kpi-customers');
            if (cust) cust.textContent = data.total_customers;
            var inv = document.getElementById('kpi-inventory');
            if (inv) inv.textContent = data.total_inventory_items;
            var low = document.getElementById('kpi-low-stock');
            if (low) low.textContent = data.low_stock_items + ' items low in stock';

            var updated = document.getElementById('dash-updated');
            if (updated) updated.textContent = 'Updated ' + new Date(data.updated_at).toLocaleTimeString();

            drawStatusChart(data.status);
            renderRecentInvoices(data.recent_invoices);
        } catch (e) { console.error('dashboard refresh failed', e); }
    }

    drawStatusChart([
        {{ $paidInvoices ?? 0 }},
        {{ $pendingInvoices ?? 0 }},
        {{ $partialCount ?? 0 }},
        {{ $overdueCount ?? 0 }}
    ]);
    refreshDashboard();
    setInterval(refreshDashboard, 30000);
});
</script>
@endpush
