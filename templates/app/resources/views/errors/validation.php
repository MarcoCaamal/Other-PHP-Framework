<!DOCTYPE html>
<html>
<head>
    <title>Validation Error</title>
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
            min-height: 100vh;
            padding: 20px;
        }
        .error-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            padding: 40px;
        }
        .error-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #e74c3c;
            text-align: center;
        }
        .error-message {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.5;
            text-align: center;
        }
        .validation-errors {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .validation-field {
            margin-bottom: 16px;
        }
        .field-name {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .field-error {
            color: #e74c3c;
            margin-left: 12px;
        }
        .back-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
            text-align: center;
            width: 100%;
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
            Validation Error
        </div>
        <div class="error-message">
            Please fix the following errors:
        </div>
        
        <div class="validation-errors">
            <?php foreach ($errors as $field => $fieldErrors): ?>
                <div class="validation-field">
                    <div class="field-name"><?= ucfirst($field) ?>:</div>
                    <?php foreach ($fieldErrors as $error): ?>
                        <div class="field-error">• <?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a href="javascript:history.back()" class="back-button">Go back and correct errors</a>
    </div>
</body>
</html>
