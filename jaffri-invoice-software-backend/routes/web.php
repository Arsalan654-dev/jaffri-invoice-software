<?php

use App\Http\Controllers\Erp\AdminController;
use App\Http\Controllers\Erp\AuthController;
use App\Http\Controllers\Erp\CustomerController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\FinanceController;
use App\Http\Controllers\Erp\InventoryController;
use App\Http\Controllers\Erp\InvoiceController;
use App\Http\Controllers\Erp\PurchaseOrderController;
use App\Http\Controllers\Erp\QuotationController;
use App\Http\Controllers\Erp\ReportController;
use App\Http\Controllers\Erp\VendorController;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', fn () => redirect()->route('erp.login'));

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('erp.login');
    Route::post('/login', [AuthController::class, 'login'])->name('erp.login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('erp.password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('erp.password.forgot.post');
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('erp.password.verify-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('erp.password.verify-otp.post');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('erp.password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('erp.password.reset.post');
});

// Logout (auth required)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('erp.logout');

// Authenticated routes
Route::middleware(['auth', 'session.security'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('erp.dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analyticsData'])->name('erp.dashboard.analytics');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('erp.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('erp.profile.update');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('erp.password.change');

    // Customers
    Route::middleware('permission:client.read')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('erp.customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('erp.customers.create');
        Route::get('/customers/{customer}/pdf', [CustomerController::class, 'downloadPdf'])->name('erp.customers.pdf');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('erp.customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('erp.customers.edit');
    });
    Route::middleware('permission:client.create')->post('/customers', [CustomerController::class, 'store'])->name('erp.customers.store');
    Route::middleware('permission:client.update')->put('/customers/{customer}', [CustomerController::class, 'update'])->name('erp.customers.update');
    Route::middleware('permission:client.delete')->delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('erp.customers.destroy');

    // Vendors
    Route::middleware('permission:client.read')->group(function () {
        Route::get('/vendors', [VendorController::class, 'index'])->name('erp.vendors.index');
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('erp.vendors.create');
        Route::get('/vendors/{vendor}/pdf', [VendorController::class, 'downloadPdf'])->name('erp.vendors.pdf');
        Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('erp.vendors.show');
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('erp.vendors.edit');
    });
    Route::middleware('permission:client.create')->post('/vendors', [VendorController::class, 'store'])->name('erp.vendors.store');
    Route::middleware('permission:client.update')->put('/vendors/{vendor}', [VendorController::class, 'update'])->name('erp.vendors.update');
    Route::middleware('permission:client.delete')->delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('erp.vendors.destroy');

    // Quotations
    Route::middleware('permission:quotation.read')->group(function () {
        Route::get('/quotations', [QuotationController::class, 'index'])->name('erp.quotations.index');
        Route::get('/quotations/create', [QuotationController::class, 'create'])->name('erp.quotations.create');
        Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('erp.quotations.pdf');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('erp.quotations.show');
        Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('erp.quotations.edit');
    });
    Route::middleware('permission:quotation.create')->post('/quotations', [QuotationController::class, 'store'])->name('erp.quotations.store');
    Route::middleware('permission:quotation.update')->put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('erp.quotations.update');
    Route::middleware('permission:quotation.delete')->delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('erp.quotations.destroy');
    Route::middleware('permission:quotation.send')->post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('erp.quotations.send');
    Route::middleware('permission:quotation.convert')->post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('erp.quotations.convert');

    // Invoices
    Route::middleware('permission:invoice.read')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('erp.invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('erp.invoices.create');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('erp.invoices.show');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('erp.invoices.edit');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('erp.invoices.pdf');
    });
    Route::middleware('permission:invoice.create')->post('/invoices', [InvoiceController::class, 'store'])->name('erp.invoices.store');
    Route::middleware('permission:invoice.update')->put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('erp.invoices.update');
    Route::middleware('permission:invoice.cancel')->delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('erp.invoices.destroy');
    Route::middleware('permission:invoice.send')->post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('erp.invoices.send');
    Route::middleware('permission:invoice.update')->post('/invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('erp.invoices.paid');
    Route::middleware('permission:invoice.cancel')->post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('erp.invoices.cancel');

    // Purchase Orders
    Route::middleware('permission:purchase.read')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('erp.purchase-orders.index');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('erp.purchase-orders.create');
        Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('erp.purchase-orders.pdf');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('erp.purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('erp.purchase-orders.edit');
    });
    Route::middleware('permission:purchase.create')->post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('erp.purchase-orders.store');
    Route::middleware('permission:purchase.update')->put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('erp.purchase-orders.update');
    Route::middleware('permission:purchase.delete')->delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('erp.purchase-orders.destroy');

    // Inventory
    Route::middleware('permission:inventory.read')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('erp.inventory.index');
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('erp.inventory.create');
        Route::get('/inventory/{inventoryItem}/pdf', [InventoryController::class, 'downloadPdf'])->name('erp.inventory.pdf');
        Route::get('/inventory/{inventoryItem}', [InventoryController::class, 'show'])->name('erp.inventory.show');
        Route::get('/inventory/{inventoryItem}/edit', [InventoryController::class, 'edit'])->name('erp.inventory.edit');
    });
    Route::middleware('permission:inventory.create')->post('/inventory', [InventoryController::class, 'store'])->name('erp.inventory.store');
    Route::middleware('permission:inventory.update')->put('/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('erp.inventory.update');
    Route::middleware('permission:inventory.delete')->delete('/inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->name('erp.inventory.destroy');

    // Finance
    Route::middleware('permission:ledger.read')->group(function () {
        Route::get('/finance/cash-book', [FinanceController::class, 'cashBook'])->name('erp.finance.cash-book');
        Route::post('/finance/cash-book', [FinanceController::class, 'storeCashEntry'])->name('erp.finance.cash-book.store');
        Route::get('/finance/cash-book/{cashEntry}/pdf', [FinanceController::class, 'downloadCashEntryPdf'])->name('erp.finance.cash-book.pdf');
        Route::delete('/finance/cash-book/{cashEntry}', [FinanceController::class, 'destroyCashEntry'])->name('erp.finance.cash-book.destroy');
        Route::put('/finance/cash-book/{cashEntry}', [FinanceController::class, 'updateCashEntry'])->name('erp.finance.cash-book.update');
        Route::get('/finance/banks', [FinanceController::class, 'banks'])->name('erp.finance.banks');
        Route::post('/finance/banks', [FinanceController::class, 'storeBank'])->name('erp.finance.banks.store');
    });
    Route::middleware('permission:ledger.read')->group(function () {
        Route::get('/finance/ledger', [FinanceController::class, 'ledger'])->name('erp.finance.ledger');
        Route::post('/finance/ledger', [FinanceController::class, 'storeLedgerEntry'])->name('erp.finance.ledger.store');
        Route::get('/finance/ledger/{ledgerEntry}/pdf', [FinanceController::class, 'downloadLedgerPdf'])->name('erp.finance.ledger.pdf');
        Route::put('/finance/ledger/{ledgerEntry}', [FinanceController::class, 'updateLedgerEntry'])->name('erp.finance.ledger.update');
        Route::delete('/finance/ledger/{ledgerEntry}', [FinanceController::class, 'destroyLedgerEntry'])->name('erp.finance.ledger.destroy');
    });

    // Reports
    Route::middleware('permission:report.read')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('erp.reports.index');
        Route::get('/reports/analytics', [ReportController::class, 'analyticsData'])->name('erp.reports.analytics');
        Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('erp.reports.sales');
        Route::get('/reports/purchases', [ReportController::class, 'purchaseReport'])->name('erp.reports.purchases');
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('erp.reports.profit-loss');
        Route::get('/reports/pnl/pdf', [ReportController::class, 'pnlPdf'])->name('erp.reports.pnl.pdf');
        Route::get('/reports/balance-sheet/pdf', [ReportController::class, 'balanceSheetPdf'])->name('erp.reports.balance-sheet.pdf');
        Route::get('/reports/aging/pdf', [ReportController::class, 'agingPdf'])->name('erp.reports.aging.pdf');
        Route::get('/reports/inventory-valuation/pdf', [ReportController::class, 'inventoryValuationPdf'])->name('erp.reports.inventory-valuation.pdf');
    });

    // Admin
    Route::prefix('admin')->name('erp.admin.')->group(function () {
        Route::middleware('permission:user.manage')->group(function () {
            Route::get('/users', [AdminController::class, 'users'])->name('users.index');
            Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
            Route::get('/users/{user}/pdf', [AdminController::class, 'downloadUserPdf'])->name('users.pdf');
            Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
            Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
            Route::put('/users/{user}/permissions', [AdminController::class, 'updateUserPermissions'])->name('users.permissions');
        });

        Route::middleware('permission:role.manage')->group(function () {
            Route::get('/roles', [AdminController::class, 'roles'])->name('roles.index');
            Route::get('/roles/create', [AdminController::class, 'createRole'])->name('roles.create');
            Route::post('/roles', [AdminController::class, 'storeRole'])->name('roles.store');
            Route::get('/roles/{role}/edit', [AdminController::class, 'editRole'])->name('roles.edit');
            Route::put('/roles/{role}', [AdminController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{role}', [AdminController::class, 'destroyRole'])->name('roles.delete');
        });

        Route::middleware('permission:audit.read')->group(function () {
            Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/audit-logs/{auditLog}/pdf', [AdminController::class, 'downloadAuditLogPdf'])->name('audit-logs.pdf');
        });
    });

    // API routes for dynamic dropdowns
    Route::get('/api/customers', function () {
        return response()->json(
            Customer::where('is_active', true)
                ->orderBy('company_name')
                ->select('id', 'company_name')
                ->get()
        );
    })->name('erp.api.customers');

    Route::get('/api/vendors', function () {
        return response()->json(
            Vendor::where('is_active', true)
                ->orderBy('company_name')
                ->select('id', 'company_name')
                ->get()
        );
    })->name('erp.api.vendors');
});
