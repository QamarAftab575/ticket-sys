<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Form Submission</title>
    <style>
        body { font-family: sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #1e293b; padding: 24px 32px; }
        .header h1 { color: #f8fafc; font-size: 20px; margin: 0; }
        .body { padding: 32px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; }
        .value { margin-top: 4px; font-size: 15px; color: #111827; }
        .message-box { background: #f3f4f6; border-left: 4px solid #6366f1; padding: 16px; border-radius: 4px; margin-top: 24px; white-space: pre-wrap; font-size: 14px; color: #374151; }
        .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📬 New Contact Form Submission</h1>
        </div>
        <div class="body">
            <div class="field">
                <div class="label">Name</div>
                <div class="value">{{ $name }}</div>
            </div>
            <div class="field">
                <div class="label">Email</div>
                <div class="value"><a href="mailto:{{ $email }}" style="color:#6366f1;">{{ $email }}</a></div>
            </div>
            <div class="field">
                <div class="label">Subject</div>
                <div class="value">{{ $subject }}</div>
            </div>
            <div class="message-box">{{ $body }}</div>
        </div>
        <div class="footer">
            {{ config('app.name') }} &mdash; Contact Form Notification
        </div>
    </div>
</body>
</html>
