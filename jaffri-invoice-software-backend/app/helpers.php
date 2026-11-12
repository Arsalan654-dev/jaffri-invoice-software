<?php

if (! function_exists('erp_navigation')) {
    function erp_navigation(): array
    {
        $user = auth()->user();

        $items = [
            ['href' => route('erp.dashboard'), 'icon' => 'dashboard', 'label' => 'Dashboard', 'active' => 'erp.dashboard'],
            ['href' => route('erp.customers.index'), 'icon' => 'customers', 'label' => 'Customers', 'active' => 'erp.customers.*', 'permission' => 'client.read'],
            ['href' => route('erp.vendors.index'), 'icon' => 'vendors', 'label' => 'Vendors', 'active' => 'erp.vendors.*', 'permission' => 'client.read'],
            ['href' => route('erp.invoices.index'), 'icon' => 'invoices', 'label' => 'Invoices', 'active' => 'erp.invoices.*', 'permission' => 'invoice.read'],
            ['href' => route('erp.quotations.index'), 'icon' => 'quotations', 'label' => 'Quotations', 'active' => 'erp.quotations.*', 'permission' => 'quotation.read'],
            ['href' => route('erp.purchase-orders.index'), 'icon' => 'purchaseOrders', 'label' => 'Purchase Orders', 'active' => 'erp.purchase-orders.*', 'permission' => 'purchase.read'],
            ['href' => route('erp.inventory.index'), 'icon' => 'inventory', 'label' => 'Inventory', 'active' => 'erp.inventory.*', 'permission' => 'inventory.read'],
            ['href' => route('erp.finance.cash-book'), 'icon' => 'cashbook', 'label' => 'Cash Book', 'active' => 'erp.finance.cash-book', 'permission' => 'ledger.read'],
            ['href' => route('erp.finance.banks'), 'icon' => 'bank', 'label' => 'Banks', 'active' => 'erp.finance.banks', 'permission' => 'ledger.read'],
            ['href' => route('erp.finance.ledger'), 'icon' => 'generalLedger', 'label' => 'General Ledger', 'active' => 'erp.finance.ledger', 'permission' => 'ledger.read'],
            ['href' => route('erp.reports.index'), 'icon' => 'reports', 'label' => 'Reports', 'active' => 'erp.reports.*', 'permission' => 'report.read'],
            ['href' => route('erp.admin.users.index'), 'icon' => 'customers', 'label' => 'Users', 'active' => 'erp.admin.users.*', 'permission' => 'user.manage'],
            ['href' => route('erp.admin.roles.index'), 'icon' => 'dashboard', 'label' => 'Roles', 'active' => 'erp.admin.roles.*', 'permission' => 'role.manage'],
            ['href' => route('erp.admin.audit-logs'), 'icon' => 'reports', 'label' => 'Audit Logs', 'active' => 'erp.admin.audit-logs', 'permission' => 'audit.read'],
        ];

        return array_values(array_filter($items, function (array $item) use ($user) {
            if (! $user) {
                return false;
            }

            if (isset($item['permission']) && ! $user->hasPermission($item['permission'])) {
                return false;
            }

            return true;
        }));
    }
}
