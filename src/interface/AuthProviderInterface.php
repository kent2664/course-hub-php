<?php 
    namespace App\Contracts;
    interface AuthProviderInterface{

        public function login(string $username, string $password): bool;

        public function logout(): void;

        public function isAuthenticated(): bool;

        public function getCurrentUser(): ?string;
    }


?>