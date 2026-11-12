@extends('erp.layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Users</h2>
        @can('user.manage')
        <a href="{{ route('erp.admin.users.create') }}" class="btn btn-primary" data-icon="plus"><span>Create User</span></a>
        @endcan
    </div>

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.admin.users.index'), 'placeholder' => 'Search users...'])
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight:600">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge" style="background:#eef2ff;color:var(--primary);margin-right:4px">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td>
                        <span class="status-badge {{ $user->is_active ? 'paid' : 'overdue' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons d-flex gap-2">
                            <a href="{{ route('erp.admin.users.pdf', $user) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                            @can('user.manage')
                            <button class="btn-icon" title="Set Permissions" data-icon="key" style="background:#eef2ff;color:var(--primary);border-color:#c7d2fe" onclick="openModal('setPermissionModal{{ $user->id }}')"></button>
                            <a href="{{ route('erp.admin.users.edit', $user) }}" class="btn-icon" title="Edit" data-icon="edit"></a>
                            <form action="{{ route('erp.admin.users.delete', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon danger" title="Delete" data-icon="trash"></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-message text-center">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($users as $user)
        @include('erp.partials.set-permission-modal', [
            'modalId' => 'setPermissionModal'.$user->id,
            'targetUser' => $user,
            'targetLabel' => $user->name,
            'groupedPermissions' => $groupedPermissions,
        ])
    @endforeach

    @include('erp.partials.pagination', ['paginator' => $users])
</div>
@endsection
