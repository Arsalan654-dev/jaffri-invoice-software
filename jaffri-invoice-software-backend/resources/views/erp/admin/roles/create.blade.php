@extends('erp.layouts.app')

@section('title', 'Create Role')

@section('content')
<div style="max-width:640px;margin:0 auto">
    <div class="page-header">
        <h2>Create Role</h2>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
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

            <div class="modal-actions" style="margin-top:24px">
                <a href="{{ route('erp.admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</div>
@endsection
