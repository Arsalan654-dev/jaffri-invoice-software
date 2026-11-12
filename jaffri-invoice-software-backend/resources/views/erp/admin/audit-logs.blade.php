@extends('erp.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Audit Logs</h2>
    </div>

    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.admin.audit-logs'), 'placeholder' => 'Search action, entity, user, IP...'])
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>IP Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    <td style="font-weight:600">{{ $log->user->name ?? 'System' }}</td>
                    <td>
                        <span class="status-badge
                            {{ $log->action === 'created' ? 'paid' : ($log->action === 'updated' ? 'partial' : 'overdue') }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td>{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                    <td style="font-family:monospace;font-size:0.8125rem;color:var(--text-muted)">{{ $log->ip_address }}</td>
                    <td>
                        <div class="action-buttons d-flex gap-2">
                            <a href="{{ route('erp.admin.audit-logs.pdf', $log) }}" class="btn-icon" title="Download PDF" data-icon="download"></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-message text-center">No audit logs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('erp.partials.pagination', ['paginator' => $logs])
</div>
@endsection
