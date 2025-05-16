<?php

/**
 * Helper functions for sending emails
 */

/**
 * Sends an email
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email content (can be HTML)
 * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
 * @return bool True if email was sent successfully, False otherwise
 */
function mailSend(string $to, string $subject, string $message, array $options = []): bool
{
    $mailer = app(\LightWeight\Mail\Contracts\MailerContract::class);
    return $mailer->send($to, $subject, $message, $options);
}

/**
 * Sends an email using a template
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $template Template name to use
 * @param array $data Data to pass to the template
 * @param array $options Additional options like 'cc', 'bcc', 'attachments', etc.
 * @return bool True if email was sent successfully, False otherwise
 */
function mailTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool
{
    $mailer = app(\LightWeight\Mail\Contracts\MailerContract::class);
    return $mailer->sendTemplate($to, $subject, $template, $data, $options);
}

/**
 * Gets or sets the email driver
 *
 * @param string|null $driver If provided, sets the driver
 * @return \LightWeight\Mail\Contracts\MailDriverContract|\LightWeight\Mail\Contracts\MailerContract
 */
function mailDriver(?string $driver = null)
{
    $mailer = app(\LightWeight\Mail\Contracts\MailerContract::class);
    
    if ($driver !== null) {
        return $mailer->setDriver(driver: $driver);
    }
    
    return $mailer->getDriver();
}
