<?php 

    namespace App\Auth;
    use App\Interface\AuthProviderInterface;

    class InMemoryAuthProvider implements AuthProviderInterface{
        private array $users = [
            ['username' => 'matheus','password'=> 'matheus123', 'role'=>'student'],
            ['username' => 'kenta','password'=> 'kenta123', 'role'=>'admin'],
            ['username' => 'tiana','password'=> 'tiana123', 'role'=>'teacher']
        ];

        private ?array $currentUser = null; //"?" means either the value should be a string or null

        public function login(string $username, string $password): bool
        {
            // Looping through users array
            foreach($this->users as $user){
                if(isset($user["username"]) && $user['username'] == $username && $user['password'] === $password){
                    $this->currentUser = $user;
                    return true;
                }
            }
            // If is different from what was requested, return false
            return false;
        }

        public function logout():void{
            $this->currentUser = null;
        }

        public function isAuthenticated(): bool{

            return $this->currentUser !== null;
        }

        public function getCurrentUser(): ?array{
            return $this->currentUser;
        }
    }


?>