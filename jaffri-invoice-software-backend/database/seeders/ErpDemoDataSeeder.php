<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\CashEntry;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpDemoDataSeeder extends Seeder
{
    private User $adminUser;

    public function run(): void
    {
        $this->adminUser = User::where('email', 'admin@yourstechhub.com')->first();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        LedgerEntry::query()->forceDelete();
        CashEntry::query()->forceDelete();
        InvoiceItem::query()->forceDelete();
        Invoice::query()->forceDelete();
        QuotationItem::query()->forceDelete();
        Quotation::query()->forceDelete();
        PurchaseOrderItem::query()->forceDelete();
        PurchaseOrder::query()->forceDelete();
        InventoryItem::query()->forceDelete();
        Bank::query()->forceDelete();
        Customer::query()->forceDelete();
        Vendor::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::transaction(function () {
            $customers = $this->seedCustomers();
            $vendors = $this->seedVendors();
            $items = $this->seedInventoryItems($vendors);
            $banks = $this->seedBanks();
            $quotations = $this->seedQuotations($customers, $items);
            $invoices = $this->seedInvoices($customers, $items, $quotations);
            $this->seedPurchaseOrders($vendors, $items);
            $this->seedCashEntries($banks);
            $this->seedLedgerEntries();
        });
    }

    private function seedCustomers(): array
    {
        $customersData = [
            [
                'company_name' => 'Systems Limited',
                'contact_person' => 'Aezaz Hussain',
                'email' => 'aezaz@systems.com.pk',
                'phone' => '+92-21-111-111-111',
                'address' => '16-C, Sunset Boulevard',
                'city' => 'Karachi',
                'country' => 'Pakistan',
                'tax_number' => 'PK1234567890',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Netsol Technologies',
                'contact_person' => 'Salim Ghauri',
                'email' => 'salim@netsol.com',
                'phone' => '+92-42-3511-1111',
                'address' => '23-EMA Tower, Shahrah-e-Faisal',
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'tax_number' => 'PK2234567891',
                'payment_terms' => 'Net 45',
            ],
            [
                'company_name' => 'Techlogix',
                'contact_person' => 'Farrukh Mazhar',
                'email' => 'farrukh@techlogix.com',
                'phone' => '+92-51-261-2612',
                'address' => '72-G, Main Jinnah Avenue',
                'city' => 'Islamabad',
                'country' => 'Pakistan',
                'tax_number' => 'PK3234567892',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Avanza Solutions',
                'contact_person' => 'Atif Azim',
                'email' => 'atif@avanza.com.pk',
                'phone' => '+92-21-3452-5000',
                'address' => '9th Floor, Garnet Tower',
                'city' => 'Karachi',
                'country' => 'Pakistan',
                'tax_number' => 'PK4234567893',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'TRG Pakistan',
                'contact_person' => 'Zia Chishti',
                'email' => 'zia@trg.com',
                'phone' => '+92-51-111-874-111',
                'address' => '1501, Emaar Business Park',
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'tax_number' => 'PK5234567894',
                'payment_terms' => 'Net 30',
            ],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $data['created_by'] = $this->adminUser->id;
            $customers[] = Customer::create($data);
        }

        return $customers;
    }

    private function seedVendors(): array
    {
        $vendorsData = [
            [
                'company_name' => 'Infinix Technologies',
                'contact_person' => 'Ahmed Khan',
                'email' => 'ahmed@infinix.com.pk',
                'phone' => '+92-42-3713-0101',
                'address' => '45-M, Block C, Gulberg III',
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'tax_number' => 'PK1000000001',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'TechArena International',
                'contact_person' => 'Usman Ali',
                'email' => 'usman@techarena.com',
                'phone' => '+92-21-3456-7890',
                'address' => '22-S, Tariq Road',
                'city' => 'Karachi',
                'country' => 'Pakistan',
                'tax_number' => 'PK1000000002',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'Digital World Distributors',
                'contact_person' => 'Hassan Raza',
                'email' => 'hassan@digitalworld.pk',
                'phone' => '+92-51-444-5566',
                'address' => '78-F, Blue Area',
                'city' => 'Islamabad',
                'country' => 'Pakistan',
                'tax_number' => 'PK1000000003',
                'payment_terms' => 'Net 45',
            ],
        ];

        $vendors = [];
        foreach ($vendorsData as $data) {
            $data['created_by'] = $this->adminUser->id;
            $vendors[] = Vendor::create($data);
        }

        return $vendors;
    }

    private function seedInventoryItems(array $vendors): array
    {
        $itemsData = [
            [
                'sku' => 'LAPTOP-001',
                'name' => 'Dell Latitude 5540 Laptop',
                'description' => 'Intel Core i7, 16GB RAM, 512GB SSD, 15.6" FHD',
                'category' => 'Laptops',
                'quantity' => 25,
                'unit' => 'pcs',
                'min_stock_level' => 5,
                'purchase_price' => 285000.00,
                'selling_price' => 335000.00,
                'vendor_id' => $vendors[0]->id,
            ],
            [
                'sku' => 'LAPTOP-002',
                'name' => 'HP EliteBook 840 G10',
                'description' => 'Intel Core i5, 8GB RAM, 256GB SSD, 14" FHD',
                'category' => 'Laptops',
                'quantity' => 40,
                'unit' => 'pcs',
                'min_stock_level' => 10,
                'purchase_price' => 225000.00,
                'selling_price' => 265000.00,
                'vendor_id' => $vendors[0]->id,
            ],
            [
                'sku' => 'PC-001',
                'name' => 'Custom Desktop PC Build',
                'description' => 'Intel Core i5-13400, 16GB DDR5, 1TB NVMe, RTX 3060',
                'category' => 'Desktops',
                'quantity' => 15,
                'unit' => 'pcs',
                'min_stock_level' => 3,
                'purchase_price' => 195000.00,
                'selling_price' => 235000.00,
                'vendor_id' => $vendors[1]->id,
            ],
            [
                'sku' => 'MON-001',
                'name' => 'Dell UltraSharp U2723QE 27" Monitor',
                'description' => '4K UHD, USB-C Hub, IPS Black, 98% DCI-P3',
                'category' => 'Monitors',
                'quantity' => 30,
                'unit' => 'pcs',
                'min_stock_level' => 5,
                'purchase_price' => 125000.00,
                'selling_price' => 155000.00,
                'vendor_id' => $vendors[1]->id,
            ],
            [
                'sku' => 'PRINTER-001',
                'name' => 'HP LaserJet Pro MFP 4103fdn',
                'description' => 'Color Laser, Print/Scan/Copy/Fax, Duplex',
                'category' => 'Printers',
                'quantity' => 12,
                'unit' => 'pcs',
                'min_stock_level' => 2,
                'purchase_price' => 95000.00,
                'selling_price' => 120000.00,
                'vendor_id' => $vendors[2]->id,
            ],
            [
                'sku' => 'NETWORK-001',
                'name' => 'Ubiquiti UniFi Dream Machine Pro',
                'description' => 'Enterprise Router/Firewall with 10G SFP+',
                'category' => 'Networking',
                'quantity' => 8,
                'unit' => 'pcs',
                'min_stock_level' => 2,
                'purchase_price' => 85000.00,
                'selling_price' => 110000.00,
                'vendor_id' => $vendors[2]->id,
            ],
            [
                'sku' => 'SERVER-001',
                'name' => 'Dell PowerEdge T350 Tower Server',
                'description' => 'Xeon E-2334, 32GB ECC, 2x1TB SAS RAID',
                'category' => 'Servers',
                'quantity' => 5,
                'unit' => 'pcs',
                'min_stock_level' => 1,
                'purchase_price' => 450000.00,
                'selling_price' => 550000.00,
                'vendor_id' => $vendors[0]->id,
            ],
            [
                'sku' => 'CABLE-001',
                'name' => 'Cat6 UTP Ethernet Cable (100m)',
                'description' => 'UTP Cat6, 4 Pair, 23AWG, 305m box',
                'category' => 'Accessories',
                'quantity' => 100,
                'unit' => 'rolls',
                'min_stock_level' => 20,
                'purchase_price' => 12000.00,
                'selling_price' => 16000.00,
                'vendor_id' => $vendors[1]->id,
            ],
            [
                'sku' => 'SWITCH-001',
                'name' => 'Cisco Catalyst 1000 24-Port Switch',
                'description' => '24x 1GbE, 4x SFP, Managed, PoE+',
                'category' => 'Networking',
                'quantity' => 18,
                'unit' => 'pcs',
                'min_stock_level' => 3,
                'purchase_price' => 65000.00,
                'selling_price' => 85000.00,
                'vendor_id' => $vendors[2]->id,
            ],
            [
                'sku' => 'SOFTWARE-001',
                'name' => 'Microsoft Office 365 Business',
                'description' => 'Annual Subscription, Per User, 1 Year License',
                'category' => 'Software',
                'quantity' => 200,
                'unit' => 'licenses',
                'min_stock_level' => 50,
                'purchase_price' => 18000.00,
                'selling_price' => 24000.00,
                'vendor_id' => $vendors[0]->id,
            ],
        ];

        $items = [];
        foreach ($itemsData as $data) {
            $items[] = InventoryItem::create($data);
        }

        return $items;
    }

    private function seedBanks(): array
    {
        $banksData = [
            [
                'name' => 'Habib Bank Limited',
                'account_number' => '1234-5678-9012',
                'account_name' => "Your's Tech Hub - Main",
                'balance' => 2500000.00,
                'currency' => 'PKR',
                'is_active' => true,
            ],
            [
                'name' => 'MCB Bank Limited',
                'account_number' => '5678-1234-5678',
                'account_name' => "Your's Tech Hub - Payroll",
                'balance' => 1200000.00,
                'currency' => 'PKR',
                'is_active' => true,
            ],
            [
                'name' => 'United Bank Limited',
                'account_number' => '9876-5432-1098',
                'account_name' => "Your's Tech Hub - Operations",
                'balance' => 850000.00,
                'currency' => 'PKR',
                'is_active' => true,
            ],
        ];

        $banks = [];
        foreach ($banksData as $data) {
            $banks[] = Bank::create($data);
        }

        return $banks;
    }

    private function seedQuotations(array $customers, array $items): array
    {
        $quotationsData = [
            [
                'quotation_number' => 'QUO-2026-001',
                'customer_id' => $customers[0]->id,
                'status' => 'accepted',
                'valid_until' => now()->addDays(30),
                'notes' => 'Bulk laptop order for new office setup',
                'items' => [
                    ['description' => 'Dell Latitude 5540 Laptop',     'quantity' => 10, 'unit_price' => 335000.00, 'discount' => 0, 'tax' => 234500.00, 'total' => 3584500.00],
                    ['description' => 'Dell UltraSharp U2723QE 27"',  'quantity' => 10, 'unit_price' => 155000.00, 'discount' => 0, 'tax' => 108500.00, 'total' => 1658500.00],
                ],
            ],
            [
                'quotation_number' => 'QUO-2026-002',
                'customer_id' => $customers[1]->id,
                'status' => 'sent',
                'valid_until' => now()->addDays(15),
                'notes' => 'Server upgrade project',
                'items' => [
                    ['description' => 'Dell PowerEdge T350 Server',   'quantity' => 3, 'unit_price' => 550000.00, 'discount' => 50000.00, 'tax' => 330000.00, 'total' => 1930000.00],
                    ['description' => 'Ubiquiti UniFi Dream Machine', 'quantity' => 2, 'unit_price' => 110000.00, 'discount' => 0, 'tax' => 44000.00, 'total' => 264000.00],
                ],
            ],
            [
                'quotation_number' => 'QUO-2026-003',
                'customer_id' => $customers[2]->id,
                'status' => 'draft',
                'valid_until' => now()->addDays(30),
                'notes' => 'Office IT infrastructure setup',
                'items' => [
                    ['description' => 'HP EliteBook 840 G10',         'quantity' => 5, 'unit_price' => 265000.00, 'discount' => 0, 'tax' => 92750.00, 'total' => 1417750.00],
                    ['description' => 'Cisco Catalyst 1000 Switch',   'quantity' => 2, 'unit_price' => 85000.00, 'discount' => 0, 'tax' => 34000.00, 'total' => 204000.00],
                    ['description' => 'Cat6 Ethernet Cable (rolls)',  'quantity' => 10, 'unit_price' => 16000.00, 'discount' => 0, 'tax' => 16000.00, 'total' => 176000.00],
                ],
            ],
            [
                'quotation_number' => 'QUO-2026-004',
                'customer_id' => $customers[3]->id,
                'status' => 'converted',
                'valid_until' => now()->subDays(5),
                'notes' => 'Software licenses annual renewal',
                'items' => [
                    ['description' => 'Microsoft Office 365 License', 'quantity' => 50, 'unit_price' => 24000.00, 'discount' => 50000.00, 'tax' => 34000.00, 'total' => 1184000.00],
                ],
            ],
            [
                'quotation_number' => 'QUO-2026-005',
                'customer_id' => $customers[4]->id,
                'status' => 'rejected',
                'valid_until' => now()->subDays(10),
                'notes' => 'Desktop PC order (rejected - budget constraints)',
                'items' => [
                    ['description' => 'Custom Desktop PC Build',      'quantity' => 20, 'unit_price' => 235000.00, 'discount' => 100000.00, 'tax' => 310000.00, 'total' => 4910000.00],
                ],
            ],
        ];

        $quotations = [];
        foreach ($quotationsData as $data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $subtotal = collect($itemsData)->sum('total');
            $taxAmount = collect($itemsData)->sum('tax');
            $discountAmount = collect($itemsData)->sum('discount');

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['discount_amount'] = $discountAmount;
            $data['tax_rate'] = 17.00;
            $data['total'] = $subtotal;
            $data['created_by'] = $this->adminUser->id;

            $quotation = Quotation::create($data);

            foreach ($itemsData as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                QuotationItem::create($itemData);
            }

            $quotations[] = $quotation;
        }

        return $quotations;
    }

    private function seedInvoices(array $customers, array $items, array $quotations): array
    {
        $invoicesData = [
            [
                'invoice_number' => 'INV-2026-001',
                'customer_id' => $customers[0]->id,
                'quotation_id' => $quotations[0]->id,
                'status' => 'paid',
                'due_date' => now()->addDays(30),
                'amount_paid' => 5243000.00,
                'notes' => 'Payment received via bank transfer',
                'items' => [
                    ['description' => 'Dell Latitude 5540 Laptop',    'quantity' => 10, 'unit_price' => 335000.00, 'discount' => 0, 'tax' => 234500.00, 'total' => 3584500.00],
                    ['description' => 'Dell UltraSharp U2723QE 27"', 'quantity' => 10, 'unit_price' => 155000.00, 'discount' => 0, 'tax' => 108500.00, 'total' => 1658500.00],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'customer_id' => $customers[1]->id,
                'status' => 'sent',
                'due_date' => now()->addDays(15),
                'amount_paid' => 0,
                'notes' => 'Awaiting payment',
                'items' => [
                    ['description' => 'Dell PowerEdge T350 Server',   'quantity' => 2, 'unit_price' => 550000.00, 'discount' => 0, 'tax' => 187000.00, 'total' => 1287000.00],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'customer_id' => $customers[2]->id,
                'status' => 'overdue',
                'due_date' => now()->subDays(15),
                'amount_paid' => 500000.00,
                'notes' => 'Partial payment received, remainder overdue',
                'items' => [
                    ['description' => 'HP EliteBook 840 G10',         'quantity' => 3, 'unit_price' => 265000.00, 'discount' => 0, 'tax' => 135150.00, 'total' => 930150.00],
                    ['description' => 'Cisco Catalyst 1000 Switch',   'quantity' => 1, 'unit_price' => 85000.00, 'discount' => 0, 'tax' => 14450.00, 'total' => 99450.00],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-004',
                'customer_id' => $customers[3]->id,
                'quotation_id' => $quotations[3]->id,
                'status' => 'draft',
                'due_date' => now()->addDays(30),
                'amount_paid' => 0,
                'notes' => 'Draft invoice for Office 365 licenses',
                'items' => [
                    ['description' => 'Microsoft Office 365 License', 'quantity' => 50, 'unit_price' => 24000.00, 'discount' => 50000.00, 'tax' => 34000.00, 'total' => 1184000.00],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-005',
                'customer_id' => $customers[4]->id,
                'status' => 'cancelled',
                'due_date' => now()->subDays(20),
                'amount_paid' => 0,
                'notes' => 'Cancelled due to order rejection',
                'items' => [
                    ['description' => 'Custom Desktop PC Build',      'quantity' => 5, 'unit_price' => 235000.00, 'discount' => 0, 'tax' => 199750.00, 'total' => 1374750.00],
                ],
            ],
        ];

        $invoices = [];
        foreach ($invoicesData as $data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $subtotal = collect($itemsData)->sum('total');
            $taxAmount = collect($itemsData)->sum('tax');
            $discountAmount = collect($itemsData)->sum('discount');

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['discount_amount'] = $discountAmount;
            $data['tax_rate'] = 17.00;
            $data['total'] = $subtotal;
            $data['created_by'] = $this->adminUser->id;

            $invoice = Invoice::create($data);

            foreach ($itemsData as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemData);
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    private function seedPurchaseOrders(array $vendors, array $items): void
    {
        $posData = [
            [
                'po_number' => 'PO-2026-001',
                'vendor_id' => $vendors[0]->id,
                'status' => 'received',
                'order_date' => now()->subDays(30),
                'expected_delivery' => now()->subDays(15),
                'notes' => 'Restocking Dell laptops and servers',
                'items' => [
                    ['description' => 'Dell Latitude 5540 Laptop',    'quantity' => 15, 'unit_price' => 285000.00, 'total' => 4275000.00],
                    ['description' => 'Dell PowerEdge T350 Server',   'quantity' => 3, 'unit_price' => 450000.00, 'total' => 1350000.00],
                ],
            ],
            [
                'po_number' => 'PO-2026-002',
                'vendor_id' => $vendors[1]->id,
                'status' => 'confirmed',
                'order_date' => now()->subDays(5),
                'expected_delivery' => now()->addDays(10),
                'notes' => 'New desktop and monitor stock',
                'items' => [
                    ['description' => 'Custom Desktop PC Build',      'quantity' => 10, 'unit_price' => 195000.00, 'total' => 1950000.00],
                    ['description' => 'Dell UltraSharp U2723QE 27"', 'quantity' => 10, 'unit_price' => 125000.00, 'total' => 1250000.00],
                    ['description' => 'Cat6 Ethernet Cable (rolls)',  'quantity' => 20, 'unit_price' => 12000.00, 'total' => 240000.00],
                ],
            ],
            [
                'po_number' => 'PO-2026-003',
                'vendor_id' => $vendors[2]->id,
                'status' => 'draft',
                'order_date' => now(),
                'expected_delivery' => now()->addDays(21),
                'notes' => 'Network equipment for upcoming projects',
                'items' => [
                    ['description' => 'Ubiquiti UniFi Dream Machine', 'quantity' => 5, 'unit_price' => 85000.00, 'total' => 425000.00],
                    ['description' => 'Cisco Catalyst 1000 Switch',   'quantity' => 5, 'unit_price' => 65000.00, 'total' => 325000.00],
                ],
            ],
        ];

        foreach ($posData as $data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $subtotal = collect($itemsData)->sum('total');

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $subtotal * 0.17;
            $data['total'] = $subtotal + ($subtotal * 0.17);
            $data['created_by'] = $this->adminUser->id;

            $po = PurchaseOrder::create($data);

            foreach ($itemsData as $itemData) {
                $itemData['purchase_order_id'] = $po->id;
                PurchaseOrderItem::create($itemData);
            }
        }
    }

    private function seedCashEntries(array $banks): void
    {
        $entries = [
            [
                'type' => 'income',
                'category' => 'Client Payment',
                'description' => 'Payment received from Systems Limited - INV-2026-001',
                'amount' => 5243000.00,
                'reference_number' => 'INV-2026-001',
                'entry_date' => now()->subDays(10),
                'bank_id' => $banks[0]->id,
            ],
            [
                'type' => 'income',
                'category' => 'Client Payment',
                'description' => 'Partial payment from Techlogix - INV-2026-003',
                'amount' => 500000.00,
                'reference_number' => 'INV-2026-003',
                'entry_date' => now()->subDays(20),
                'bank_id' => $banks[0]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Purchase',
                'description' => 'Payment for PO-2026-001 - Dell Laptops',
                'amount' => 5625000.00,
                'reference_number' => 'PO-2026-001',
                'entry_date' => now()->subDays(25),
                'bank_id' => $banks[0]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Rent',
                'description' => 'Office rent - September 2026',
                'amount' => 350000.00,
                'reference_number' => 'RENT-SEP-2026',
                'entry_date' => now()->subDays(5),
                'bank_id' => $banks[1]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Salaries',
                'description' => 'Employee salaries - September 2026',
                'amount' => 1850000.00,
                'reference_number' => 'SAL-SEP-2026',
                'entry_date' => now()->subDays(2),
                'bank_id' => $banks[1]->id,
            ],
            [
                'type' => 'income',
                'category' => 'Service Revenue',
                'description' => 'IT support retainer - Avanza Solutions',
                'amount' => 250000.00,
                'reference_number' => 'SRV-AVZ-001',
                'entry_date' => now()->subDays(8),
                'bank_id' => $banks[2]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Utilities',
                'description' => 'Internet & phone bills',
                'amount' => 45000.00,
                'reference_number' => 'UTIL-SEP-2026',
                'entry_date' => now()->subDays(3),
                'bank_id' => $banks[2]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Marketing',
                'description' => 'Digital marketing - Google Ads',
                'amount' => 120000.00,
                'reference_number' => 'MKT-GADS-001',
                'entry_date' => now()->subDays(7),
                'bank_id' => $banks[0]->id,
            ],
            [
                'type' => 'income',
                'category' => 'Consultation',
                'description' => 'IT consultation - TRG Pakistan',
                'amount' => 180000.00,
                'reference_number' => 'CON-TRG-001',
                'entry_date' => now()->subDays(12),
                'bank_id' => $banks[2]->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Office Supplies',
                'description' => 'Stationery and office supplies purchase',
                'amount' => 35000.00,
                'reference_number' => 'OFF-SUP-001',
                'entry_date' => now()->subDays(6),
                'bank_id' => $banks[1]->id,
            ],
        ];

        foreach ($entries as $data) {
            $data['created_by'] = $this->adminUser->id;
            CashEntry::create($data);
        }
    }

    private function seedLedgerEntries(): void
    {
        $ledgerData = [
            [
                'account_name' => 'Accounts Receivable',
                'type' => 'debit',
                'amount' => 5243000.00,
                'description' => 'Invoice INV-2026-001 - Systems Limited',
                'reference_type' => 'App\\Models\\Invoice',
                'reference_id' => 1,
                'entry_date' => now()->subDays(10),
            ],
            [
                'account_name' => 'Revenue',
                'type' => 'credit',
                'amount' => 5243000.00,
                'description' => 'Revenue recognized - Systems Limited',
                'reference_type' => 'App\\Models\\Invoice',
                'reference_id' => 1,
                'entry_date' => now()->subDays(10),
            ],
            [
                'account_name' => 'Accounts Payable',
                'type' => 'credit',
                'amount' => 5625000.00,
                'description' => 'Purchase Order PO-2026-001 - Infinix Technologies',
                'reference_type' => 'App\\Models\\PurchaseOrder',
                'reference_id' => 1,
                'entry_date' => now()->subDays(25),
            ],
            [
                'account_name' => 'Inventory Asset',
                'type' => 'debit',
                'amount' => 5625000.00,
                'description' => 'Inventory received - Dell Laptops & Servers',
                'reference_type' => 'App\\Models\\PurchaseOrder',
                'reference_id' => 1,
                'entry_date' => now()->subDays(15),
            ],
            [
                'account_name' => 'Cash - HBL',
                'type' => 'debit',
                'amount' => 2500000.00,
                'description' => 'Opening bank balance - Habib Bank Limited',
                'reference_type' => null,
                'reference_id' => null,
                'entry_date' => now()->subDays(30),
            ],
        ];

        foreach ($ledgerData as $data) {
            $data['created_by'] = $this->adminUser->id;
            LedgerEntry::create($data);
        }
    }
}
