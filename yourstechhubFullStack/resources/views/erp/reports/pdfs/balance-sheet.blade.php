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
        <p>Balance Sheet</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr><th style="width:60%">Item</th><th>Amount (Rs.)</th></tr>
        </thead>
        <tbody>
            <tr><td>Cash in Hand</td><td>{{ number_format($cash, 2) }}</td></tr>
            <tr><td>Accounts Receivable</td><td>{{ number_format($receivable, 2) }}</td></tr>
            <tr><td>Total Assets</td><td class="total">{{ number_format($totalAssets, 2) }}</td></tr>
            <tr><td colspan="2"></td></tr>
            <tr><td>Accounts Payable</td><td>{{ number_format($payable, 2) }}</td></tr>
            <tr><td class="total">Equity / Net Worth</td><td class="total">{{ number_format($equity, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">Created By Yourtechhub</div>
</body>
</html>
