# Laravel Backend Plan - Demo ERP Mirror

## Project Overview
Build a complete Laravel 13 REST API backend for the Demo ERP frontend mirror at `d:\yourstechhub`. The backend will replace localStorage with persistent SQLite database and provide all CRUD + reporting functionality via API endpoints.

## Frontend Features to Backend Mapping

| Frontend Page | Data Entities | Key Operations |
|---------------|---------------|----------------|
| Login | User (hardcoded admin) | Session auth |
| Dashboard | Invoices, Customers, Inventory, Cash Entries | Aggregated stats, charts |
| Customers | Customer | CRUD + balance tracking |
| Vendors | Vendor | CRUD + balance tracking |
| Invoices | Invoice, InvoiceItem | Full CRUD + items + totals + PDF |
| Quotations | Quotation | CRUD |
| Purchase Orders | PurchaseOrder | CRUD |
| Inventory | InventoryItem | CRUD + image upload + reorder level |
| Cash Book | CashEntry, Bank | CRUD entries + bank mgmt + running balance |
| General Ledger | (Computed from Invoices + CashEntries) | Filter by account |
| Party Ledger | (Computed per Customer/Vendor) | Date range + opening/closing |
| Reports | (Computed aggregations) | P&L, BS, Aging, Inventory - PDF |

---

## Phase 1: Database Design (Migrations)

### 1.1 Core Tables
```sql
users (existing - use for admin login)
customers
vendors
invoices
invoice_items
quotations
purchase_orders
inventory_items
cash_entries
banks
```

### 1.2 Migration Details

**customers**
- id, name, phone, email, opening_balance (decimal), balance (decimal, computed), created_at, updated_at

**vendors**  
- id, name, phone, email, opening_balance (decimal), balance (decimal, computed), created_at, updated_at

**invoices**
- id, invoice_number (unique), type (receivable/payable), party_type (customer/vendor), party_id, party_name, date, due_date, subtotal, discount_type (none/percentage/fixed), discount_value, tax_rate, tax_amount, total, paid_amount, payment_mode (cash/online/cheque/none), status (paid/partial/pending/overdue), notes, created_at, updated_at

**invoice_items**
- id, invoice_id, description, quantity, unit (pcs/kg), unit_price, amount, created_at

**quotations**
- id, quotation_number, customer_id, customer_name, date, expiry_date, total, status (pending/accepted/rejected/expired), notes, created_at, updated_at

**purchase_orders**
- id, po_number, vendor_id, vendor_name, date, amount, status (pending/ordered/received/cancelled), notes, created_at, updated_at

**inventory_items**
- id, name, sku (unique), description, quantity, unit_price, reorder_level, image_path, created_at, updated_at

**cash_entries**
- id, entry_number, date, type (receipt/payment), mode (cash/online/cheque), amount, party_type (customer/vendor/none), party_id (nullable), party_name, customer_id (nullable), vendor_id (nullable), description, reference, cheque_number, bank_id (nullable), balance (running), created_at, updated_at

**banks**
- id, name, initial_balance, current_balance, created_at, updated_at

---

## Phase 2: Models & Relationships

```php
// Customer
hasMany(Invoice::class) - where type=receivable
hasMany(CashEntry::class) - where party_type=customer

// Vendor  
hasMany(Invoice::class) - where type=payable
hasMany(CashEntry::class) - where party_type=vendor

// Invoice
belongsTo(Customer/Vendor - polymorphic or party_type+party_id)
hasMany(InvoiceItem::class)

// InvoiceItem
belongsTo(Invoice::class)

// Quotation
belongsTo(Customer::class)

// PurchaseOrder
belongsTo(Vendor::class)

// InventoryItem
// standalone

// CashEntry
belongsTo(Customer::class, nullable)
belongsTo(Vendor::class, nullable)
belongsTo(Bank::class, nullable)

// Bank
hasMany(CashEntry::class)
```

---

## Phase 3: API Routes Structure

```
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/user

// Dashboard
GET    /api/dashboard/stats
GET    /api/dashboard/recent-invoices
GET    /api/dashboard/chart-data

// Customers
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
DELETE /api/customers/{id}

// Vendors
GET    /api/vendors
POST   /api/vendors
GET    /api/vendors/{id}
PUT    /api/vendors/{id}
DELETE /api/vendors/{id}

// Invoices
GET    /api/invoices (with filters: type, search, status)
POST   /api/invoices
GET    /api/invoices/{id}
PUT    /api/invoices/{id}
DELETE /api/invoices/{id}
GET    /api/invoices/{id}/pdf

// Quotations
GET    /api/quotations
POST   /api/quotations
GET    /api/quotations/{id}
PUT    /api/quotations/{id}
DELETE /api/quotations/{id}

// Purchase Orders
GET    /api/purchase-orders
POST   /api/purchase-orders
GET    /api/purchase-orders/{id}
PUT    /api/purchase-orders/{id}
DELETE /api/purchase-orders/{id}

// Inventory
GET    /api/inventory
POST   /api/inventory (multipart for image)
GET    /api/inventory/{id}
PUT    /api/inventory/{id}
DELETE /api/inventory/{id}

// Cash Book
GET    /api/cash-entries (with filters: date range, mode, type)
POST   /api/cash-entries
GET    /api/cash-entries/{id}
PUT    /api/cash-entries/{id}
DELETE /api/cash-entries/{id}
GET    /api/cash-entries/summary
GET    /api/cash-entries/pdf

// Banks
GET    /api/banks
POST   /api/banks
GET    /api/banks/{id}
PUT    /api/banks/{id}
DELETE /api/banks/{id}

// General Ledger
GET    /api/general-ledger (filter: account, date range)
GET    /api/general-ledger/pdf

// Party Ledger
GET    /api/party-ledger (type: customer|vendor, id, date range)
GET    /api/party-ledger/pdf
GET    /api/party-ledger/all-customers-pdf

// Reports
GET    /api/reports/profit-loss
GET    /api/reports/balance-sheet
GET    /api/reports/ar-aging
GET    /api/reports/inventory-valuation
GET    /api/reports/profit-loss/pdf
GET    /api/reports/balance-sheet/pdf
GET    /api/reports/ar-aging/pdf
GET    /api/reports/inventory-valuation/pdf
```

---

## Phase 4: Implementation Steps

### Step 1: Run migrations for all new tables
```bash
php artisan make:migration create_customers_table
php artisan make:migration create_vendors_table
php artisan make:migration create_invoices_table
php artisan make:migration create_invoice_items_table
php artisan make:migration create_quotations_table
php artisan make:migration create_purchase_orders_table
php artisan make:migration create_inventory_items_table
php artisan make:migration create_cash_entries_table
php artisan make:migration create_banks_table
```

### Step 2: Create Models with relationships
```bash
php artisan make:model Customer -m
php artisan make:model Vendor -m
php artisan make:model Invoice -m
php artisan make:model InvoiceItem -m
php artisan make:model Quotation -m
php artisan make:model PurchaseOrder -m
php artisan make:model InventoryItem -m
php artisan make:model CashEntry -m
php artisan make:model Bank -m
```

### Step 3: Create API Controllers
```bash
php artisan make:controller Api/AuthController
php artisan make:controller Api/DashboardController
php artisan make:controller Api/CustomerController --api
php artisan make:controller Api/VendorController --api
php artisan make:controller Api/InvoiceController --api
php artisan make:controller Api/QuotationController --api
php artisan make:controller Api/PurchaseOrderController --api
php artisan make:controller Api/InventoryController --api
php artisan make:controller Api/CashEntryController --api
php artisan make:controller Api/BankController --api
php artisan make:controller Api/LedgerController
php artisan make:controller Api/PartyLedgerController
php artisan make:controller Api/ReportController
```

### Step 4: Register API routes in routes/api.php

### Step 5: Implement PDF generation
- Use `barryvdh/laravel-dompdf` or `niklasravn/laravel-pdf`

### Step 6: Configure Sanctum for API auth
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Step 7: Seed admin user
```bash
php artisan make:seeder AdminUserSeeder
```

---

## Phase 5: Frontend Integration

1. Update frontend `js/app.js` API base URL
2. Replace localStorage calls with `fetch()` to API endpoints
3. Handle auth token in requests
4. Update login flow to use API

---

## Phase 6: Testing & Validation

- Test all CRUD operations
- Verify running balances in Cash Book
- Verify General Ledger auto-generation
- Verify Party Ledger calculations
- Test all PDF reports
- Test image upload for inventory

---

## Estimated Timeline

| Phase | Tasks | Est. Time |
|-------|-------|-----------|
| 1: Migrations | 9 migrations | 30 min |
| 2: Models | 9 models + relationships | 45 min |
| 3: Controllers | 12 controllers | 2-3 hours |
| 4: Routes & Auth | Routes + Sanctum | 30 min |
| 5: PDF Reports | 4 report PDFs | 1 hour |
| 6: Frontend Integration | Update all pages | 1-2 hours |
| 7: Testing | Full flow test | 1 hour |

**Total: ~6-7 hours**

---

## Ready to Start?

Shall I begin with **Phase 1: Migrations**? We'll create all 9 migration files, then models, then controllers step by step. Each step I'll show you the code and you can review before moving to the next.