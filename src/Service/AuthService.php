<?php 

  namespace App\Services;
  use App\Interface\AuthProviderInterface;
  class AuthService{
    private AuthProviderInterface $provider;
    function __construct(AuthProviderInterface $provider){
        $this->provider = $provider;
    }
    function attemptLogin(string $email, string $password): string{
        if($this->provider->login($email,$password)){
            return "Login successful. Welcome, {$email}.";
        }
        return "Login failed. Invalid credentials.";

    }
    function status(){
        if($this->provider->isAuthenticated()){
            $user = $this->provider->getCurrentUser();
            return $user;
        }

        return "No user is currently authenticated.";
    }
    
  }
  function registerUser($fullname,$email,$pass,$addr){
                $errFlag = false;
                $db = new mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                if($db->connect_error){
                    throw new Exception("DB error: ".$db->connect_error,500);
                }
                $insertPrep = $db->prepare("INSERT INTO user_tb (ufullname,email,pass,uaddr) VALUES (?,?,?,?)");
                $selectPrep = $db->prepare("SELECT uid FROM user_tb WHERE email=?");
                $selectPrep->bind_param("s",$email);
                $pass = password_hash($pass,PASSWORD_BCRYPT,['cost'=>10]);
                $insertPrep->bind_param("ssss",$fullname,$email,$pass,$addr);
                $selectPrep->execute();
                $result = $selectPrep->get_result();
                if($result->num_rows > 0) $errFlag = true;
                else{
                    if(!$insertPrep->execute()) $errFlag = true;
                }
                $db->close();
                if(!$errFlag)
                    echo "record added";
                else
                    throw new Exception("Record insertion failed.",500);
    }


?>