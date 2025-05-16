# Email API Reference

This page documents in detail the classes and methods that make up the LightWeight email system.

## Contracts (Interfaces)

### `LightWeight\Mail\Contracts\MailerContract`

Main interface for the email service.

```php
interface MailerContract
{
    /**
     * Sends a simple email
     *
     * @param string $to Recipient's email address
     * @param string $subject Email subject
     * @param string $message Email content (can be HTML)
     * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
     * @return bool True if the email was sent successfully, False otherwise
     */
    public function send(string $to, string $subject, string $message, array $options = []): bool;
    
    /**
     * Sends an email using a template
     *
     * @param string $to Recipient's email address
     * @param string $subject Email subject
     * @param string $template Name of the template to use
     * @param array $data Data to pass to the template
     * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
     * @return bool True if the email was sent successfully, False otherwise
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;
    
    /**
     * Gets the current driver
     *
     * @return \LightWeight\Mail\Contracts\MailDriverContract
     */
    public function getDriver(): MailDriverContract;
    
    /**
     * Sets the driver to use
     *
     * @param string $driver Driver name ('phpmailer', 'smtp', 'log', etc.)
     * @return self
     */
    public function setDriver(string $driver): self;
    
    /**
     * Registers a new driver
     *
     * @param string $name Driver name
     * @param string $class Class that implements MailDriverContract
     * @return self
     */
    public function registerDriver(string $name, string $class): self;
}
```

### `LightWeight\Mail\Contracts\MailDriverContract`

Interface that all mail drivers must implement.

```php
interface MailDriverContract
{
    /**
     * Resets all properties for a new email
     *
     * @return self
     */
    public function reset(): self;
    
    /**
     * Sets the email sender
     *
     * @param string $email Sender's email address
     * @param string|null $name Sender's name (optional)
     * @return self
     */
    public function setFrom(string $email, ?string $name = null): self;
    
    /**
     * Sets the main recipient of the email
     *
     * @param string $email Recipient's email address
     * @return self
     */
    public function setTo(string $email): self;
    
    /**
     * Sets the email subject
     *
     * @param string $subject Email subject
     * @return self
     */
    public function setSubject(string $subject): self;
    
    /**
     * Sets the HTML body of the email
     *
     * @param string $html HTML content of the email
     * @return self
     */
    public function setHtmlBody(string $html): self;
    
    /**
     * Sets the plain text body of the email (optional)
     *
     * @param string|null $text Plain text content
     * @return self
     */
    public function setTextBody(?string $text): self;
    
    /**
     * Adds a carbon copy (CC) recipient
     *
     * @param string $email Email address
     * @return self
     */
    public function addCC(string $email): self;
    
    /**
     * Adds a blind carbon copy (BCC) recipient
     *
     * @param string $email Email address
     * @return self
     */
    public function addBCC(string $email): self;
    
    /**
     * Adds an attachment to the email
     *
     * @param string $path Full path to the file
     * @param string|null $name Alternative name for the file (optional)
     * @return self
     */
    public function addAttachment(string $path, ?string $name = null): self;
    
    /**
     * Sends the email with the current configuration
     *
     * @return bool True if the email was sent successfully, False otherwise
     */
    public function send(): bool;
}
```

## Main Class

### `LightWeight\Mail\Mailer`

Main implementation of the mail service, uses the Strategy pattern to switch between different drivers.

```php
class Mailer implements MailerContract
{
    /**
     * Constructor
     *
     * @param string $defaultDriver Default driver to use
     * @param ViewContract|null $viewEngine View engine for rendering templates (optional)
     */
    public function __construct(string $defaultDriver = 'phpmailer', ?ViewContract $viewEngine = null);
    
    /**
     * Sends a simple email
     */
    public function send(string $to, string $subject, string $message, array $options = []): bool;
    
    /**
     * Sends an email using a template
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;
    
    /**
     * Gets the current driver
     */
    public function getDriver(): MailDriverContract;
    
    /**
     * Sets the driver to use
     */
    public function setDriver(string $driver): self;
    
    /**
     * Registers a new driver
     */
    public function registerDriver(string $name, string $class): self;
    
    /**
     * Renders a template with the provided data
     *
     * @param string $template Template name
     * @param array $data Data for the template
     * @return string Rendered HTML content
     * @throws \LightWeight\Mail\Exceptions\MailerException If the template doesn't exist
     */
    protected function renderTemplate(string $template, array $data): string;
}
```

## Mail Drivers

### `LightWeight\Mail\Drivers\PhpMailerDriver`

Driver that uses the PHPMailer library to send emails via SMTP.

```php
class PhpMailerDriver implements MailDriverContract
{
    // Implements all methods from MailDriverContract
    // Uses PHPMailer internally to send emails
}
```

### `LightWeight\Mail\Drivers\LogDriver`

Driver that logs emails instead of sending them. Useful for development environments.

```php
class LogDriver implements MailDriverContract
{
    // Implements all methods from MailDriverContract
    // Logs information about emails instead of sending them
}
```

## Service Provider

### `LightWeight\Providers\MailServiceProvider`

Registers mail services in the dependency container.

```php
class MailServiceProvider implements ServiceProviderContract
{
    /**
     * Registers email-related services in the container
     *
     * @param DIContainer $container Dependency injection container
     * @return void
     */
    public function registerServices(DIContainer $container): void;
}
```

## Helper Functions

### `LightWeight\Helpers\mailSend()`

```php
/**
 * Sends an email
 *
 * @param string $to Recipient's email address
 * @param string $subject Email subject
 * @param string $message Email content (can be HTML)
 * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
 * @return bool True if the email was sent successfully, False otherwise
 */
function mailSend(string $to, string $subject, string $message, array $options = []): bool;
```

### `LightWeight\Helpers\mailTemplate()`

```php
/**
 * Sends an email using a template
 *
 * @param string $to Recipient's email address
 * @param string $subject Email subject
 * @param string $template Name of the template to use
 * @param array $data Data to pass to the template
 * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
 * @return bool True if the email was sent successfully, False otherwise
 */
function mailTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;
```

### `LightWeight\Helpers\mailDriver()`

```php
/**
 * Gets or sets the email driver
 *
 * @param string|null $driver If provided, sets the driver
 * @return \LightWeight\Mail\Contracts\MailDriverContract|\LightWeight\Mail\Contracts\MailerContract
 */
function mailDriver(?string $driver = null);
```

## Exceptions

### `LightWeight\Mail\Exceptions\MailerException`

```php
/**
 * Specific exception for email sending errors
 */
class MailerException extends \Exception
{
    // Specific methods for handling mail errors
}
```
