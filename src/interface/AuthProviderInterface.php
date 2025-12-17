<?php 
    namespace App\Interface;
    interface AuthProviderInterface{

        public function login(string $email, string $password): ?bool;

        public function logout(): void;

        public function registerUser($email, $password, $role, $deleteFlag = 0): void;

        public function isAuthenticated(): ?bool;

        public function getCurrentUser(): ?array;
    }


?>