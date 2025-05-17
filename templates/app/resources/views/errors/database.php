<!DOCTYPE html>
<html>
<head>
    <title>Database Error</title>
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
        .error-details {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
            font-family: monospace;
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
        <div class="error-title">
            Database Error
        </div>
        <div class="error-message">
            <?= $message ?? 'A database error has occurred.' ?>
        </div>
        
        <?php if(config('exceptions.debug', false) === true): ?>
        <div class="error-details">
            <strong>Error <?= $code ?? '500' ?></strong><br>
            <br>
            <?php if(isset($file) && isset($line)): ?>
            <strong>Location:</strong> <?= $file ?>:<?= $line ?><br>
            <?php endif; ?>
            
            <?php if(isset($trace)): ?>
            <strong>Stack Trace:</strong><br>
            <pre><?= print_r($trace, true) ?></pre>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="javascript:history.back()" class="back-button">Go back</a>
    </div>
</body>
</html>
