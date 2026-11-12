@php
    $modalId = $modalId ?? 'setPermissionModal';
    $targetUser = $targetUser ?? null;
    $targetLabel = $targetLabel ?? ($targetUser->name ?? '');
    $currentPermissionIds = $targetUser
        ? $targetUser->userPermissions->pluck('permission_id')->all()
        : [];
    $formAction = $targetUser ? route('erp.admin.users.permissions', $targetUser) : '#';
@endphp

<div class="modal-overlay" id="{{ $modalId }}">
    <div class="modal large">
        <div class="modal-header">
            <h3>Set Permission - {{ $targetLabel }}</h3>
            <button class="close-btn" onclick="closeModal('{{ $modalId }}')">&times;</button>
        </div>
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @method('PUT')
            @foreach(($groupedPermissions ?? collect()) as $module => $modulePermissions)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px">
                <h4 style="font-size:1rem;font-weight:700;color:var(--text-main);margin-bottom:12px">{{ $module }}</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px">
                    @foreach($modulePermissions as $permission)
                    <label style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;font-size:0.875rem;color:var(--text-main)">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            {{ in_array($permission->id, $currentPermissionIds) ? 'checked' : '' }}>
                        <span>{{ $permission->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('{{ $modalId }}')">Cancel</button>
                <button type="submit" class="btn btn-primary">Set Permission - {{ $targetLabel }}</button>
            </div>
        </form>
    </div>
</div>
