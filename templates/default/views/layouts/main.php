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
            background-color: #f8f9fa;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #343a40;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            margin-bottom: 30px;
        }
        header h1 {
            margin: 0;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>LightWeight Framework</h1>
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
