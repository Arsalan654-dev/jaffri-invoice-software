# Jaffri Invoice Software - Static Mirror (Bootstrap 5)

Frontend mirror of `demoyourtechhub.netlify.app` - a React Accounting/ERP demo, rebuilt with plain HTML, CSS, JavaScript and Bootstrap 5.

## Pages

| Page | File |
|------|------|
| Login | `index.html` |
| Dashboard | `dashboard.html` |
| Customers | `customers.html` |
| Vendors | `vendors.html` |
| Invoices | `invoices.html` |
| Quotations | `quotations.html` |
| Purchase Orders | `purchase-orders.html` |
| Inventory | `inventory.html` |
| Cash Book | `cashbook.html` |
| General Ledger | `general-ledger.html` |
| Party Ledger | `party-ledger.html` |
| Reports | `reports.html` |

## Demo Credentials

- **Email:** `admin@example.com`
- **Password:** `11111111`

## Structure

```
├── index.html          # Login page
├── dashboard.html
├── customers.html
├── vendors.html
├── invoices.html
├── quotations.html
├── purchase-orders.html
├── inventory.html
├── cashbook.html
├── general-ledger.html
├── party-ledger.html
├── reports.html
├── css/
│   └── style.css       # All custom styles (mirrors original)
├── js/
│   └── app.js          # Shared layout, auth, icons, helpers
├── vite.svg            # Favicon
```

## How to Run

Open `index.html` directly in a browser, or serve locally:

```bash
npx serve .
```

Login with demo credentials to access all pages. Data is persisted in `localStorage` just like the demo.