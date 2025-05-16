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
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
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
            
            <div class="info-row">
                <span class="info-label">Fecha:</span> <?= htmlspecialchars($date) ?>
            </div>
            <div class="info-row">
                <span class="info-label">Nombre:</span> <?= htmlspecialchars($name) ?>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span> <?= htmlspecialchars($email) ?>
            </div>
            <div class="info-row">
                <span class="info-label">Asunto:</span> <?= htmlspecialchars($subject) ?>
            </div>
            
            <div class="message-box">
                <p><span class="info-label">Mensaje:</span></p>
                <p><?= nl2br(htmlspecialchars($userMessage)) ?></p>
            </div>
            
            <p>Puede responder directamente a este correo para contactar al remitente.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. Todos los derechos reservados.</p>
            <p>Este mensaje fue enviado desde el formulario de contacto de su sitio web.</p>
        </div>
    </div>
</body>
</html>
