<?php 

    namespace App\Auth;
    use App\Interface\AuthProviderInterface;

    class InMemoryAuthProvider implements AuthProviderInterface{
        private array $users = [
            ['email' => 'matheus@gmail.com','password'=> 'matheus123', 'role'=>'student'],
            ['email' => 'kenta@gmail.com','password'=> 'kenta123', 'role'=>'admin'],
            ['email' => 'tiana@gmail.com','password'=> 'tiana123', 'role'=>'teacher']
        ];

        private ?array $currentUser = null; //"?" means either the value should be a string or null

        public function login(string $email, string $password): bool
        {
            // Looping through users array
            foreach($this->users as $user){
                if(isset($user["email"]) && $user['email'] == $email && $user['password'] === $password){
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