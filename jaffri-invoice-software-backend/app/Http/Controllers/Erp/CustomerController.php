<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['user.roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('group');
        $roles = Role::orderBy('name')->get();

        return view('erp.customers.index', compact('customers', 'groupedPermissions', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('erp.customers.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'exists:roles,slug'],
        ]);

        if (($validated['role'] ?? null) === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Only a Super Admin can assign the Super Admin role.'])->withInput();
        }

        $roleSlug = $validated['role'] ?? null;
        unset($validated['role']);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        $password = $validated['password'] ?? null;
        unset($validated['password'], $validated['password_confirmation']);

        $customer = Customer::create($validated);

        if ($customer->email && $password) {
            $user = User::create([
                'name' => $customer->contact_person ?: $customer->company_name,
                'email' => $customer->email,
                'password' => $password,
                'is_active' => $customer->is_active,
            ]);

            if ($roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }

            $customer->update(['user_id' => $user->id]);
        } elseif ($roleSlug && $customer->user) {
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $customer->user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        return redirect()->route('erp.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'invoices' => fn ($q) => $q->latest()->limit(10),
        ]);

        $totalInvoiced = $customer->invoices()->sum('total');
        $totalPaid = $customer->invoices()->where('status', 'paid')->sum('total');
        $pendingAmount = $totalInvoiced - $totalPaid;

        return view('erp.customers.show', compact('customer', 'totalInvoiced', 'totalPaid', 'pendingAmount'));
    }

    public function edit(Customer $customer)
    {
        $customer->load('user.roles');
        $roles = Role::orderBy('name')->get();

        return view('erp.customers.edit', compact('customer', 'roles'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,'.$customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'exists:roles,slug'],
        ]);

        if (($validated['role'] ?? null) === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Only a Super Admin can assign the Super Admin role.'])->withInput();
        }

        $roleSlug = $validated['role'] ?? null;
        $roleTouched = $request->has('role');
        unset($validated['role']);

        $validated['is_active'] = $request->boolean('is_active');

        $password = $validated['password'] ?? null;
        unset($validated['password'], $validated['password_confirmation']);

        $customer->update($validated);
        $customer->refresh();

        if ($customer->user) {
            if ($password) {
                $customer->user->update(['password' => $password]);
            }

            if ($roleTouched) {
                if ($roleSlug) {
                    $role = Role::where('slug', $roleSlug)->first();
                    if ($role) {
                        $customer->user->roles()->sync([$role->id]);
                    }
                } else {
                    $customer->user->roles()->detach();
                }
            }
        } elseif ($customer->email && $password) {
            $user = User::create([
                'name' => $customer->contact_person ?: $customer->company_name,
                'email' => $customer->email,
                'password' => $password,
                'is_active' => $customer->is_active,
            ]);

            if ($roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }

            $customer->update(['user_id' => $user->id]);
        }

        return redirect()->route('erp.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing invoices. Remove related invoices first.');
        }

        if ($customer->user) {
            $customer->user->delete();
        }

        $customer->delete();

        return redirect()->route('erp.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function downloadPdf(Customer $customer)
    {
        $fields = [
            'Company Name' => e($customer->company_name),
            'Contact Person' => e($customer->contact_person ?? '—'),
            'Email' => e($customer->email ?? '—'),
            'Phone' => e($customer->phone ?? '—'),
            'Address' => e($customer->address ?? '—'),
            'City' => e($customer->city ?? '—'),
            'Country' => e($customer->country ?? '—'),
            'Tax Number' => e($customer->tax_number ?? '—'),
            'Payment Terms' => e($customer->payment_terms ?? '—'),
            'Balance' => 'Rs. '.number_format($customer->balance ?? 0, 2),
            'Status' => $customer->is_active ? 'Active' : 'Inactive',
            'Created' => $customer->created_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Customer Profile',
            'reference' => $customer->company_name,
            'status' => $customer->is_active ? 'Active' : 'Inactive',
            'fields' => $fields,
            'notes' => $customer->notes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('customer-'.$customer->id.'.pdf');
    }
}
