<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; }
        .header h1 { font-size: 24px; color: #4f46e5; margin: 0; }
        .header p { color: #64748b; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 10px; border-bottom: 2px solid #e2e8f0; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .total { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Demo ERP</h1>
        <p>Accounts Receivable Aging</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr><th>Invoice #</th><th>Customer</th><th>Date</th><th>Due</th><th>Aging</th><th>Amount (Rs.)</th></tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv['number'] }}</td>
                    <td>{{ $inv['customer'] }}</td>
                    <td>{{ $inv['date'] }}</td>
                    <td>{{ $inv['due'] }}</td>
                    <td>{{ $inv['aging'] }}</td>
                    <td>{{ number_format($inv['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="color:#94a3b8">No outstanding invoices</td></tr>
            @endforelse
            <tr><td colspan="5" class="total">Total Outstanding</td><td class="total">{{ number_format($totalDue, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">Created By Yourtechhub</div>
</body>
</html>
