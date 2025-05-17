<!DOCTYPE html>
<html>
<head>
    <title>Not Found</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .error-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            padding: 40px;
            text-align: center;
        }
        .error-code {
            font-size: 96px;
            font-weight: bold;
            color: #f0f0f0;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #e74c3c;
        }
        .error-message {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .back-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .back-button:hover {
            background-color: #2980b9;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-title">Page Not Found</div>
        <div class="error-message">
            The page you are looking for does not exist or has been moved.
        </div>
        <a href="/" class="back-button">Go to Homepage</a>
    </div>
</body>
</html>
