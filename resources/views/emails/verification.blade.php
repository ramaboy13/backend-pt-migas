{{-- resources/views/emails/verification.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1a56db; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background-color: #f9fafb; }
        .button { display: inline-block; background-color: #1a56db; color: white; padding: 12px 24px; 
                  text-decoration: none; border-radius: 5px; font-weight: bold; }
        .code { font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #1a56db; 
                text-align: center; padding: 15px; background-color: #e0e7ff; border-radius: 5px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; 
                  color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT Migas Finance</h1>
        </div>
        
        <div class="content">
            <h2>Hello, {{ $name }}!</h2>
            
            <p>Thank you for registering with PT Migas Finance System.</p>
            <p>Please verify your email address by clicking the button below:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $verificationUrl }}" class="button">
                    Verify Email Address
                </a>
            </div>
            
            <p>Or use this verification code:</p>
            
            <div class="code">
                {{ $verificationCode }}
            </div>
            
            <p style="margin-top: 20px;">
                This verification link will expire in <strong>24 hours</strong>.
            </p>
            
            <p>If you did not create an account, no further action is required.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>
            <strong>PT Migas Finance Team</strong></p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>