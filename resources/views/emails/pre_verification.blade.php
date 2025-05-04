{{-- resources/views/emails/confirm-new-email.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your New Email Address</title>
    <style>
        /* Base styles - compatible with most email clients */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f9fafb;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f9fafb;
            padding: 30px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #000000;
            padding: 30px 30px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px 20px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .important {
            font-weight: 600;
            color: #1e3a8a;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .button {
            display: inline-block;
            background-color: #000000;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 12px 30px;
            border-radius: 6px;
            text-align: center;
            /* Ensure fallback for outlook */
            mso-padding-alt: 12px 30px;
            mso-border-alt: 10px solid #000000;
        }
        .note {
            font-size: 14px;
            color: #6b7280;
            margin-top: 30px;
            font-style: italic;
        }
        .expiry {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 15px;
            margin-top: 30px;
            color: #4b5563;
            font-size: 14px;
            border-left: 4px solid #000000;
        }
        .footer {
            background-color: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-top: 1px solid #e5e7eb;
        }
        .company {
            font-weight: 600;
            color: #4b5563;
        }
        .alternate-link {
            display: block;
            margin-top: 20px;
            word-break: break-all;
            font-size: 13px;
            color: #6b7280;
        }
        
        /* Mobile responsive styles */
        @media screen and (max-width: 600px) {
            .container {
                width: 100%;
                border-radius: 0;
            }
            .header, .footer {
                border-radius: 0;
            }
            .content {
                padding: 30px 20px 15px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Email Address Verification</h1>
            </div>
            
            <div class="content">
                <p class="greeting">Hello,</p>
                
                <p class="message">
                    Thank you for registering with <span class="important">{{ config('app.name', 'Laravel') }}</span>. To complete your registration and verify your email address, please insert the verification code below.
                </p>
                
                <hr> 

                <p class="message">
                    The verification code is: <span class="important font-size: 24px;">{{ $verificationCode }}</span>
                </p>
                
                <div class="expiry">
                    <strong>Please note:</strong> This verification code will expire in 1 hour from when it was sent.
                </div>
                
                <p class="note">
                    If you didn't create an account with us, you can safely ignore this message.
                </p>
                
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} <span class="company">{{ config('app.name', 'Laravel') }}</span>. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
