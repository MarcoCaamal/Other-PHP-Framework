<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo mensaje de contacto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f5f5f5;
        }
        .content {
            padding: 20px 0;
        }
        .message-box {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f5f5f5;
            font-size: 12px;
            color: #888;
        }
        .details {
            margin-bottom: 20px;
        }
        .details dt {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .details dd {
            margin-left: 0;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nuevo mensaje de contacto</h1>
        </div>
        
        <div class="content">
            <p>Se ha recibido un nuevo mensaje a través del formulario de contacto:</p>
            
            <dl class="details">
                <dt>Nombre:</dt>
                <dd><?= htmlspecialchars($name) ?></dd>
                
                <dt>Email:</dt>
                <dd><?= htmlspecialchars($email) ?></dd>
                
                <dt>Asunto:</dt>
                <dd><?= htmlspecialchars($subject) ?></dd>
                
                <dt>Fecha:</dt>
                <dd><?= htmlspecialchars($date) ?></dd>
            </dl>
            
            <p><strong>Mensaje:</strong></p>
            <div class="message-box">
                <?= nl2br(htmlspecialchars($userMessage)) ?>
            </div>
            
            <p>Puedes responder directamente a este mensaje para contactar con el remitente.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. Sistema de contacto.</p>
        </div>
    </div>
</body>
</html>
