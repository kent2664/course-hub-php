<?php

namespace App\Providers;

use Src\Common\Response;
use App\Services\AuditService;
use App\Interface\AuthProviderInterface;

class DataAuthProvider implements AuthProviderInterface
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function login(string $email, string $password): bool
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            Response::json([], 500, "Error connecting to database: {$db->connect_error}");
        }

        $loadUser = $db->prepare("SELECT * FROM `users` WHERE email=?");
        $loadUser->bind_param("s", $email);
        $loadUser->execute();
        $userData = $loadUser->get_result();
        // Checking if the data exist in the database
        if ($userData->num_rows === 0) {
            // User not found
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $this->auditService->logLogin($email, false, $ip);
            Response::json([], 400, "Failed login attempt");
        }

        $userInfo = $userData->fetch_assoc();
        $loadUser->close();

        if (!isset($userInfo['passWord'])) {
            $db->close();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $this->auditService->logLogin($email, false, $ip);
            Response::json([], 400, "Failed login attempt");
        }

        $hash = $userInfo['passWord'];
        $db->close();

        if (!password_verify($password, $hash)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $this->auditService->logLogin($email, false, $ip);
            Response::json([], 400, "Failed login attempt");
        }

        // Successful login
        $_SESSION["email"] = $email;
        $_SESSION["authenticated"] = true;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $this->auditService->logLogin($email, true, $ip);
        Response::json([], 200, 'Login successful');
    }

    public function logout(): void
    {
        if (isset($_SESSION["email"])) {
            $this->auditService->logLogout($_SESSION["email"]);
            session_unset();
            session_destroy();
            Response::json([], 200, "Logout succesfull");
        } else {
            Response::json([], 400, "Logout attempt failed");
        }
    }

    public function isAuthenticated(): bool
    {
        return false;
    }

    public function getCurrentUser(): ?array
    {
        return [];
    }
}
?>