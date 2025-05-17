<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LightWeight Framework</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f0f8ff;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #1e88e5;
            color: #fff;
            padding: 1.5rem 0;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        header h1 {
            margin: 0;
            font-weight: 300;
            letter-spacing: 1px;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #5c6bc0;
            border-top: 1px solid #bbdefb;
        }
        .content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        a {
            color: #1976d2;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        .logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header-content svg {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <svg class="logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="40px" height="40px">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
            </svg>
            <h1>LightWeight Framework</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="content">
            @content
        </div>
    </div>
    
    <footer>
        <p>Powered by LightWeight Framework &copy; <?php echo date('Y'); ?></p>
    </footer>
</body>
</html>
