<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}{{ isset($reference) && $reference ? ' - '.$reference : '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #0f172a; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #4f46e5; padding-bottom: 18px; margin-bottom: 24px; }
        .brand h1 { font-size: 22px; color: #4f46e5; margin: 0; letter-spacing: -0.5px; }
        .brand p { color: #64748b; font-size: 11px; margin-top: 4px; }
        .meta { text-align: right; }
        .meta .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta .value { font-size: 16px; font-weight: bold; color: #0f172a; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 6px; background: #eef2ff; color: #4f46e5; }
        .fields { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
        .field { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; min-width: 160px; flex: 1 1 160px; }
        .field .label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .field .value { font-size: 12px; font-weight: 600; color: #0f172a; margin-top: 2px; word-break: break-word; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #4f46e5; color: #fff; text-align: left; padding: 10px 8px; font-size: 11px; font-weight: 600; }
        table.items th.right, table.items td.right { text-align: right; }
        table.items td { padding: 9px 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table.items tbody tr:nth-child(even) { background: #f8fafc; }
        .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals { width: 280px; }
        .totals .row { display: flex; justify-content: space-between; padding: 7px 12px; font-size: 12px; }
        .totals .row.grand { background: #4f46e5; color: #fff; font-weight: bold; font-size: 14px; border-radius: 4px; margin-top: 4px; }
        .totals .row .lbl { color: #64748b; }
        .totals .row.grand .lbl { color: #fff; }
        .notes { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; margin-bottom: 20px; }
        .notes h3 { font-size: 11px; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }
        .notes p { font-size: 11px; color: #334155; white-space: pre-wrap; }
        .footer { text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <h1>Jaffri Invoice Software</h1>
            <p>Your's Tech Hub &mdash; {{ $title }}</p>
        </div>
        <div class="meta">
            <div class="label">{{ $title }}</div>
            <div class="value">{{ $reference ?? '—' }}</div>
            @if(!empty($status))
                <span class="status">{{ $status }}</span>
            @endif
        </div>
    </div>

    <div class="fields">
        @foreach($fields as $label => $value)
            <div class="field">
                <div class="label">{{ $label }}</div>
                <div class="value">{!! $value !!}</div>
            </div>
        @endforeach
    </div>

    @if(!empty($items) && $items->count())
        <table class="items">
            <thead>
                <tr>
                    <th style="width:6%">#</th>
                    <th style="width:46%">{{ $itemColumns[0] ?? 'Description' }}</th>
                    @for($i = 1; $i < count($itemColumns); $i++)
                        <th class="right" style="width:{{ floor(48 / (count($itemColumns) - 1)) }}%">{{ $itemColumns[$i] }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @foreach($item as $cell)
                            <td class="{{ isset($cell['right']) && $cell['right'] ? 'right' : '' }}">{!! $cell['value'] !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($totals))
        <div class="totals-wrap">
            <div class="totals">
                @foreach($totals as $label => $value)
                    @php
                        $isGrand = is_array($value) && !empty($value['grand']);
                        $display = is_array($value) ? ($value['value'] ?? '') : $value;
                    @endphp
                    <div class="row {{ $isGrand ? 'grand' : '' }}">
                        <span class="lbl">{{ $label }}</span>
                        <span>{{ $display }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($notes))
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $notes }}</p>
        </div>
    @endif

    <div class="footer">
        Created By Jaffri Invoice Software &middot; {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
