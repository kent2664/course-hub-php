<?php 
    namespace App\Interface;
    interface AuthProviderInterface{

        public function login(string $username, string $password): bool;

        public function logout(): void;

        public function isAuthenticated(): bool;

        public function getCurrentUser(): ?array;
    }


?>