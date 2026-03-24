<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-body {
            margin-top: 16px;
        }
        .email-body img {
            max-width: 100%;
            height: auto;
        }
        .email-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e5e5;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-body">
            {!! $body !!}
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
