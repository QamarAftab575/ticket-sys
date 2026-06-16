@extends('installer::layout')

@section('title', 'Installation Error')

@section('content')
<div style="text-align: center; margin-bottom: 32px;">
    <div style="font-size: 64px; margin-bottom: 16px; color: var(--error);">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--error);">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
    </div>
    <h2 style="color: var(--error); margin-bottom: 12px; font-size: 24px; font-weight: 700;">Installation Error</h2>
    <p style="color: var(--text-muted); font-size: 15px;">
        Something went wrong during the installation process.
    </p>
</div>

<div class="alert alert-error">
    <span class="alert-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
    </span>
    <div>
        <strong>Error Details</strong>
        <p style="margin-top: 8px; font-family: 'Courier New', monospace; font-size: 13px;">
            {{ $error ?? 'An unknown error occurred' }}
        </p>
    </div>
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
        <strong>Troubleshooting Tips</strong>
        <ul style="margin-top: 8px;">
            <li>Check your database credentials are correct</li>
            <li>Ensure the database exists and is accessible</li>
            <li>Verify your server meets all requirements</li>
            <li>Check file permissions are set correctly</li>
            <li>Review your server error logs for more details</li>
        </ul>
    </div>
</div>

<div style="margin-top: 32px;">
    <a href="{{ route('installer.index') }}" class="btn btn-primary btn-full">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 2v6h-6"></path>
            <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
            <path d="M3 22v-6h6"></path>
            <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
        </svg>
        Try Again
    </a>
</div>

<div class="footer-note">
    <p style="font-size: 13px; color: var(--text-muted);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; vertical-align: middle;">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <strong>Need help?</strong> Contact your hosting provider or check the documentation for assistance.
    </p>
</div>
@endsection
