@extends('installer::layout')

@section('title', 'Installation Complete')

@section('content')
<div class="success-icon">
    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success);">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
</div>

<div class="completion-title">
    <h2>Installation Complete!</h2>
    <p>Your {{ $appName }} workspace is ready to use.</p>
</div>

<div class="info-box success">
    <strong>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        Setup Complete
    </strong>
    <ul>
        <li>Database configured and migrations completed</li>
        <li>Administrator account created successfully</li>
        <li>Email notifications configured</li>
        <li>Application cache optimized</li>
        <li>System is ready for production use</li>
    </ul>
</div>

<div class="info-box warning">
    <strong>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Important Security Steps
    </strong>
    <ul>
        <li><strong>Delete the installer:</strong> Remove the <code>installation</code> folder from your server for security</li>
        <li><strong>Set up backups:</strong> Configure regular database backups in your hosting control panel</li>
        <li><strong>Enable SSL:</strong> Make sure your site uses HTTPS for secure connections</li>
        <li><strong>Review settings:</strong> Check your application settings in the admin panel</li>
    </ul>
</div>

<div class="info-box info">
    <strong>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        Cron Job Setup (Optional)
    </strong>
    <p style="font-size: 14px; color: var(--text-muted); margin-top: 8px; margin-bottom: 10px;">
        For scheduled tasks and notifications, add this cron job to your server:
    </p>
    <pre>* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</pre>
    <p style="font-size: 13px; color: var(--text-muted); margin-top: 10px;">
        <strong>cPanel:</strong> Add to Cron Jobs section<br>
        <strong>VPS/Linux:</strong> Run <code>crontab -e</code> and paste the command above
    </p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 32px;">
    <a href="{{ $appUrl }}" class="btn btn-primary" style="text-decoration: none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        Go to Application
    </a>
    <a href="{{ $appUrl }}/login" class="btn btn-secondary" style="text-decoration: none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
            <polyline points="10 17 15 12 10 7"></polyline>
            <line x1="15" y1="12" x2="3" y2="12"></line>
        </svg>
        Login Now
    </a>
</div>

<div class="footer-note">
    <p style="margin-bottom: 12px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle;">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <strong>Need help?</strong> Check the documentation or contact support.
    </p>
    <p style="font-size: 12px; color: #94A3B8;">
        The installer has been disabled and will not run again.
    </p>
</div>
@endsection
