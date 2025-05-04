<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation {{ $verified ? 'Successful' : 'Failed' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --primary-hover: #000000;
            --success-color: #10b981;
            --success-bg: #ecfdf5;
            --error-color: #ef4444;
            --error-bg: #fef2f2;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: var(--text-primary);
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        
        #app {
            width: 100%;
            max-width: 36rem;
        }
        
        .card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .success-header {
            background-color: var(--success-bg);
            color: var(--success-color);
        }
        
        .error-header {
            background-color: var(--error-bg);
            color: var(--error-color);
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        .icon-container {
            margin: 0 auto 1.5rem auto;
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-icon {
            background-color: var(--success-color);
            color: white;
        }
        
        .error-icon {
            background-color: var(--error-color);
            color: white;
        }
        
        .icon {
            font-size: 2rem;
            font-weight: bold;
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
        }
        
        .button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .brand {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .brand-name {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--primary-color);
        }
        
        .help-text {
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .help-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .help-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="app">

        
        <div class="brand">
            <div class="brand-name">
                <img src="{{ asset('loanbram-black.png') }}" alt="Bram Agency Logo" style="width: 150px; height: auto;">
            </div>
        </div>

        <div class="card">
            <div class="card-header {{ $verified ? 'success-header' : 'error-header' }}">
                <div class="icon-container {{ $verified ? 'success-icon' : 'error-icon' }}">
                    <span class="icon">{{ $verified ? '✓' : '✗' }}</span>
                </div>
            </div>
            
            <div class="card-body">
                @if($verified)
                    <h1>Email Confirmation Successful</h1>
                    <p>Your email address has been successfully verified and updated. You can now use your new email address to sign in to your account.</p>
                @else
                    <h1>Email Confirmation Failed</h1>
                    <p>{{ $errorMessage ?? "We couldn't verify your email address. The confirmation link may have expired or is invalid. Please try requesting a new verification email from your profile page." }}</p>
                @endif
                
                <a href="{{ url('/login') }}" class="button">Go to Login</a>
                
                <div class="help-text">
                    Need assistance? <a href="{{ url('/contact') }}" class="help-link">Contact our support team</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 