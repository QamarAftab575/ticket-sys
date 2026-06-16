@extends('installer::layout')

@section('title', 'Welcome to Installation')

@section('content')
<div style="text-align: center; margin-bottom: 32px;">
    <h2 style="color: var(--text); margin-bottom: 12px; font-size: 24px; font-weight: 700;">Welcome! 👋</h2>
    <p style="color: var(--text-muted); font-size: 15px;">
        Let's get your {{ $appName }} workspace up and running in just a few minutes.
    </p>
</div>

@if(!collect($requirements)->every(fn($req) => $req['status']))
    <div class="alert alert-warning">
        <span class="alert-icon">⚠️</span>
        <div>
            <strong>System Requirements Not Met</strong>
            Some system requirements need attention. Please review them in the next step.
        </div>
    </div>
@endif

<div class="welcome-section">
    <h3>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
        </svg>
        What We'll Set Up
    </h3>
    <ol>
        <li><strong>System Requirements</strong> - Verify your server meets all requirements</li>
        <li><strong>Database Connection</strong> - Configure your database settings</li>
        <li><strong>Site Information</strong> - Set your application name and URL</li>
        <li><strong>Administrator Account</strong> - Create your admin user</li>
        <li><strong>Email Configuration</strong> - Set up notifications (optional)</li>
        <li><strong>Final Setup</strong> - Install and optimize everything</li>
    </ol>
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
        This process typically takes 2-3 minutes. Don't close this page until you see the completion message.
    </div>
</div>

<div style="margin-top: 32px;">
    <a href="{{ route('installer.step', 'requirements') }}" class="btn btn-primary btn-full">
        Start Installation
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </a>
</div>
@endsection
