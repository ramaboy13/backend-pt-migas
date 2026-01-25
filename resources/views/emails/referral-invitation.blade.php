{{-- resources/views/emails/referral-invitation.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #047857; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background-color: #f9fafb; }
        .code { font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #047857; 
                text-align: center; padding: 15px; background-color: #d1fae5; 
                border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; background-color: #047857; color: white; 
                 padding: 12px 24px; text-decoration: none; border-radius: 5px; 
                 font-weight: bold; margin: 10px 0; }
        .info-box { background-color: #f0fdf4; border-left: 4px solid #047857; 
                    padding: 15px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; 
                  color: #6b7280; font-size: 14px; }
        .warning { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT Migas Finance System</h1>
            <p>You're Invited!</p>
        </div>
        
        <div class="content">
            <h2>Hello!</h2>
            
            <p>You have been invited by <strong>{{ $createdBy }}</strong> to join the 
               <strong>PT Migas Finance System</strong> as an <strong>{{ $role }}</strong>.</p>
            
            <div class="info-box">
                <h3>Registration Details:</h3>
                <ul>
                    <li><strong>Role:</strong> {{ ucfirst($role) }}</li>
                    <li><strong>Invited by:</strong> {{ $createdBy }}</li>
                    @if($expiresAt)
                    <li><strong>Valid until:</strong> {{ \Carbon\Carbon::parse($expiresAt)->format('d F Y, H:i') }}</li>
                    @endif
                </ul>
            </div>
            
            <p>To register, please use this referral code:</p>
            
            <div class="code">
                {{ $referralCode }}
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $registrationUrl }}" class="button">
                    Register Now
                </a>
            </div>
            
            <p>Or copy this registration link:</p>
            <p style="word-break: break-all; color: #1d4ed8;">
                {{ $registrationUrl }}
            </p>
            
            <div class="info-box">
                <h3>Important Information:</h3>
                <ul>
                    <li>You <span class="warning">must</span> use a valid Gmail address</li>
                    <li>After registration, you will receive an email verification</li>
                    <li>Email verification is required before you can access the system</li>
                    <li>Keep your referral code confidential</li>
                </ul>
            </div>
            
            <p>If you have any questions, please contact the system administrator 
               or the person who invited you.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>
            <strong>PT Migas Finance Administration</strong></p>
            <p>This is an automated invitation message. Please do not reply to this email.</p>
            <p style="font-size: 12px; color: #9ca3af;">
                If you received this email by mistake, please ignore it.
            </p>
        </div>
    </div>
</body>
</html>