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
        <p>Inventory Valuation</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr><th>Item</th><th>SKU</th><th>Qty</th><th>Unit Price (Rs.)</th><th>Value (Rs.)</th></tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ $item['qty'] }}</td>
                    <td>{{ number_format($item['price'], 2) }}</td>
                    <td>{{ number_format($item['value'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="color:#94a3b8">No inventory items</td></tr>
            @endforelse
            <tr><td colspan="4" class="total">Total Stock Value</td><td class="total">{{ number_format($totalValue, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">Created By Yourtechhub</div>
</body>
</html>
