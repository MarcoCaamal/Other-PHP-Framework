<?php
namespace OtherPHPFramework\Session;

use OtherPHPFramework\Session\Contracts\SessionContract;

class Session {
    protected SessionContract $storage;
    
    public function __construct(SessionContract $storage) {
        $this->storage = $storage;
        $this->storage->start();
    }

    public function flash(string $key, mixed $value) {
    }
    public function id(): string {
        return $this->storage->id();
    }
    public function get(string $key, $default = null) {
        return $this->storage->get($key, $default);
    }
    public function set(string $key, mixed $value) {
        return $this->storage->set($key, $value);
    }
    public function has(string $key): bool {
        return $this->storage->has($key);
    }
    public function remove(string $key) {
        return $this->storage->remove($key);
    }
    public function destroy() {
        return $this->storage->destroy();
    }
}