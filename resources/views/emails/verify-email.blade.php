<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }
        .logo span {
            color: #c4b5fd;
        }
        .content {
            padding: 40px 30px;
        }
        h1 {
            color: #f1f5f9;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            text-align: center;
        }
        p {
            color: #94a3b8;
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 24px;
            text-align: center;
        }
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            padding: 16px 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.5);
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.2), transparent);
            margin: 32px 0;
        }
        .link-fallback {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }
        .link-fallback p {
            font-size: 14px;
            margin-bottom: 12px;
        }
        .link-fallback a {
            color: #60a5fa;
            word-break: break-all;
            font-size: 13px;
        }
        .footer {
            background: rgba(15, 23, 42, 0.5);
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }
        .footer p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .footer p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Task<span>Flow</span></div>
        </div>
        <div class="content">
            <h1>Verify Your Email Address</h1>
            <p>Welcome to TaskFlow! Please click the button below to verify your email address and complete your registration.</p>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
            </div>

            <p>This verification link will expire in 60 minutes.</p>

            <div class="divider"></div>

            <div class="link-fallback">
                <p>If the button above doesn't work, copy and paste this URL into your browser:</p>
                <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </div>
        </div>
        <div class="footer">
            <p>If you did not create an account, no further action is required.</p>
            <p>&copy; {{ date('Y') }} TaskFlow. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

