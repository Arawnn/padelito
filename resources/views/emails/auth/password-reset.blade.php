<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; padding: 40px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset your password</h2>
        <p>Hello {{ $userName }},</p>
        <p>We received a request to reset your password. Click the button below to choose a new one.</p>
        <p style="margin: 32px 0;">
            <a href="{{ config('app.frontend_url') }}/reset-password?token={{ urlencode($token) }}&email={{ urlencode($email) }}" class="btn">
                Reset password
            </a>
        </p>
        <p>This link will expire in <strong>1 hour</strong>. If you did not request a password reset, you can safely ignore this email.</p>
        <div class="footer">
            <p>If the button above doesn't work, copy and paste the following link into your browser:</p>
            <p>{{ config('app.frontend_url') }}/reset-password?token={{ urlencode($token) }}&email={{ urlencode($email) }}</p>
        </div>
    </div>
</body>
</html>
