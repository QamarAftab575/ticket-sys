@extends('installer::layout')

@section('title', 'Email Configuration')

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
    <div class="step-item completed">
        <div class="step-circle">4</div>
        <div class="step-label">Admin</div>
    </div>
    <div class="step-item active">
        <div class="step-circle">5</div>
        <div class="step-label">Email</div>
    </div>
</div>

<div style="margin-bottom: 32px;">
    <h2 style="color: var(--text); margin-bottom: 8px; font-size: 22px; font-weight: 700;">Email Configuration</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Configure email notifications (optional - can be set up later).</p>
</div>

<form action="{{ route('installer.process', 'email') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="mail_mailer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Email Service Provider
        </label>
        <input
            type="text"
            id="mail_mailer"
            name="mail_mailer"
            value="{{ old('mail_mailer', $mail_mailer ?? 'smtp') }}"
            placeholder="smtp"
            
            aria-label="Email Service Provider"
        >
        <p class="help-text">e.g. <code>smtp</code></p>
    </div>

    <div id="smtp-settings" >
        <div class="form-group">
            <label for="mail_host">SMTP Host</label>
            <input 
                type="text" 
                id="mail_host" 
                name="mail_host" 
                value="{{ old('mail_host', $mail_host ?? '') }}"
                placeholder="smtp.gmail.com"
                aria-label="SMTP Host"
            >
            <p class="help-text">Example: <code>smtp.gmail.com</code> or <code>smtp.yourhost.com</code></p>
        </div>

        <div class="form-group">
            <label for="mail_port">SMTP Port</label>
            <input 
                type="number" 
                id="mail_port" 
                name="mail_port" 
                value="{{ old('mail_port', $mail_port ?? '587') }}"
                placeholder="587"
                aria-label="SMTP Port"
            >
            <p class="help-text">Common ports: <code>587</code> (TLS), <code>465</code> (SSL), <code>25</code> (plain)</p>
        </div>

        <div class="form-group">
            <label for="mail_username">SMTP Username</label>
            <input 
                type="text" 
                id="mail_username" 
                name="mail_username" 
                value="{{ old('mail_username', $mail_username ?? '') }}"
                placeholder="your-email@gmail.com"
                aria-label="SMTP Username"
            >
            <p class="help-text">Usually your email address</p>
        </div>

        <div class="form-group">
            <label for="mail_password">SMTP Password</label>
            <div style="position: relative;">
                <input 
                    type="password" 
                    id="mail_password" 
                    name="mail_password" 
                    value="{{ old('mail_password', $mail_password ?? '') }}"
                    placeholder="Your SMTP password"
                    aria-label="SMTP Password"
                    style="padding-right: 45px;"
                >
                <button 
                    type="button" 
                    class="password-toggle" 
                    onclick="togglePassword('mail_password', this)"
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
            <p class="help-text">For Gmail, use an <a href="https://support.google.com/accounts/answer/185833" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: underline;">App Password</a></p>
        </div>

        <div class="form-group">
            <label for="mail_encryption">Encryption</label>
            <select id="mail_encryption" name="mail_encryption" aria-label="Encryption">
                <option value="">None</option>
                <option value="tls" {{ old('mail_encryption', $mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                <option value="ssl" {{ old('mail_encryption', $mail_encryption ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
            </select>
            <p class="help-text">Use TLS for port 587 or SSL for port 465</p>
        </div>
    </div>

    <div class="form-group">
        <label for="mail_from_address">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
            </svg>
            From Email Address
        </label>
        <input 
            type="email" 
            id="mail_from_address" 
            name="mail_from_address" 
            value="{{ old('mail_from_address', $mail_from_address ?? '') }}"
            placeholder="noreply@example.com"
            aria-label="From Email Address"
        >
        <p class="help-text">Email address that notifications will be sent from</p>
    </div>

    <div class="alert alert-info">
        <span class="alert-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
        </span>
        <div>
            <strong>Optional Configuration</strong>
Email configuration is optional and can be completed later from the dashboard. If skipped, email notifications and member invitations will be disabled until it is configured.
        </div>
    </div>

    <div class="button-group">
        <a href="{{ route('installer.step', 'admin') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back
        </a>
        <button type="submit" class="btn btn-primary">
            Finalize Installation
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </button>
    </div>
</form>
@endsection
