<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a nuestra plataforma</title>
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
        .button {
            display: inline-block;
            background-color: #4a89dc;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido/a a nuestra plataforma!</h1>
        </div>
        
        <div class="content">
            <p>Hola <?= htmlspecialchars($userName) ?>,</p>
            
            <p>¡Gracias por registrarte en nuestra plataforma! Estamos emocionados de tenerte como miembro.</p>
            
            <p>Con tu cuenta podrás:</p>
            <ul>
                <li>Acceder a todas nuestras funcionalidades</li>
                <li>Gestionar tus datos personales</li>
                <li>Participar en nuestra comunidad</li>
                <li>Y mucho más...</li>
            </ul>
            
            <p>Si tienes alguna pregunta, no dudes en contactarnos respondiendo a este correo electrónico.</p>
            
            <p>
                <a href="<?= config('app.url', 'https://yoursite.com') ?>/login" class="button">Iniciar sesión</a>
            </p>
        </div>
                    
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. Todos los derechos reservados.</p>
            <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
