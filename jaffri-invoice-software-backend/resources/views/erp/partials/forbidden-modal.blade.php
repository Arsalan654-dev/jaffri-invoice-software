{{-- Global 403 forbidden modal popup. Show via showForbiddenModal() in app.js --}}
<div class="modal-overlay" id="forbiddenModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Forbidden</h3>
            <button class="close-btn" onclick="closeModal('forbiddenModal')">&times;</button>
        </div>
        <div style="text-align:center;padding:12px 0 4px">
            <div style="font-size:2.5rem;font-weight:800;color:var(--danger,#ef4444)">403</div>
            <p style="color:var(--text-muted,#64748b);font-size:1rem;margin:12px 0 0">You cannot allowed by admin to access this</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('forbiddenModal')">Close</button>
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
        </div>
    </div>
</div>
