<?php

namespace LightWeight\Exceptions;

use Throwable;

/**
 * Class for sending notifications about critical exceptions
 */
class ExceptionNotifier
{
    /**
     * Available notification channels
     * 
     * @var array
     */
    protected static array $availableChannels = [
        'email', 'log', 'slack', 'webhook', 'sms'
    ];
    
    /**
     * Configured notification channels
     * 
     * @var array
     */
    protected static array $channels = [];
    
    /**
     * Initialize the notifier with configuration
     * 
     * @param array $config
     * @return void
     */
    public static function init(array $config = []): void
    {
        self::$channels = $config['channels'] ?? ['log'];
    }
    
    /**
     * Send notification about an exception
     * 
     * @param Throwable $exception
     * @param array|null $channels
     * @return void
     */
    public static function notify(Throwable $exception, ?array $channels = null): void
    {
        // Determine which channels to use
        $notifyChannels = $channels ?? self::$channels;
        
        // Normalize and filter channels
        $notifyChannels = array_intersect(
            $notifyChannels,
            self::$availableChannels
        );
        
        // Generate notification content
        $content = self::formatExceptionForNotification($exception);
        
        // Send to each channel
        foreach ($notifyChannels as $channel) {
            $method = 'notifyVia' . ucfirst($channel);
            if (method_exists(self::class, $method)) {
                self::{$method}($content, $exception);
            }
        }
    }
    
    /**
     * Format exception for notification
     * 
     * @param Throwable $exception
     * @return array
     */
    protected static function formatExceptionForNotification(Throwable $exception): array
    {
        $env = env('APP_ENV', 'production');
        $appName = config('app.name', 'LightWeight Application');
        
        return [
            'subject' => "[{$appName}] [{$env}] Exception: " . get_class($exception),
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $env,
            'application' => $appName,
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];
    }
    
    /**
     * Send notification via email
     * 
     * @param array $content
     * @param Throwable $exception
     * @return void
     */
    protected static function notifyViaEmail(array $content, Throwable $exception): void
    {
        $to = config('exceptions.notifications.email.to', '');
        
        if (empty($to)) {
            return;
        }
        
        $subject = $content['subject'];
        
        // Build email body
        $body = "An exception occurred in your application.\n\n";
        $body .= "Exception: {$content['exception']}\n";
        $body .= "Message: {$content['message']}\n";
        $body .= "File: {$content['file']} (line {$content['line']})\n";
        $body .= "URL: {$content['request_uri']}\n";
        $body .= "Environment: {$content['environment']}\n";
        $body .= "Time: {$content['timestamp']}\n\n";
        $body .= "Stack Trace:\n{$content['trace']}\n";
        
        // Send email
        mail($to, $subject, $body);
    }
    
    /**
     * Send notification via log
     * 
     * @param array $content
     * @param Throwable $exception
     * @return void
     */
    protected static function notifyViaLog(array $content, Throwable $exception): void
    {
        // Use the ExceptionLogger to log the notification
        ExceptionLogger::log($exception, ExceptionLogger::CRITICAL);
    }
    
    /**
     * Send notification via Slack
     * 
     * @param array $content
     * @param Throwable $exception
     * @return void
     */
    protected static function notifyViaSlack(array $content, Throwable $exception): void
    {
        $webhookUrl = config('exceptions.notifications.slack.webhook', '');
        
        if (empty($webhookUrl)) {
            return;
        }
        
        // Create Slack message payload
        $payload = [
            'text' => $content['subject'],
            'attachments' => [
                [
                    'color' => '#FF0000',
                    'title' => $content['message'],
                    'fields' => [
                        [
                            'title' => 'Exception',
                            'value' => $content['exception'],
                            'short' => true
                        ],
                        [
                            'title' => 'Environment',
                            'value' => $content['environment'],
                            'short' => true
                        ],
                        [
                            'title' => 'Location',
                            'value' => "{$content['file']} (line {$content['line']})",
                            'short' => false
                        ],
                        [
                            'title' => 'URL',
                            'value' => "{$content['request_method']} {$content['request_uri']}",
                            'short' => false
                        ]
                    ],
                    'footer' => "LightWeight Exception Notifier | {$content['timestamp']}"
                ]
            ]
        ];
        
        // Send to Slack
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
    
    /**
     * Send notification via webhook
     * 
     * @param array $content
     * @param Throwable $exception
     * @return void
     */
    protected static function notifyViaWebhook(array $content, Throwable $exception): void
    {
        $webhookUrl = config('exceptions.notifications.webhook.url', '');
        
        if (empty($webhookUrl)) {
            return;
        }
        
        // Send to webhook
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
    
    /**
     * Send notification via SMS
     * 
     * @param array $content
     * @param Throwable $exception
     * @return void
     */
    protected static function notifyViaSms(array $content, Throwable $exception): void
    {
        $to = config('exceptions.notifications.sms.to', '');
        $provider = config('exceptions.notifications.sms.provider', '');
        
        if (empty($to) || empty($provider)) {
            return;
        }
        
        // SMS content needs to be brief
        $message = "[{$content['application']}] Exception: {$content['exception']} - {$content['message']}";
        
        // Implementation would depend on the SMS provider
        // This is a placeholder for actual SMS sending logic
        // You would typically use a third-party service/API here
    }
}
