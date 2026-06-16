@extends('installer::layout')

@section('title', 'Database Configuration')

@section('content')
<!-- Progress Steps -->
<div class="progress-steps">
    <div class="step-item completed">
        <div class="step-circle">1</div>
        <div class="step-label">Requirements</div>
    </div>
    <div class="step-item active">
        <div class="step-circle">2</div>
        <div class="step-label">Database</div>
    </div>
    <div class="step-item">
        <div class="step-circle">3</div>
        <div class="step-label">Site Info</div>
    </div>
    <div class="step-item">
        <div class="step-circle">4</div>
        <div class="step-label">Admin</div>
    </div>
    <div class="step-item">
        <div class="step-circle">5</div>
        <div class="step-label">Email</div>
    </div>
</div>

<div style="margin-bottom: 32px;">
    <h2 style="color: var(--text); margin-bottom: 8px; font-size: 22px; font-weight: 700;">Database Configuration</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Enter your database connection details.</p>
</div>

<form action="{{ route('installer.process', 'database') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="db_host">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                <line x1="6" y1="18" x2="6.01" y2="18"></line>
            </svg>
            Database Host
        </label>
        <input 
            type="text" 
            id="db_host" 
            name="db_host" 
            value="{{ old('db_host', $db_host ?? '127.0.0.1') }}"
            placeholder="127.0.0.1"
            required
            aria-label="Database Host"
        >
        <p class="help-text">Usually <code>localhost</code> or <code>127.0.0.1</code></p>
    </div>

    <div class="form-group">
        <label for="db_port">Database Port</label>
        <input 
            type="number" 
            id="db_port" 
            name="db_port" 
            value="{{ old('db_port', $db_port ?? '3306') }}"
            placeholder="3306"
            required
            aria-label="Database Port"
        >
        <p class="help-text">Default MySQL port is <code>3306</code></p>
    </div>

    <div class="form-group">
        <label for="db_database">Database Name</label>
        <input 
            type="text" 
            id="db_database" 
            name="db_database" 
            value="{{ old('db_database', $db_database ?? '') }}"
            placeholder="e.g., myapp_production"
            required
            aria-label="Database Name"
        >
        <p class="help-text">The database must already exist on your server</p>
    </div>

    <div class="form-group">
        <label for="db_username">Database Username</label>
        <input 
            type="text" 
            id="db_username" 
            name="db_username" 
            value="{{ old('db_username', $db_username ?? 'root') }}"
            placeholder="root"
            required
            aria-label="Database Username"
        >
    </div>

    <div class="form-group">
        <label for="db_password">Database Password</label>
        <div style="position: relative;">
            <input 
                type="password" 
                id="db_password" 
                name="db_password" 
                value="{{ old('db_password', $db_password ?? '') }}"
                placeholder="Leave blank if no password"
                aria-label="Database Password"
                style="padding-right: 45px;"
            >
            <button 
                type="button" 
                class="password-toggle" 
                onclick="togglePassword('db_password', this)"
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
        <p class="help-text">Enter your database password (if required)</p>
    </div>

    <div class="alert alert-info">
        <span class="alert-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </span>
        <div>
            <strong>Need help?</strong> Check your hosting control panel (cPanel, Plesk, etc.) for database credentials.
        </div>
    </div>

    <div class="button-group">
        <a href="{{ route('installer.step', 'requirements') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back
        </a>
        <button type="submit" class="btn btn-primary">
            Test & Continue
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</form>
@endsection
