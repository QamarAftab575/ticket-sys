@extends('installer::layout')

@section('title', 'System Requirements')

@section('content')
<!-- Progress Steps -->
<div class="progress-steps">
    <div class="step-item active">
        <div class="step-circle">1</div>
        <div class="step-label">Requirements</div>
    </div>
    <div class="step-item">
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
    <h2 style="color: var(--text); margin-bottom: 8px; font-size: 22px; font-weight: 700;">System Requirements</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Checking your server configuration...</p>
</div>

<ul class="requirements-list">
    @foreach($requirements as $key => $requirement)
        <li class="{{ $requirement['status'] ? 'met' : 'not-met' }}">
            <span class="requirement-icon">
                @if($requirement['status'])
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                @else
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                @endif
            </span>
            <div class="requirement-content">
                <strong>{{ $requirement['name'] }}</strong>
                <div class="requirement-value">{{ $requirement['value'] }}</div>
            </div>
        </li>
    @endforeach
</ul>

@if(collect($requirements)->every(fn($req) => $req['status']))
    <div class="alert alert-success">
        <span class="alert-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </span>
        <div>
            <strong>All Requirements Met!</strong>
            Your server configuration is perfect. You can proceed with the installation.
        </div>
    </div>

    <form action="{{ route('installer.process', 'requirements') }}" method="POST">
        @csrf
        <div class="button-group">
            <a href="{{ route('installer.index') }}" class="btn btn-secondary">
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
@else
    <div class="alert alert-error">
        <span class="alert-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </span>
        <div>
            <strong>Requirements Not Met</strong>
            Please contact your hosting provider to fix these issues:
            <ul>
                @foreach($requirements as $requirement)
                    @if(!$requirement['status'])
                        <li>{{ $requirement['name'] }}: {{ $requirement['value'] }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div style="margin-top: 32px;">
        <a href="{{ route('installer.index') }}" class="btn btn-secondary btn-full">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Start
        </a>
    </div>
@endif
@endsection
