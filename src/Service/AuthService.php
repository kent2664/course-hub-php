<?php

namespace App\Services;
use App\Interface\AuthProviderInterface;
class AuthService
{
    private AuthProviderInterface $provider;
    function __construct(AuthProviderInterface $provider)
    {
        $this->provider = $provider;
    }
    function attemptLogin(string $email, string $password): string
    {
        if ($this->provider->login($email, $password)) {
            return "Login successful. Welcome, {$email}.";
        }
        return "Login failed. Invalid credentials.";

    }
    function status()
    {
        if ($this->provider->isAuthenticated()) {
            $user = $this->provider->getCurrentUser();
            return $user;
        }

        return "No user is currently authenticated.";
    }

}


?>