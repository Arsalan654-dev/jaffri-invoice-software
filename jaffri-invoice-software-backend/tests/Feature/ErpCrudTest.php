<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\CashEntry;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

class ErpCrudTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('database.default', 'mysql');
        $this->app['config']->set('database.connections.mysql.database', 'yours_tech_hub');
        $this->app['config']->set('database.connections.mysql.username', 'root');
        $this->app['config']->set('database.connections.mysql.password', '');
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_customer_crud(): void
    {
        // Create
        $response = $this->actingAs($this->admin)->post('/customers', [
            'company_name' => 'Test Customer Ltd',
            'email' => 'test@testcustomer.com',
            'phone' => '+92-300-1234567',
            'city' => 'Lahore',
            'country' => 'Pakistan',
        ]);
        $customer = Customer::where('company_name', 'Test Customer Ltd')->first();
        $this->assertNotNull($customer, 'Customer not created');
        $this->assertTrue($customer->is_active, 'Customer should be active');
        $this->assertEquals($this->admin->id, $customer->created_by, 'created_by should be set');

        // Edit
        $response = $this->actingAs($this->admin)->put("/customers/{$customer->id}", [
            'company_name' => 'Test Customer Ltd Updated',
            'phone' => '+92-300-9999999',
        ]);
        $customer->refresh();
        $this->assertEquals('Test Customer Ltd Updated', $customer->company_name);

        // Delete
        $response = $this->actingAs($this->admin)->delete("/customers/{$customer->id}");
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_vendor_crud(): void
    {
        $response = $this->actingAs($this->admin)->post('/vendors', [
            'company_name' => 'Test Vendor Corp',
            'email' => 'vendor@test.com',
            'phone' => '+92-321-7654321',
            'balance' => 5000,
        ]);
        $vendor = Vendor::where('company_name', 'Test Vendor Corp')->first();
        $this->assertNotNull($vendor, 'Vendor not created');
        $this->assertTrue($vendor->is_active);
        $this->assertEquals(5000, $vendor->balance);

        $response = $this->actingAs($this->admin)->put("/vendors/{$vendor->id}", [
            'company_name' => 'Test Vendor Updated',
        ]);
        $vendor->refresh();
        $this->assertEquals('Test Vendor Updated', $vendor->company_name);

        $response = $this->actingAs($this->admin)->delete("/vendors/{$vendor->id}");
        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_invoice_crud(): void
    {
        $customer = Customer::where('is_active', true)->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($this->admin)->post('/invoices', [
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                ['description' => 'Web Development Service', 'quantity' => 1, 'unit_price' => 150000],
                ['description' => 'SEO Optimization', 'quantity' => 1, 'unit_price' => 50000],
            ],
        ]);
        $invoice = Invoice::where('invoice_number', 'like', 'INV-%')->latest()->first();
        $this->assertNotNull($invoice, 'Invoice not created');
        $this->assertEquals(2, $invoice->items()->count(), 'Invoice should have 2 items');
        $this->assertEquals('draft', $invoice->status);
    }

    public function test_quotation_crud(): void
    {
        $customer = Customer::where('is_active', true)->first();
        $response = $this->actingAs($this->admin)->post('/quotations', [
            'customer_id' => $customer->id,
            'notes' => 'Test quotation',
            'items' => [
                ['description' => 'Software License', 'quantity' => 5, 'unit_price' => 40000],
            ],
        ]);
        $this->assertDatabaseHas('quotations', ['customer_id' => $customer->id, 'notes' => 'Test quotation']);
    }

    public function test_purchase_order_crud(): void
    {
        $vendor = Vendor::where('is_active', true)->first();
        $this->assertNotNull($vendor);

        $response = $this->actingAs($this->admin)->post('/purchase-orders', [
            'vendor_id' => $vendor->id,
            'total' => 300000,
            'status' => 'draft',
        ]);
        $po = PurchaseOrder::where('po_number', 'like', 'PO-%')->latest()->first();
        $this->assertNotNull($po, 'Purchase Order not created');
    }

    public function test_cash_book_crud(): void
    {
        $bank = Bank::first();
        $this->assertNotNull($bank);

        $response = $this->actingAs($this->admin)->post('/finance/cash-book', [
            'type' => 'income',
            'description' => 'Test income entry',
            'amount' => 50000,
            'entry_date' => now()->format('Y-m-d'),
            'bank_id' => $bank->id,
        ]);
        $entry = CashEntry::where('description', 'Test income entry')->first();
        $this->assertNotNull($entry, 'Cash entry not created');

        $response = $this->actingAs($this->admin)->delete("/finance/cash-book/{$entry->id}");
        $this->assertDatabaseMissing('cash_entries', ['id' => $entry->id]);
    }

    public function test_ledger_crud(): void
    {
        $response = $this->actingAs($this->admin)->post('/finance/ledger', [
            'account_name' => 'Test Revenue Account',
            'type' => 'credit',
            'amount' => 100000,
            'description' => 'Test ledger entry',
            'entry_date' => now()->format('Y-m-d'),
        ]);
        $entry = LedgerEntry::where('description', 'Test ledger entry')->first();
        $this->assertNotNull($entry, 'Ledger entry not created');

        $response = $this->actingAs($this->admin)->put("/finance/ledger/{$entry->id}", [
            'account_name' => 'Test Revenue Account',
            'type' => 'credit',
            'amount' => 150000,
            'description' => 'Test ledger entry updated',
            'entry_date' => now()->format('Y-m-d'),
        ]);
        $entry->refresh();
        $this->assertEquals(150000, $entry->amount);

        $response = $this->actingAs($this->admin)->delete("/finance/ledger/{$entry->id}");
        $this->assertDatabaseMissing('ledger_entries', ['id' => $entry->id]);
    }

    public function test_invoice_mark_paid(): void
    {
        $invoice = Invoice::whereNotIn('status', ['paid', 'cancelled'])->first();
        if (! $invoice) {
            $this->markTestSkipped('No eligible invoice found');
        }

        $response = $this->actingAs($this->admin)->post("/invoices/{$invoice->id}/paid");
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_invoice_send(): void
    {
        $invoice = Invoice::where('status', 'draft')->first();
        if (! $invoice) {
            $this->markTestSkipped('No draft invoice found');
        }

        $response = $this->actingAs($this->admin)->post("/invoices/{$invoice->id}/send");
        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
    }

    public function test_invoice_cancel(): void
    {
        $invoice = Invoice::whereNotIn('status', ['paid', 'cancelled'])->first();
        if (! $invoice) {
            $this->markTestSkipped('No cancellable invoice found');
        }

        $response = $this->actingAs($this->admin)->post("/invoices/{$invoice->id}/cancel");
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
    }
}
