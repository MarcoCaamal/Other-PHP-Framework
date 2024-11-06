<?php

namespace LightWeight\Session;

use LightWeight\Session\Contracts\SessionStorageContract;

class PhpNativeSessionStorage implements SessionStorageContract
{
    public function start()
    {
        if (!session_start()) {
            throw new \RuntimeException("Failed starting session");
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
