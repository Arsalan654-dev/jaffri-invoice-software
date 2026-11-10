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
        <p>Profit & Loss Statement</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr><th style="width:60%">Particulars</th><th>Amount (Rs.)</th></tr>
        </thead>
        <tbody>
            <tr><td>Sales Revenue</td><td>{{ number_format($revenue, 2) }}</td></tr>
            <tr><td>Cost of Goods (Purchases)</td><td>{{ number_format($expenses, 2) }}</td></tr>
            <tr><td>Cash Receipts</td><td>{{ number_format($cashIn, 2) }}</td></tr>
            <tr><td>Cash Payments</td><td>{{ number_format($cashOut, 2) }}</td></tr>
            <tr><td colspan="2"></td></tr>
            <tr><td class="total">Net Profit / Loss</td><td class="total">{{ number_format($profit, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">Created By Yourtechhub</div>
</body>
</html>
