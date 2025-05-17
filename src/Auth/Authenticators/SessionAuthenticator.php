<?php

namespace LightWeight\Auth\Authenticators;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Auth\Authenticatable;

class SessionAuthenticator implements AuthenticatorContract
{
    public function login(Authenticatable $authenticatable, bool $remember = false)
    {
        session()->set('_auth', $authenticatable); 
        
        // If remember is enabled, we could set a longer session expiration
        // or implement a remember-me token system here
        if ($remember) {
            // Example implementation - in a real application, this would be more secure
            session()->set('_auth_remember', true);
            // You might add code here to extend session lifetime or store a secure token
        }
    }
    
    public function logout(Authenticatable $authenticatable)
    {
        session()->remove("_auth");
        session()->remove("_auth_remember");
    }
    
    public function isAuthenticated(Authenticatable $authenticatable): bool
    {
        return session()->get("_auth")?->id() === $authenticatable->id();
    }
    
    public function resolve(): ?Authenticatable
    {
        return session()->get("_auth");
    }

    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials): bool
    {
        // Obtener el modelo de usuario configurado
        $userModel = config('auth.user_model', '\App\Models\User');
        
        // Buscar el usuario por el identificador (por defecto email)
        $identifierField = config('auth.identifier_field', 'email');
        $user = $userModel::where($identifierField, $credentials[$identifierField] ?? null)->first();
        
        if (!$user) {
            return false;
        }
        
        // Verificar la contraseña
        $passwordField = config('auth.password_field', 'password');
        if (!password_verify($credentials[$passwordField] ?? '', $user->$passwordField)) {
            return false;
        }
        
        return true;
    }

    /**
     * Retrieve user by credentials
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $userModel = config('auth.user_model', '\App\Models\User');
        $identifierField = config('auth.identifier_field', 'email');
        return $userModel::where($identifierField, $credentials[$identifierField] ?? null)->first();
    }

    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        // Primero validamos las credenciales
        if (!$this->validate($credentials)) {
            return false;
        }
        
        // Si las credenciales son válidas, obtenemos el usuario
        $user = $this->retrieveByCredentials($credentials);
        
        if (!$user) {
            return false;
        }

        // Si todo es correcto, hacemos login
        $this->login($user, $remember);
        
        return true;
    }
}
