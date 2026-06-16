@extends('installer::layout')

@section('title', 'Create Admin User')

@section('content')
<!-- Progress Steps -->
<div class="progress-steps">
    <div class="step-item completed">
        <div class="step-circle">1</div>
        <div class="step-label">Requirements</div>
    </div>
    <div class="step-item completed">
        <div class="step-circle">2</div>
        <div class="step-label">Database</div>
    </div>
    <div class="step-item completed">
        <div class="step-circle">3</div>
        <div class="step-label">Site Info</div>
    </div>
    <div class="step-item active">
        <div class="step-circle">4</div>
        <div class="step-label">Admin</div>
    </div>
    <div class="step-item">
        <div class="step-circle">5</div>
        <div class="step-label">Email</div>
    </div>
</div>

<div style="margin-bottom: 32px;">
    <h2 style="color: var(--text); margin-bottom: 8px; font-size: 22px; font-weight: 700;">Create Administrator</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Create your admin account to manage the workspace.</p>
</div>

<form action="{{ route('installer.process', 'admin') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="admin_name">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Full Name
        </label>
        <input 
            type="text" 
            id="admin_name" 
            name="admin_name" 
            value="{{ session('installer_data.admin.name', '') }}"
            placeholder="John Doe"
            required
            aria-label="Full Name"
        >
        <p class="help-text">Your full name as it will appear in the system</p>
    </div>

    <div class="form-group">
        <label for="admin_email">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Email Address
        </label>
        <input 
            type="email" 
            id="admin_email" 
            name="admin_email" 
            value="{{ session('installer_data.admin.email', '') }}"
            placeholder="admin@example.com"
            required
            aria-label="Email Address"
        >
        <p class="help-text">Use your work email address for login</p>
    </div>

    <div class="form-group">
        <label for="workspace_name">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            Workspace Name
        </label>
        <input 
            type="text" 
            id="workspace_name" 
            name="workspace_name" 
            value="{{ session('installer_data.admin.workspace_name', '') }}"
            placeholder="My Workspace"
            required
            aria-label="Workspace Name"
        >
        <p class="help-text">Your first workspace will be created with this name</p>
    </div>

    <div class="form-group">
        <label for="admin_password">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Password
        </label>
        <div style="position: relative;">
            <input 
                type="password" 
                id="admin_password" 
                name="admin_password" 
                placeholder="Minimum 8 characters"
                required
                minlength="8"
                aria-label="Password"
                style="padding-right: 45px;"
            >
            <button 
                type="button" 
                class="password-toggle" 
                onclick="togglePassword('admin_password', this)"
                aria-label="Toggle password visibility"
                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; color: var(--text-muted); transition: color 0.2s;"
            >
                <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            </button>
        </div>
        <p class="help-text">Use a strong, secure password with at least 8 characters</p>
    </div>

    <div class="form-group">
        <label for="admin_password_confirmation">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Confirm Password
        </label>
        <div style="position: relative;">
            <input 
                type="password" 
                id="admin_password_confirmation" 
                name="admin_password_confirmation" 
                placeholder="Re-enter your password"
                required
                minlength="8"
                aria-label="Confirm Password"
                style="padding-right: 45px;"
            >
            <button 
                type="button" 
                class="password-toggle" 
                onclick="togglePassword('admin_password_confirmation', this)"
                aria-label="Toggle password visibility"
                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; color: var(--text-muted); transition: color 0.2s;"
            >
                <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            </button>
        </div>
        <p class="help-text">Enter the same password again</p>
    </div>

    <div class="alert alert-warning">
        <span class="alert-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </span>
        <div>
            <strong>Keep Your Credentials Safe</strong>
            You'll use these credentials to log in after installation. Store them securely.
        </div>
    </div>

    <div class="button-group">
        <a href="{{ route('installer.step', 'site') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back
        </a>
        <button type="submit" class="btn btn-primary">
            Continue
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</form>
@endsection
