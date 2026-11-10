<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('DELETE FROM role_permissions');
        DB::statement('DELETE FROM role_users');
        DB::statement('DELETE FROM user_permissions');
        Permission::query()->forceDelete();
        Role::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $permissions = [
            'client.create' => ['name' => 'Create Client',            'group' => 'CRM'],
            'client.read' => ['name' => 'View Clients',            'group' => 'CRM'],
            'client.update' => ['name' => 'Update Client',           'group' => 'CRM'],
            'client.delete' => ['name' => 'Delete Client',           'group' => 'CRM'],
            'client.export' => ['name' => 'Export Clients',          'group' => 'CRM'],

            'quotation.create' => ['name' => 'Create Quotation',        'group' => 'Quotation'],
            'quotation.read' => ['name' => 'View Quotations',         'group' => 'Quotation'],
            'quotation.update' => ['name' => 'Update Quotation',        'group' => 'Quotation'],
            'quotation.send' => ['name' => 'Send Quotation',          'group' => 'Quotation'],
            'quotation.convert' => ['name' => 'Convert Quotation',       'group' => 'Quotation'],
            'quotation.delete' => ['name' => 'Delete Quotation',        'group' => 'Quotation'],

            'invoice.create' => ['name' => 'Create Invoice',          'group' => 'Invoice'],
            'invoice.read' => ['name' => 'View Invoices',           'group' => 'Invoice'],
            'invoice.update' => ['name' => 'Update Invoice',          'group' => 'Invoice'],
            'invoice.lock' => ['name' => 'Lock Invoice',            'group' => 'Invoice'],
            'invoice.send' => ['name' => 'Send Invoice',            'group' => 'Invoice'],
            'invoice.cancel' => ['name' => 'Cancel Invoice',          'group' => 'Invoice'],

            'payment.create' => ['name' => 'Create Payment',          'group' => 'Finance'],
            'payment.read' => ['name' => 'View Payments',           'group' => 'Finance'],
            'expense.create' => ['name' => 'Create Expense',          'group' => 'Finance'],
            'expense.read' => ['name' => 'View Expenses',           'group' => 'Finance'],
            'ledger.read' => ['name' => 'View Ledger',             'group' => 'Finance'],
            'ledger.post' => ['name' => 'Post Ledger Entries',     'group' => 'Finance'],
            'report.read' => ['name' => 'View Reports',            'group' => 'Finance'],

            'inventory.create' => ['name' => 'Create Inventory Item',   'group' => 'Inventory'],
            'inventory.read' => ['name' => 'View Inventory',          'group' => 'Inventory'],
            'inventory.update' => ['name' => 'Update Inventory Item',   'group' => 'Inventory'],
            'inventory.delete' => ['name' => 'Delete Inventory Item',   'group' => 'Inventory'],

            'purchase.create' => ['name' => 'Create Purchase Order',   'group' => 'Purchase'],
            'purchase.read' => ['name' => 'View Purchase Orders',    'group' => 'Purchase'],
            'purchase.update' => ['name' => 'Update Purchase Order',   'group' => 'Purchase'],
            'purchase.delete' => ['name' => 'Delete Purchase Order',   'group' => 'Purchase'],

            'user.manage' => ['name' => 'Manage Users',            'group' => 'Admin'],
            'role.manage' => ['name' => 'Manage Roles',            'group' => 'Admin'],
            'audit.read' => ['name' => 'View Audit Logs',         'group' => 'Admin'],
            'settings.manage' => ['name' => 'Manage Settings',         'group' => 'Admin'],
        ];

        $permissionModels = [];
        foreach ($permissions as $slug => $data) {
            $permissionModels[$slug] = Permission::create([
                'name' => $data['name'],
                'slug' => $slug,
                'group' => $data['group'],
            ]);
        }

        $allSlugs = array_keys($permissionModels);

        $roles = [
            'super_admin' => $allSlugs,
            'admin' => [
                'user.manage', 'role.manage', 'audit.read',
                'client.create', 'client.read', 'client.update', 'client.delete', 'client.export',
                'quotation.create', 'quotation.read', 'quotation.update', 'quotation.send', 'quotation.convert', 'quotation.delete',
                'invoice.create', 'invoice.read', 'invoice.update', 'invoice.lock', 'invoice.send', 'invoice.cancel',
                'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete',
                'purchase.create', 'purchase.read', 'purchase.update', 'purchase.delete',
            ],
            'finance' => [
                'invoice.create', 'invoice.read', 'invoice.update', 'invoice.lock', 'invoice.send', 'invoice.cancel',
                'payment.create', 'payment.read',
                'expense.create', 'expense.read',
                'ledger.read', 'ledger.post',
                'report.read',
                'quotation.read',
            ],
            'sales' => [
                'client.create', 'client.read', 'client.update', 'client.delete', 'client.export',
                'quotation.create', 'quotation.read', 'quotation.update', 'quotation.send', 'quotation.convert', 'quotation.delete',
                'invoice.read',
                'inventory.read',
            ],
            'staff' => [
                'client.read',
                'invoice.read',
                'quotation.read',
                'inventory.read',
            ],
        ];

        $roleDescriptions = [
            'super_admin' => 'Full system access with all permissions',
            'admin' => 'Administrative access to most modules',
            'finance' => 'Access to financial modules and reports',
            'sales' => 'Access to CRM, quotations, and limited invoice/inventory',
            'staff' => 'Read-only access to basic modules',
        ];

        foreach ($roles as $slug => $assignedSlugs) {
            $role = Role::create([
                'name' => ucwords(str_replace('_', ' ', $slug)),
                'slug' => $slug,
                'description' => $roleDescriptions[$slug],
            ]);

            $rolePermissionIds = array_map(
                fn ($slug) => $permissionModels[$slug]->id,
                $assignedSlugs
            );

            $role->permissions()->attach($rolePermissionIds);
        }
    }
}
