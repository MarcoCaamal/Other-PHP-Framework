<?php

namespace LightWeight\Log;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use LightWeight\Log\Contracts\LoggerContract;

/**
 * Logger class for LightWeight framework
 * 
 * This class is a wrapper around Monolog to provide logging functionality
 * to the LightWeight framework
 */
class Logger implements LoggerContract
{
    /**
     * The Monolog logger instance
     *
     * @var MonologLogger
     */
    protected MonologLogger $logger;

    /**
     * The default log format
     *
     * @var string
     */
    protected string $format = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    /**
     * Create a new logger instance
     *
     * @param string $channel The channel name
     * @param array $config The logger configuration
     */
    public function __construct(string $channel = 'LightWeight', array $config = [])
    {
        $this->logger = new MonologLogger($channel);
        $this->configureHandlers($config);
    }

    /**
     * Configure the logger handlers
     *
     * @param array $config
     * @return void
     */
    protected function configureHandlers(array $config): void
    {
        $path = $config['path'] ?? storagePath('logs/lightweight.log');
        $level = $this->getLogLevel($config['level'] ?? 'debug');
        $days = $config['days'] ?? 7;
        $bubble = $config['bubble'] ?? true;

        $formatter = new LineFormatter(
            $config['format'] ?? $this->format,
            $config['date_format'] ?? 'Y-m-d H:i:s',
            true, // Allow inline line breaks
            true  // Ignore empty context and extra
        );

        // Add rotating file handler
        $handler = new RotatingFileHandler($path, $days, $level, $bubble);
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);

        // Add additional handlers if configured
        if (isset($config['handlers']) && is_array($config['handlers'])) {
            foreach ($config['handlers'] as $handler) {
                if ($handler instanceof \Monolog\Handler\HandlerInterface) {
                    $this->logger->pushHandler($handler);
                }
            }
        }
    }

    /**
     * Get the Monolog level from the configuration
     *
     * @param string $level
     * @return Level
     */
    protected function getLogLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Debug,
        };
    }

    /**
     * Get the underlying Monolog instance
     *
     * @return MonologLogger
     */
    public function getLogger(): mixed
    {
        return $this->logger;
    }

    /**
     * Add a handler to the logger
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     * @return self
     */
    public function pushHandler(mixed $handler): self
    {
        if ($handler instanceof \Monolog\Handler\HandlerInterface) {
            $this->logger->pushHandler($handler);
        }
        return $this;
    }

    /**
     * Log a debug message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log a notice message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Log a critical message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Log an alert message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Log an emergency message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Log with an arbitrary level
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
