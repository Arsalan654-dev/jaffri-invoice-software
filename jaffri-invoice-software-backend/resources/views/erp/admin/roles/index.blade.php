@extends('erp.layouts.app')

@section('title', 'Manage Roles')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Roles</h2>
    </div>

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.admin.roles.index'), 'placeholder' => 'Search roles...'])
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-main);margin-bottom:16px">All Roles</h3>
                @forelse($roles as $role)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:12px">
                    <div style="flex:1">
                        <h4 style="font-weight:600;color:var(--text-main)">{{ $role->name }}</h4>
                        <p style="font-size:0.8125rem;color:var(--text-muted)">Slug: {{ $role->slug }}</p>
                        @if($role->description)
                            <p style="font-size:0.8125rem;color:var(--text-muted);margin-top:4px">{{ $role->description }}</p>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <a href="{{ route('erp.admin.roles.edit', $role) }}" class="btn-icon" title="Edit Role" data-icon="edit"></a>
                        <form action="{{ route('erp.admin.roles.delete', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon danger" title="Delete Role" data-icon="trash"></button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="empty-message text-center" style="padding:32px 0">No roles found</p>
                @endforelse
                @include('erp.partials.pagination', ['paginator' => $roles])
            </div>
        </div>

        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
                <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-main);margin-bottom:16px">Create Role</h3>
                <form action="{{ route('erp.admin.roles.store') }}" method="POST">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:20px">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required @error('name') style="border-color:var(--danger)" @enderror>
                            @error('name')
                                <span class="error-message" style="text-align:left">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="What can this role do?">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error-message" style="text-align:left">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:20px;justify-content:center">Create Role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
