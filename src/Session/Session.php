<?php

namespace LightWeight\Session;

use LightWeight\Session\Contracts\SessionStorageContract;

class Session
{
    protected SessionStorageContract $storage;
    public const FLASH_KEY = '_flash';

    public function __construct(SessionStorageContract $storage)
    {
        $this->storage = $storage;
        $this->storage->start();

        if(!$this->storage->has(self::FLASH_KEY)) {
            $this->storage->set(self::FLASH_KEY, ['old' => [], 'new' => []]);
        }
    }
    public function __destruct()
    {
        $flash = $this->storage->get(self::FLASH_KEY);
        
        // Verificar si flash y sus elementos están definidos correctamente
        if ($flash && isset($flash['old']) && is_array($flash['old'])) {
            foreach ($flash['old'] as $key) {
                $this->storage->remove($key);
            }
        }
        
        $this->ageFlashData();
        $this->storage->save();
    }
    public function ageFlashData()
    {
        $flash = $this->storage->get(self::FLASH_KEY);
        
        // Si flash no está configurado o no tiene la estructura esperada, lo inicializamos
        if (!$flash || !isset($flash['old']) || !isset($flash['new'])) {
            $flash = ['old' => [], 'new' => []];
        } else {
            $flash['old'] = $flash['new'];
            $flash['new'] = [];
        }
        
        $this->storage->set(self::FLASH_KEY, $flash);
    }
    public function flash(string $key, mixed $value)
    {
        $this->storage->set($key, $value);
        $flash = $this->storage->get(self::FLASH_KEY);
        
        // Si flash no está configurado o no tiene la estructura esperada, lo inicializamos
        if (!$flash || !isset($flash['new'])) {
            $flash = ['old' => [], 'new' => []];
        }
        
        $flash['new'][] = $key;
        $this->storage->set(self::FLASH_KEY, $flash);
    }
    public function id(): string
    {
        return $this->storage->id();
    }
    public function get(string $key, $default = null)
    {
        return $this->storage->get($key, $default);
    }
    public function set(string $key, mixed $value)
    {
        return $this->storage->set($key, $value);
    }
    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }
    public function remove(string $key)
    {
        return $this->storage->remove($key);
    }
    public function destroy()
    {
        return $this->storage->destroy();
    }
}
