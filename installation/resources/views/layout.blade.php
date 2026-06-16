<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Installation Wizard') - {{ config('app.name') }}</title>
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/images/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/images/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/favicon/apple-icon-76x76.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/android-icon-96x96.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563EB;
            --secondary: #3B82F6;
            --cta: #F97316;
            --background: #F8FAFC;
            --text: #1E293B;
            --text-muted: #475569;
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
            --border: #E2E8F0;
            --card-bg: #FFFFFF;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F1F5F9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .installer-container {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 680px;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .installer-header {
            background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
            color: white;
            padding: 48px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .installer-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .installer-header-content {
            position: relative;
            z-index: 1;
        }

        .installer-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .installer-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .installer-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 400;
        }

        .installer-content {
            padding: 40px 32px;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 0 8px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            background: var(--card-bg);
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text-muted);
            transition: all 0.3s ease;
            position: relative;
        }

        .step-item.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transform: scale(1.1);
        }

        .step-item.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step-item.completed .step-circle::after {
            content: '✓';
            position: absolute;
            font-size: 16px;
        }

        .step-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            display: none;
        }

        @media (min-width: 640px) {
            .step-label {
                display: block;
            }
        }

        .step-item.active .step-label {
            color: var(--primary);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 24px;
            animation: fadeIn 0.4s ease-out backwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.05s; }
        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.15s; }
        .form-group:nth-child(4) { animation-delay: 0.2s; }
        .form-group:nth-child(5) { animation-delay: 0.25s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
            background: var(--card-bg);
            color: var(--text);
        }

        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: var(--secondary);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: var(--error);
        }

        .error-message {
            color: var(--error);
            font-size: 13px;
            margin-top: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .error-message::before {
            content: '⚠';
            font-size: 14px;
        }

        .help-text {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.4;
        }

        .help-text code {
            background: var(--background);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            flex: 1;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--cta) 0%, #ea580c 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--background);
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: #E2E8F0;
            border-color: #CBD5E1;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-full {
            width: 100%;
        }

        /* Alerts */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
            border: 1px solid;
            display: flex;
            gap: 12px;
            animation: fadeIn 0.4s ease-out;
        }

        .alert-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .alert-info {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: #BFDBFE;
        }

        .alert-success {
            background: #ECFDF5;
            color: #047857;
            border-color: #A7F3D0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #B91C1C;
            border-color: #FECACA;
        }

        .alert-warning {
            background: #FFFBEB;
            color: #B45309;
            border-color: #FDE68A;
        }

        .alert strong {
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .alert ul {
            margin-top: 8px;
            margin-left: 20px;
        }

        .alert a {
            color: inherit;
            text-decoration: underline;
        }

        /* Requirements List */
        .requirements-list {
            list-style: none;
            margin-bottom: 20px;
        }

        .requirements-list li {
            padding: 14px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid;
            transition: all 0.2s ease;
            animation: fadeIn 0.3s ease-out backwards;
        }

        .requirements-list li:nth-child(1) { animation-delay: 0.05s; }
        .requirements-list li:nth-child(2) { animation-delay: 0.1s; }
        .requirements-list li:nth-child(3) { animation-delay: 0.15s; }
        .requirements-list li:nth-child(4) { animation-delay: 0.2s; }
        .requirements-list li:nth-child(5) { animation-delay: 0.25s; }
        .requirements-list li:nth-child(6) { animation-delay: 0.3s; }

        .requirements-list li.met {
            background: #ECFDF5;
            color: #047857;
            border-color: #A7F3D0;
        }

        .requirements-list li.not-met {
            background: #FEF2F2;
            color: #B91C1C;
            border-color: #FECACA;
        }

        .requirement-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .requirement-content {
            flex: 1;
        }

        .requirement-content strong {
            display: block;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .requirement-value {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Loading Spinner */
        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Welcome Page Specific */
        .welcome-section {
            background: var(--background);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .welcome-section h3 {
            color: var(--text);
            margin-bottom: 16px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .welcome-section ol {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.8;
            margin-left: 20px;
        }

        .welcome-section ol li {
            margin-bottom: 8px;
        }

        .welcome-section ol li strong {
            color: var(--text);
            font-weight: 600;
        }

        /* Done Page */
        .success-icon {
            font-size: 80px;
            text-align: center;
            margin-bottom: 24px;
            animation: bounce 1s ease-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            25% { transform: translateY(-20px); }
            50% { transform: translateY(0); }
            75% { transform: translateY(-10px); }
        }

        .completion-title {
            text-align: center;
            margin-bottom: 32px;
        }

        .completion-title h2 {
            color: var(--success);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .completion-title p {
            color: var(--text-muted);
            font-size: 16px;
        }

        .info-box {
            background: var(--background);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .info-box.success { border-left-color: var(--success); }
        .info-box.warning { border-left-color: var(--warning); }
        .info-box.info { border-left-color: var(--primary); }

        .info-box strong {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .info-box ul {
            margin-left: 20px;
            font-size: 14px;
            line-height: 1.8;
            color: var(--text-muted);
        }

        .info-box pre {
            background: var(--card-bg);
            padding: 12px;
            border-radius: 6px;
            overflow-x: auto;
            margin-top: 8px;
            font-size: 12px;
            border: 1px solid var(--border);
        }

        .footer-note {
            text-align: center;
            margin-top: 32px;
            padding: 24px;
            background: var(--background);
            border-radius: 12px;
        }

        .footer-note p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .installer-header {
                padding: 32px 24px;
            }

            .installer-header h1 {
                font-size: 24px;
            }

            .installer-header p {
                font-size: 14px;
            }

            .installer-content {
                padding: 32px 24px;
            }

            .button-group {
                flex-direction: column-reverse;
            }

            .progress-steps {
                padding: 0;
            }

            .step-circle {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .welcome-section ol {
                font-size: 13px;
                margin-left: 16px;
            }
        }

        /* Password Toggle Button */
        .password-toggle:hover {
            color: var(--text);
        }

        .password-toggle:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: 4px;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <div class="installer-header-content">
                <div class="installer-logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h1>Installation Wizard</h1>
                <p>Let's set up your {{ config('app.name') }} workspace</p>
            </div>
        </div>

        <div class="installer-content">
            @yield('content')
        </div>
    </div>

    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';

                // Clear previous errors
                form.querySelectorAll('.error-message').forEach(el => el.remove());
                form.querySelectorAll('.form-group').forEach(el => el.classList.remove('error'));

                try {
                    const response = await fetch(form.action, {
                        method: form.method,
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Success - redirect
                        submitBtn.innerHTML = '<span class="loading"></span> Success! Redirecting...';
                        
                        // Add longer delay for steps that modify .env to allow server to stabilize
                        const isDbStep = form.action.includes('/database');
                        const isSiteStep = form.action.includes('/site');
                        const isEmailStep = form.action.includes('/email');
                        const delay = (isDbStep || isSiteStep || isEmailStep) ? 1500 : 500;
                        
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, delay);
                    } else {
                        // Handle different error formats
                        let errorDisplayed = false;

                        // Check for general error message (like database connection errors)
                        if (data.error) {
                            showGeneralError(data.error);
                            errorDisplayed = true;
                        }
                        
                        // Check for message field
                        if (data.message && !errorDisplayed) {
                            showGeneralError(data.message);
                            errorDisplayed = true;
                        }

                        // Check for validation errors
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const input = form.querySelector(`[name="${field}"]`);
                                if (input) {
                                    const fieldGroup = input.closest('.form-group');
                                    if (fieldGroup) {
                                        fieldGroup.classList.add('error');
                                        const errorDiv = document.createElement('div');
                                        errorDiv.className = 'error-message';
                                        errorDiv.textContent = Array.isArray(data.errors[field]) 
                                            ? data.errors[field][0] 
                                            : data.errors[field];
                                        input.parentNode.insertBefore(errorDiv, input.nextSibling);
                                    }
                                }
                            });

                            // Scroll to first error
                            const firstError = form.querySelector('.error');
                            if (firstError) {
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                            errorDisplayed = true;
                        }

                        // If no specific error format found
                        if (!errorDisplayed) {
                            showGeneralError('An error occurred during installation. Please check your inputs and try again.');
                        }

                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error('Installation error:', error);
                    showGeneralError('An unexpected error occurred. Please check your connection and try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });

        // Function to show general error messages
        function showGeneralError(message) {
            // Remove any existing general error
            const existingError = document.querySelector('.general-error-alert');
            if (existingError) {
                existingError.remove();
            }

            // Create error alert
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-error general-error-alert';
            errorAlert.style.animation = 'fadeIn 0.3s ease-out';
            errorAlert.innerHTML = `
                <span class="alert-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </span>
                <div>
                    <strong>Error</strong>
                    <p style="margin-top: 4px;">${message}</p>
                </div>
            `;

            // Insert at the top of the form or content area
            const form = document.querySelector('form');
            if (form) {
                form.insertBefore(errorAlert, form.firstChild);
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                const content = document.querySelector('.installer-content');
                if (content) {
                    content.insertBefore(errorAlert, content.firstChild);
                    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        // Remove general error on form change
        document.addEventListener('input', function(e) {
            if (e.target.matches('input, select, textarea')) {
                const generalError = document.querySelector('.general-error-alert');
                if (generalError) {
                    generalError.style.opacity = '0';
                    generalError.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => generalError.remove(), 300);
                }
            }
        });

        // Remove error state on input change
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', () => {
                const fieldGroup = input.closest('.form-group');
                if (fieldGroup) {
                    fieldGroup.classList.remove('error');
                    const errorMsg = fieldGroup.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
        });

        // Password visibility toggle function
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeOffIcon = button.querySelector('.eye-off-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        // Make togglePassword function globally available
        window.togglePassword = togglePassword;
    </script>

    @stack('scripts')
</body>
</html>
