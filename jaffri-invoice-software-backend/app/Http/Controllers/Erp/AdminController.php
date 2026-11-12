<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    // ─── User Management ──────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::with('roles')->with('userPermissions.permission');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('group');

        return view('erp.admin.users.index', compact('users', 'groupedPermissions'));
    }

    public function createUser()
    {
        $roles = Role::orderBy('name')->get();

        return view('erp.admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'exists:roles,slug'],
            'is_active' => ['boolean'],
            'must_change_password' => ['boolean'],
        ]);

        if (($validated['role'] ?? null) === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['must_change_password'] = $request->boolean('must_change_password');

        $user = User::create($validated);

        if ($request->filled('role')) {
            $role = Role::where('slug', $request->role)->first();
            $user->roles()->attach($role);
        }

        return redirect()->route('erp.admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $userRoles = $user->roles->pluck('slug')->toArray();

        return view('erp.admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'exists:roles,slug'],
            'is_active' => ['boolean'],
        ]);

        if (($validated['role'] ?? null) === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('role')) {
            $role = Role::where('slug', $request->role)->first();
            $user->roles()->sync([$role->id]);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('erp.admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('erp.admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    // ─── Role Management ──────────────────────────────────────────────

    public function roles(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $roles = $query->latest()->paginate(15)->withQueryString();

        return view('erp.admin.roles.index', compact('roles'));
    }

    public function createRole()
    {
        return view('erp.admin.roles.create');
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        Role::create($validated);

        return back()->with('success', 'Role created successfully.');
    }

    public function editRole(Role $role)
    {
        return view('erp.admin.roles.edit', compact('role'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'description' => ['nullable', 'string'],
        ]);

        $role->update($validated);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyRole(Role $role)
    {
        if ($role->slug === 'super_admin') {
            return back()->with('error', 'Cannot delete the Super Admin role.');
        }

        $role->users()->detach();
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    // ─── Permission Management ────────────────────────────────────────

    public function updateUserPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $permissionIds = collect($request->input('permissions', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $user->userPermissions()->delete();
        $user->userPermissions()->createMany(
            $permissionIds->map(fn ($id) => ['permission_id' => $id, 'type' => 'allow'])->all()
        );

        return back()->with('success', 'Permissions updated successfully for '.$user->name.'.');
    }

    // ─── Audit Logs ──────────────────────────────────────────────────

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('erp.admin.audit-logs', compact('logs', 'users'));
    }

    public function downloadUserPdf(User $user)
    {
        $user->load('roles');

        $fields = [
            'Name' => e($user->name),
            'Email' => e($user->email),
            'Phone' => e($user->phone ?? '—'),
            'Employee ID' => e($user->employee_id ?? '—'),
            'Department' => e($user->department ?? '—'),
            'Designation' => e($user->designation ?? '—'),
            'Roles' => e($user->roles->pluck('name')->join(', ') ?: '—'),
            'Status' => $user->is_active ? 'Active' : 'Inactive',
            'Created' => $user->created_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'User Profile',
            'reference' => $user->email,
            'status' => $user->is_active ? 'Active' : 'Inactive',
            'fields' => $fields,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('user-'.$user->id.'.pdf');
    }

    public function downloadAuditLogPdf(AuditLog $auditLog)
    {
        $auditLog->load('user');

        $fields = [
            'Date' => $auditLog->created_at->format('M d, Y H:i:s'),
            'User' => e($auditLog->user->name ?? 'System'),
            'Action' => e($auditLog->action),
            'Entity' => e($auditLog->entity_type).' #'.e((string) $auditLog->entity_id),
            'IP Address' => e($auditLog->ip_address ?? '—'),
            'Method' => e($auditLog->method ?? '—'),
            'Path' => e($auditLog->path ?? '—'),
        ];

        $notes = null;
        if (! empty($auditLog->new_values) && is_array($auditLog->new_values)) {
            $notes = "New Values:\n".json_encode($auditLog->new_values, JSON_PRETTY_PRINT);
        }
        if (! empty($auditLog->old_values) && is_array($auditLog->old_values)) {
            $notes = ($notes ? $notes."\n\n" : '')."Old Values:\n".json_encode($auditLog->old_values, JSON_PRETTY_PRINT);
        }

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Audit Log',
            'reference' => 'LOG-'.str_pad($auditLog->id, 6, '0', STR_PAD_LEFT),
            'status' => ucfirst($auditLog->action),
            'fields' => $fields,
            'notes' => $notes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('audit-log-'.$auditLog->id.'.pdf');
    }
}
