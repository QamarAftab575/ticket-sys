<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We received your message</title>
    <style>
        body { font-family: sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #6366f1; padding: 24px 32px; }
        .header h1 { color: #ffffff; font-size: 20px; margin: 0; }
        .body { padding: 32px; font-size: 15px; line-height: 1.7; }
        .highlight { font-weight: 600; color: #111827; }
        .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>✅ We received your message!</h1>
        </div>
        <div class="body">
            <p>Hi <span class="highlight">{{ $name }}</span>,</p>
            <p>Thank you for reaching out to <span class="highlight">{{ config('app.name') }}</span>! We have received your message regarding "<span class="highlight">{{ $subject }}</span>" and will get back to you as soon as possible.</p>
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            {{ config('app.name') }} &mdash; This is an automated confirmation. Please do not reply to this email.
        </div>
    </div>
</body>
</html>
