<?php 

    namespace App\Auth;
    use App\Interface\AuthProviderInterface;

    class InMemoryAuthProvider implements AuthProviderInterface{
        // Array for initial users
        private array $users = [
            ['email' => 'matheus@gmail.com','password'=> 'matheus123', 'role'=>'student'],
            ['email' => 'kenta@gmail.com','password'=> 'kenta123', 'role'=>'admin'],
            ['email' => 'tiana@gmail.com','password'=> 'tiana123', 'role'=>'teacher']
        ];

        private ?array $currentUser = null; //"?" means either the value should be a string or null

        public function login(string $email, string $password): bool
        {
            // Looping through users array and support both plain and hashed passwords
            foreach ($this->users as $user) {
                if (!isset($user["email"]) || $user['email'] != $email) continue;

                $stored = $user['password'];

                if (password_verify($password, $stored)) {
                    $this->currentUser = $user;
                    return true;
                }
            }

            // If no match, return false
            return false;
        }

        public function logout():void{
            $this->currentUser = null;
        }

        public function isAuthenticated(): bool{

            return $this->currentUser !== null;
        }

        public function getCurrentUser(int $userId): ?array{
            return $this->currentUser;
        }
    }


?>