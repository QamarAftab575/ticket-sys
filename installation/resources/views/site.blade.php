@extends('installer::layout')

@section('title', 'Site Configuration')

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
    <div class="step-item active">
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
    <h2 style="color: var(--text); margin-bottom: 8px; font-size: 22px; font-weight: 700;">Site Configuration</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Configure your application settings.</p>
</div>

<form action="{{ route('installer.process', 'site') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="app_name">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Application Name
        </label>
        <input 
            type="text" 
            id="app_name" 
            name="app_name" 
            value="{{ old('app_name', $app_name ?? config('app.name')) }}"
            placeholder="My Project Management"
            required
            aria-label="Application Name"
        >
        <p class="help-text">The name that will appear throughout your application</p>
    </div>

    <div class="form-group">
        <label for="app_url">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            Application URL
        </label>
        <input 
            type="url" 
            id="app_url" 
            name="app_url" 
            value="{{ old('app_url', $app_url ?? config('app.url')) }}"
            placeholder="https://example.com"
            required
            aria-label="Application URL"
        >
        <p class="help-text">Your application's public URL (include http:// or https://)</p>
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
            Make sure the URL matches your domain exactly to avoid login and authentication issues.
        </div>
    </div>

    <div class="button-group">
        <a href="{{ route('installer.step', 'database') }}" class="btn btn-secondary">
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
