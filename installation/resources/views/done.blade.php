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
        For scheduled tasks and notifications, configure the scheduler based on your server:
    </p>
    
    <div style="margin-bottom: 15px;">
        <p style="font-weight: 600; font-size: 13px; margin-bottom: 5px;">Windows (Task Scheduler):</p>
        <pre>* * * * * php &lt;path to your project&gt;\artisan schedule:run >> /dev/null 2>&1</pre>
    </div>

    <div>
        <p style="font-weight: 600; font-size: 13px; margin-bottom: 5px;">Linux/cPanel/VPS:</p>
        <pre>* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</pre>
        <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">
            <strong>cPanel:</strong> Add to Cron Jobs section<br>
            <strong>VPS/Linux:</strong> Run <code>crontab -e</code> and paste the command above
        </p>
    </div>
</div>

<div style="display: flex; justify-content: center; gap: 12px; margin-top: 32px;">
    <form action="{{ route('installer.complete') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; border: none; cursor: pointer; font-size: 16px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
           Click to Finish it
        </button>
    </form>
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
