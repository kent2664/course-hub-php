<?php 

    namespace App\Auth;
    use App\Interface\AuthProviderInterface;

    class InMemoryAuthProvider implements AuthProviderInterface{
        private array $users = [
            'alice' => 'password123',
            'bob'   => 'secret',
        ];

        private ?string $currentUser = null; //"?" means either the value should be a string or null

        public function login(string $username, string $password): bool
        {
            if(isset($this->users[$username]) && $this->users[$username] === $password){
                $this->currentUser = $username;
                return true;
            }
            return false;
        }

        public function logout():void{
            $this->currentUser = null;
        }

        public function isAuthenticated(): bool{

            return $this->currentUser !== null;
        }

        public function getCurrentUser(): ?string{
            return $this->currentUser;
        }
    }


?>