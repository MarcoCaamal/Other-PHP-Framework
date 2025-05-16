<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de contacto</title>
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
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f5f5f5;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirmación de mensaje recibido</h1>
        </div>
        
        <div class="content">
            <p>Hola <?= htmlspecialchars($userName) ?>,</p>
            
            <p>Gracias por contactarnos. Hemos recibido tu mensaje y te responderemos lo antes posible.</p>
            
            <p>Nuestro equipo de soporte está trabajando para responder a todas las consultas en un plazo de 24-48 horas laborables.</p>
            
            <p>Si tu consulta es urgente, no dudes en contactarnos por teléfono.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. Todos los derechos reservados.</p>
            <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
