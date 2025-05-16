# Email System

LightWeight offers a powerful email system that makes it easy to send emails from your application. The system is designed following the Strategy pattern, allowing you to easily switch between different mail drivers.

> For a detailed reference of all classes, interfaces, and methods, check the [Email API Reference](mail-api-reference.md).

## Main Features

- **Multiple drivers**: Support for different mail providers (PHPMailer, Log, etc.)
- **Email templates**: Send emails using HTML templates
- **Helper functions**: Simple API for sending emails from anywhere in the application
- **Attachments**: Support for file attachments in emails
- **CC and BCC**: Ability to add carbon copy and blind carbon copy recipients
- **Integration with the view engine**: Uses the same view engine as the application to render templates

## Configuration

The email system is configured in the `config/mail.php` file:

```php
return [
    // Default driver ('phpmailer', 'log', etc.)
    'default' => env('MAIL_DRIVER', 'phpmailer'),
    
    // Configuration for PHPMailer
    'phpmailer' => [
        'host' => env('MAIL_HOST', 'smtp.example.com'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', 'user@example.com'),
        'password' => env('MAIL_PASSWORD', 'secret'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'LightWeight App'),
    ],
    
    // Configuration for the log driver (useful for development)
    'log' => [
        'channel' => env('MAIL_LOG_CHANNEL', 'mail'),
    ],
];
```

Additionally, you should configure your credentials in the `.env` file:

```
MAIL_DRIVER=phpmailer
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="My Application"
```

## Basic Usage

### Sending a Simple Email

```php
// Using the helper function
mailSend(
    'recipient@example.com',
    'Email Subject',
    '<p>This is the HTML content of the email</p>'
);

// Or using the service directly
$mailer = app(\LightWeight\Mail\Contracts\MailerContract::class);
$mailer->send(
    'recipient@example.com',
    'Email Subject',
    '<p>This is the HTML content of the email</p>'
);
```

### Sending an Email with Additional Options

```php
mailSend(
    'recipient@example.com',
    'Email Subject',
    '<p>This is the HTML content of the email</p>',
    [
        'from' => 'sender@example.com',
        'from_name' => 'My Name',
        'cc' => ['cc1@example.com', 'cc2@example.com'],
        'bcc' => 'bcc@example.com',
        'text' => 'Plain text version of the email (optional)',
        'attachments' => [
            '/path/to/file.pdf',
            [
                'path' => '/path/to/file2.jpg',
                'name' => 'image.jpg'
            ]
        ]
    ]
);
```

### Sending an Email with a Template

Email templates are stored in `resources/views/emails/` and use the application's view engine.

```php
// Using the helper function
mailTemplate(
    'recipient@example.com',
    'Welcome to our application',
    'welcome',  // Template name (resources/views/emails/welcome.php)
    [
        'userName' => 'John Doe',
        'activationLink' => 'https://example.com/activate/123'
    ]
);

// Or using dot notation for templates in subdirectories
mailTemplate(
    'recipient@example.com',
    'Purchase Confirmation',
    'orders.confirmation',  // resources/views/emails/orders/confirmation.php
    [
        'orderNumber' => '12345',
        'items' => $items
    ]
);
```

### Changing the Driver at Runtime

```php
// Get the current driver
$currentDriver = mailDriver();

// Change to the log driver (useful for testing)
mailDriver('log');

// Send the email using the log driver
mailSend('test@example.com', 'Test', '<p>This email will be logged</p>');

// Restore the original driver
mailDriver('phpmailer');
```

## Creating Email Templates

Email templates are stored in `resources/views/emails/` and can use all features of the view engine, such as sections, partial includes, etc.

Example welcome template (`resources/views/emails/welcome.php`):

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to our platform</title>
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
            <h1>Welcome to our platform!</h1>
        </div>
        
        <div class="content">
            <p>Hello <?= htmlspecialchars($userName) ?>,</p>
            
            <p>Thank you for registering on our platform! We're excited to have you as a member.</p>
            
            <p>With your account, you'll be able to:</p>
            <ul>
                <li>Access all our features</li>
                <li>Manage your personal data</li>
                <li>Participate in our community</li>
                <li>And much more...</li>
            </ul>
            
            <p>If you have any questions, don't hesitate to contact us by replying to this email.</p>
            
            <p>
                <a href="<?= config('app.url', 'https://yoursite.com') ?>/login" class="button">Log in</a>
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. All rights reserved.</p>
            <p>This is an automated email, please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
```

## Custom Drivers

You can create custom drivers for the mail system by implementing the `LightWeight\Mail\Contracts\MailDriverContract` interface:

```php
namespace App\Mail\Drivers;

use LightWeight\Mail\Contracts\MailDriverContract;

class CustomMailDriver implements MailDriverContract
{
    // Implementation of required methods
    public function reset(): self { /* ... */ }
    public function setFrom(string $email, ?string $name = null): self { /* ... */ }
    public function setTo(string $email): self { /* ... */ }
    public function setSubject(string $subject): self { /* ... */ }
    public function setHtmlBody(string $html): self { /* ... */ }
    public function setTextBody(?string $text): self { /* ... */ }
    public function addCC(string $email): self { /* ... */ }
    public function addBCC(string $email): self { /* ... */ }
    public function addAttachment(string $path, ?string $name = null): self { /* ... */ }
    public function send(): bool { /* ... */ }
}
```

Then register your custom driver:

```php
use LightWeight\Mail\Contracts\MailerContract;

// In a service provider or in the application bootstrap
$mailer = app(MailerContract::class);
$mailer->registerDriver('custom', \App\Mail\Drivers\CustomMailDriver::class);

// Now you can use your custom driver
mailDriver('custom');
```

## Testing

To test email sending without actually sending emails, you can use the log driver:

```php
// Temporarily switch to the log driver
mailDriver('log');

// Send the email (it will be logged instead of being sent)
mailSend('test@example.com', 'Test', '<p>Test email</p>');

// Restore the original driver if needed
mailDriver('phpmailer');
```

Or create a specific test driver for your tests:

```php
class TestMailDriver implements MailDriverContract
{
    public $sentEmails = [];
    
    // Implementation of methods...
    
    public function send(): bool
    {
        $this->sentEmails[] = [
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->htmlBody,
            // Other fields...
        ];
        return true;
    }
}
```

## Security Considerations

- Avoid including sensitive information in emails
- Always use `htmlspecialchars()` or the automatic escaping of the view engine when displaying user-provided data
- Keep your SMTP credentials secure in the `.env` file (don't include them in version control)
- Consider using SPF, DKIM, and DMARC to improve email deliverability and security

## Troubleshooting

### Emails Are Not Being Sent

Check:
- SMTP configuration (host, port, credentials)
- Firewall or network restrictions that may block the SMTP port
- Application logs for specific errors
- That the mail driver is correctly configured

### Emails Are Going to the Spam Folder

Possible solutions:
- Properly configure SPF, DKIM, and DMARC records
- Use a real domain for the sender (not free emails like Gmail or Hotmail)
- Avoid words that may trigger spam filters
- Maintain a good balance between text and images

### Templates Not Found

If you receive template not found errors:
- Verify that the template exists in `resources/views/emails/`
- Check that you're using the correct notation (dots for subdirectories)
- Make sure file permissions are correct
