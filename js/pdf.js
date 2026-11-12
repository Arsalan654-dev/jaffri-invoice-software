/* ===== Print/PDF helper for Jaffri Invoice Software mirror ===== */
// Opens a print-friendly view and triggers browser print -> "Save as PDF"

function printReport(title, tableHtml, summaryRows) {
  var w = window.open('', '_blank', 'width=1000,height=700');
  if (!w) {
    showToast('Please allow popups to generate PDF.', 'error');
    return;
  }
  var summaryHtml = (summaryRows || []).map(function (r) {
    return '<div class="summary-line"><span>' + r.label + '</span><strong>' + r.value + '</strong></div>';
  }).join('');

  w.document.write(
    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + title + '</title>' +
    '<style>' +
    'body{font-family:"Plus Jakarta Sans",Arial,sans-serif;color:#0f172a;padding:40px;margin:0;background:#fff}' +
    'h1{font-size:1.8rem;margin:0 0 4px;color:#0f172a}' +
    '.sub{color:#64748b;font-size:.875rem;margin-bottom:24px}' +
    '.header{padding-bottom:16px;border-bottom:3px solid #4f46e5;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start}' +
    '.brand{font-weight:800;color:#4f46e5;font-size:1.2rem}' +
    '.summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px}' +
    '.summary-line{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;font-size:.8rem;color:#64748b;font-weight:600}' +
    '.summary-line strong{display:block;font-size:1.2rem;color:#0f172a;margin-top:4px}' +
    'table{width:100%;border-collapse:collapse;font-size:.875rem}' +
    'th{background:#f1f5f9;text-align:left;padding:12px;border:1px solid #cbd5e1;text-transform:uppercase;font-size:.72rem;letter-spacing:.05em;color:#475569}' +
    'td{padding:10px 12px;border:1px solid #e2e8f0}' +
    'tfoot td{background:#f8fafc;font-weight:800}' +
    '.footer{margin-top:32px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:.75rem;color:#64748b;display:flex;justify-content:space-between}' +
    '@media print{body{padding:20px}}' +
    '</style></head><body>' +
    '<div class="header">' +
    '  <div><div class="brand">Jaffri Invoice Software</div><h1>' + title + '</h1><div class="sub">Generated: ' + new Date().toLocaleString() + '</div></div>' +
    '</div>' +
    (summaryHtml ? '<div class="summary">' + summaryHtml + '</div>' : '') +
    tableHtml +
    '<div class="footer"><span>Jaffri Invoice Software - Jaffri Invoice Software</span><span>+92 335 2352689</span></div>' +
    '</body></html>'
  );
  w.document.close();
  w.focus();
  setTimeout(function () {
    w.print();
  }, 400);
}

// Invoice/Single PDF with items
function printInvoicePDF(inv) {
  var itemsRows = (inv.items || []).map(function (it) {
    return '<tr><td>' + escapeHtml(it.desc) + '</td><td>' + it.qty + ' ' + it.unit + '</td><td>' + formatMoney(it.price) + '</td><td>' + formatMoney(it.amount) + '</td></tr>';
  }).join('') || '<tr><td colspan="4" style="color:#94a3b8">No items</td></tr>';

  var table =
    '<table>' +
    '<thead><tr><th>Description</th><th>Qty</th><th>Price</th><th>Amount</th></tr></thead>' +
    '<tbody>' + itemsRows + '</tbody>' +
    '<tfoot>' +
    '<tr><td colspan="3" style="text-align:right">Subtotal</td><td>' + formatMoney(inv.subtotal !== undefined ? inv.subtotal : inv.total) + '</td></tr>' +
    '<tr><td colspan="3" style="text-align:right">Tax</td><td>' + formatMoney(inv.tax || 0) + '</td></tr>' +
    '<tr><td colspan="3" style="text-align:right">Total</td><td>' + formatMoney(inv.total) + '</td></tr>' +
    '<tr><td colspan="3" style="text-align:right">Paid</td><td>' + formatMoney(inv.paid || 0) + '</td></tr>' +
    '<tr><td colspan="3" style="text-align:right">Balance Due</td><td>' + formatMoney(inv.total - (inv.paid || 0)) + '</td></tr>' +
    '</tfoot></table>';

  var w = window.open('', '_blank', 'width=1000,height=700');
  if (!w) { showToast('Please allow popups.', 'error'); return; }
  w.document.write(
    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>INVOICE ' + inv.number + '</title>' +
    '<style>' +
    'body{font-family:"Plus Jakarta Sans",Arial,sans-serif;color:#0f172a;padding:40px;margin:0;background:#fff}' +
    '.top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:32px}' +
    '.brand{color:#4f46e5;font-size:2rem;font-weight:800;letter-spacing:-.03em}' +
    '.inv-num{text-align:right}.inv-num h1{font-size:1.5rem;margin:0 0 4px}.inv-num p{color:#64748b;margin:2px 0;font-size:.875rem}' +
    '.meta{display:flex;gap:40px;margin-bottom:24px;padding:16px 20px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0}' +
    '.meta div{flex:1}.meta label{font-size:.7rem;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.05em;display:block}.meta span{font-weight:700;font-size:.95rem}' +
    'table{width:100%;border-collapse:collapse;font-size:.875rem}' +
    'th{background:#eef2ff;color:#3730a3;text-align:left;padding:12px;border-bottom:2px solid #c7d2fe}' +
    'td{padding:12px;border-bottom:1px solid #e2e8f0}' +
    'tfoot td{font-weight:700;padding:10px 12px}' +
    '.status{display:inline-block;padding:6px 14px;border-radius:20px;font-weight:700;font-size:.8rem}' +
    '.status.paid{background:#f0fdf4;color:#15803d}.status.pending{background:#fff7ed;color:#c2410c}' +
    '.notes{margin-top:24px;padding:16px;background:#f8fafc;border-radius:10px;font-size:.85rem;color:#475569}' +
    '.footer{margin-top:40px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:.75rem;color:#64748b;display:flex;justify-content:space-between}' +
    '@media print{body{padding:20px}}' +
    '</style></head><body>' +
    '<div class="top"><div class="brand">Jaffri Invoice Software</div><div class="inv-num"><h1>' + (inv.type === 'receivable' ? 'INVOICE' : 'BILL') + '</h1><p>' + inv.number + '</p><p>Date: ' + inv.date + '</p><p>Due: ' + inv.dueDate + '</p></div></div>' +
    '<div class="meta"><div><label>Party</label><span>' + escapeHtml(inv.party) + '</span></div><div><label>Status</label><span class="status ' + inv.status + '">' + inv.status + '</span></div><div><label>Payment</label><span>' + inv.method + '</span></div></div>' +
    table +
    (inv.notes ? '<div class="notes"><strong>Notes:</strong> ' + escapeHtml(inv.notes) + '</div>' : '') +
    '<div class="footer"><span>Jaffri Invoice Software - Jaffri Invoice Software</span><span>+92 335 2352689</span></div>' +
    '</body></html>'
  );
  w.document.close();
  w.focus();
  setTimeout(function () { w.print(); }, 400);
}

function escapeHtml(str) {
  var div = document.createElement('div');
  div.textContent = str || '';
  return div.innerHTML;
}