<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Password Reset Request</h2>
        
        <p>Hi {{ $user->name }},</p>
        
        <p>You requested to reset your password. Use the code below to proceed:</p>
        
        <div style="background-color: #f0f0f0; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px;">
            <h1 style="margin: 0; letter-spacing: 5px; color: #0066cc;">{{ $code }}</h1>
        </div>
        
        <p>This code will expire in 10 minutes.</p>
        
        <p>If you didn't request this, please ignore this email.</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #666;">
            This is an automated email. Please do not reply to this message.
        </p>
    </div>
</body>
</html>
