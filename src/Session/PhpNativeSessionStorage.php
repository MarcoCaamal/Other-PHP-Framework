<?php

namespace LightWeight\Session;

use LightWeight\Events\SessionStartedEvent;
use LightWeight\Session\Contracts\SessionStorageContract;

class PhpNativeSessionStorage implements SessionStorageContract
{
    public function start()
    {
        $isNew = !isset($_SESSION);

        if (!session_start()) {
            throw new \RuntimeException("Failed starting session");
        }

        // Dispatch session.started event
        try {
            if (function_exists('event')) {
                event(new SessionStartedEvent([
                    'session_id' => $this->id(),
                    'is_new' => $isNew,
                    'session_data' => $_SESSION ?? []
                ]));
            }
        } catch (\Throwable $e) {
            // Log error but don't break session functionality
            error_log("Error dispatching session.started event: " . $e->getMessage());
        }
    }
    public function id(): string
    {
        return session_id();
    }
    public function save()
    {
        session_write_close();
    }
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public function set(string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public function remove(string $key)
    {
        unset($_SESSION[$key]);
    }
    public function destroy()
    {
        session_destroy();
    }
}
