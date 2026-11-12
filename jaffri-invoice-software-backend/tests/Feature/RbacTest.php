<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleSlug): User
    {
        $role = Role::factory()->create(['slug' => $roleSlug, 'name' => ucfirst(str_replace('_', ' ', $roleSlug))]);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role->id);

        return $user;
    }

    private function giveUserPermission(User $user, string $slug, string $type = 'allow'): Permission
    {
        $permission = Permission::factory()->create(['slug' => $slug, 'name' => ucfirst($slug)]);
        $user->userPermissions()->create([
            'permission_id' => $permission->id,
            'type' => $type,
        ]);

        return $permission;
    }

    private function attachPermissionToRole(Role $role, string $slug): Permission
    {
        $permission = Permission::factory()->create(['slug' => $slug, 'name' => ucfirst($slug)]);
        $role->permissions()->attach($permission->id);

        return $permission;
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = $this->createUserWithRole('super_admin');

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasPermission('client.create'));
        $this->assertTrue($user->hasPermission('user.manage'));
        $this->assertTrue($user->hasPermission('nonexistent.permission'));
    }

    public function test_role_does_not_grant_permission(): void
    {
        $role = Role::factory()->create(['slug' => 'sales']);
        $this->attachPermissionToRole($role, 'client.create');

        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role->id);

        $this->assertFalse($user->hasPermission('client.create'));
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_allow_grants_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.create');

        $this->assertTrue($user->hasPermission('client.create'));
        $this->assertFalse($user->hasPermission('client.delete'));
    }

    public function test_user_deny_blocks_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.create', 'deny');

        $this->assertFalse($user->hasPermission('client.create'));
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('erp.login'));
        $this->get('/admin/users')->assertRedirect(route('erp.login'));
    }

    public function test_user_without_permission_gets_403(): void
    {
        $user = $this->createUserWithRole('staff');

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
        $this->actingAs($user)->get('/customers')->assertForbidden();
    }

    public function test_forbidden_response_contains_admin_message(): void
    {
        $user = $this->createUserWithRole('staff');

        $this->actingAs($user)
            ->get('/customers')
            ->assertForbidden()
            ->assertSee('You cannot allowed by admin to access this');
    }

    public function test_user_with_permission_can_access_route(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.read');

        $this->actingAs($user)->get('/customers')->assertOk();
    }

    public function test_user_with_user_manage_but_not_role_manage_cannot_access_roles(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'user.manage');

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
    }

    public function test_super_admin_can_access_admin_panel(): void
    {
        $user = $this->createUserWithRole('super_admin');

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin/roles')->assertOk();
        $this->actingAs($user)->get('/admin/audit-logs')->assertOk();
    }

    public function test_gate_checks_direct_permission_for_blade_can(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.update');

        $this->assertTrue($user->can('client.update'));
        $this->assertFalse($user->can('user.manage'));
    }

    public function test_store_role_requires_role_manage_permission(): void
    {
        $user = $this->createUserWithRole('sales');

        $this->actingAs($user)->post('/admin/roles', ['name' => 'New Role'])->assertForbidden();

        $this->assertDatabaseMissing('roles', ['name' => 'New Role']);
    }

    public function test_store_role_succeeds_with_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'role.manage');

        $this->actingAs($user)->post('/admin/roles', [
            'name' => 'Warehouse Manager',
            'description' => 'Manages inventory',
        ])->assertRedirect();

        $this->assertDatabaseHas('roles', [
            'name' => 'Warehouse Manager',
            'slug' => 'warehouse-manager',
        ]);
    }

    public function test_only_super_admin_can_assign_super_admin_role(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($admin, 'user.manage');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Escalated',
            'email' => 'escalated@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'escalated@example.com']);

        $super = $this->createUserWithRole('super_admin');

        $this->actingAs($super)->post('/admin/users', [
            'name' => 'Legit Super',
            'email' => 'legit-super@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_admin',
        ])->assertRedirect(route('erp.admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'legit-super@example.com']);
    }

    public function test_permission_middleware_blocks_unauthorized_write(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.read');

        $this->actingAs($user)->post('/customers', [
            'company_name' => 'Blocked Co',
            'email' => 'blocked@example.com',
        ])->assertForbidden();

        $this->assertDatabaseMissing('customers', ['company_name' => 'Blocked Co']);
    }

    public function test_update_user_permissions_syncs(): void
    {
        $target = User::factory()->create(['is_active' => true]);
        $p1 = Permission::factory()->create();
        $p2 = Permission::factory()->create();

        $admin = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($admin, 'user.manage');

        $this->actingAs($admin)->put("/admin/users/{$target->id}/permissions", [
            'permissions' => [$p1->id, $p2->id],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $target->id,
            'permission_id' => $p1->id,
            'type' => 'allow',
        ]);
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $target->id,
            'permission_id' => $p2->id,
            'type' => 'allow',
        ]);

        $this->actingAs($admin)->put("/admin/users/{$target->id}/permissions", [
            'permissions' => [$p1->id],
        ])->assertSessionHas('success');

        $this->assertDatabaseMissing('user_permissions', [
            'user_id' => $target->id,
            'permission_id' => $p2->id,
        ]);
    }

    public function test_update_role_does_not_change_slug(): void
    {
        $role = Role::factory()->create(['slug' => 'sales', 'name' => 'Sales']);

        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'role.manage');

        $this->actingAs($user)->put("/admin/roles/{$role->id}", [
            'name' => 'Sales Executive',
            'description' => 'Updated',
        ])->assertSessionHas('success');

        $role->refresh();
        $this->assertSame('sales', $role->slug);
        $this->assertSame('Sales Executive', $role->name);
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $role = Role::factory()->create(['slug' => 'super_admin', 'name' => 'Super Admin']);

        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'role.manage');

        $this->actingAs($user)->delete("/admin/roles/{$role->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_deleting_role_leaves_user_intact(): void
    {
        $role = Role::factory()->create(['slug' => 'finance', 'name' => 'Finance']);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role->id);

        $admin = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($admin, 'role.manage');

        $this->actingAs($admin)->delete("/admin/roles/{$role->id}")->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('role_users', ['user_id' => $user->id]);
    }

    public function test_customer_store_with_password_creates_linked_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.create');

        $this->actingAs($user)->post('/customers', [
            'company_name' => 'Portal Co',
            'email' => 'portal@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('erp.customers.index'));

        $customer = \App\Models\Customer::where('company_name', 'Portal Co')->firstOrFail();
        $this->assertNotNull($customer->user_id);

        $linked = User::where('email', 'portal@example.com')->firstOrFail();
        $this->assertSame($customer->user_id, $linked->id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret123', $linked->password));
    }

    public function test_customer_store_without_password_does_not_create_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->giveUserPermission($user, 'client.create');

        $this->actingAs($user)->post('/customers', [
            'company_name' => 'No Login Co',
            'email' => 'nologin@example.com',
        ])->assertRedirect(route('erp.customers.index'));

        $customer = \App\Models\Customer::where('company_name', 'No Login Co')->firstOrFail();
        $this->assertNull($customer->user_id);
        $this->assertDatabaseMissing('users', ['email' => 'nologin@example.com']);
    }
}
