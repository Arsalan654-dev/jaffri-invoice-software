<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ErpFlowTest extends TestCase
{
    public function test_all_pages_load(): void
    {
        $this->app['config']->set('database.default', 'mysql');
        $this->app['config']->set('database.connections.mysql.database', 'yours_tech_hub');
        $this->app['config']->set('database.connections.mysql.username', 'root');
        $this->app['config']->set('database.connections.mysql.password', '');

        $admin = User::where('email', 'admin@yourstechhub.com')->first();
        $this->assertNotNull($admin, 'Admin user not found');

        $pages = [
            '/dashboard',
            '/customers',
            '/vendors',
            '/invoices',
            '/invoices/create',
            '/quotations',
            '/quotations/create',
            '/purchase-orders',
            '/purchase-orders/create',
            '/inventory',
            '/inventory/create',
            '/finance/cash-book',
            '/finance/ledger',
            '/finance/banks',
            '/reports',
            '/reports/sales',
            '/reports/purchases',
            '/reports/profit-loss',
            '/admin/users',
            '/admin/roles',
            '/admin/audit-logs',
            '/profile',
            '/customers/1',
            '/vendors/1',
            '/invoices/1',
            '/quotations/1',
            '/purchase-orders/1',
            '/inventory/1',
            '/reports/pnl/pdf',
            '/reports/balance-sheet/pdf',
            '/reports/aging/pdf',
            '/reports/inventory-valuation/pdf',
        ];

        $failures = [];
        foreach ($pages as $page) {
            try {
                $response = $this->actingAs($admin)->get($page);
                $status = $response->status();
                if ($status !== 200) {
                    $failures[] = "$page -> $status";
                }
            } catch (\Throwable $e) {
                $failures[] = "$page -> ERROR: ".$e->getMessage();
            }
        }

        if (! empty($failures)) {
            $this->fail("Failed pages:\n".implode("\n", $failures));
        }

        $this->assertTrue(true, 'All pages loaded successfully');
    }
}
