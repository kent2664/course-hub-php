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

    public function registerUser($email, $password, $role, $deleteFlag = 0): void
    {
        try {
            $errFlag = false;
            $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            $userid = require_auth($db);
            if ($db->connect_error) {
                throw new \Exception("DB error: " . $db->connect_error, 500);
            }
            $insertPrep = $db->prepare("INSERT INTO `users` (passWord,email,role,deleteFlag) VALUES (?,?,?,?)");
            $selectPrep = $db->prepare("SELECT userId FROM `users` WHERE email=?");
            $selectPrep->bind_param("s", $email);
            $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $insertPrep->bind_param("sssi", $password, $email, $role, $deleteFlag);
            $selectPrep->execute();
            $result = $selectPrep->get_result();
            if ($result->num_rows > 0)
                $errFlag = true;
            else {
                if (!$insertPrep->execute())
                    $errFlag = true;
            }
            $db->close();
            
            if (!$errFlag){
                $this->auditService->outputLog($userid, true, "Successfully inserted user with email: " . $email);
                Response::json([], 200, "Record Added");

            }else {
                $this->auditService->outputLog($userid, false, "Failed to insert user with email: " . $email);
                throw new \Exception("Record insertion failed.", 400);
            }
        } catch (\Exception $err) {
            Response::json([], $err->getCode(), $err->getMessage());
        }

    }

    public function login(string $email, string $password): ?bool
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
            $this->auditService->logLogin($email, false, $ip, null);
            Response::json([], 400, "Failed login attempt");
        }

        $userInfo = $userData->fetch_assoc();
        $loadUser->close();
        $userId = $userInfo["userId"];

        if (!isset($userInfo['passWord'])) {
            $db->close();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $this->auditService->logLogin($email, false, $ip, $userId);
            Response::json([], 400, "Failed login attempt");
        }

        // Get the hashed and salted password from db
        $hash = $userInfo['passWord'];

        if (!password_verify($password, $hash)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $this->auditService->logLogin($email, false, $ip, $userId);
            Response::json([], 400, "Failed login attempt");
        }

        // Successful login
        $userid = (int) $userInfo["userId"];
        $token = generate_token();
        store_access_token($db, $userid, $token);
        $db->close();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $this->auditService->logLogin($email, true, $ip,$userId);
        json_response(json_writer([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => ACCESS_TOKEN_TTL_SECONDS
        ]));
    }

    public function logout(): void
    {
        // Expect a Bearer token for logout; revoke it in the api_token table
        $token = get_bearer_token();
        if ($token === null) {
            Response::json([], 401, "Missing Bearer Token");
        }

        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            Response::json([], 500, "DB connection error");
        }

        // Find user id for this token (even if expired/revoked status will be handled separately)
        $hash = hash('sha256', $token);
        $selectPrep = $db->prepare("SELECT userId FROM api_token WHERE token_hash=?");
        $selectPrep->bind_param("s", $hash);
        $selectPrep->execute();
        $result = $selectPrep->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            $db->close();
            Response::json([], 401, "Invalid token");
        }
        $userId = (int)$row['userId'];

        // Get user email for audit logging
        $selUser = $db->prepare("SELECT email FROM users WHERE userId=?");
        $selUser->bind_param("i", $userId);
        $selUser->execute();
        $userRes = $selUser->get_result();
        $userRow = $userRes->fetch_assoc();
        $email = $userRow['email'] ?? '';

        // Revoke the token (set revoked_at = NOW())
        revoke_access_token($db, $token);

        $ip = $_SERVER['REMOTE_ADDR'] ?? "UNKNOWN";
        $this->auditService->logLogout($email, $ip, $userId);
        $db->close();

        Response::json([], 200, "Logout succesfull");
    }

    public function isAuthenticated(): ?bool
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            throw new Exception(json_writer(['error' => "Connection issue."]), 500);
        }
        $userid = require_auth($db);
        json_response(json_writer([
            'user_id' => $userid,
            'message' => 'Authenticated'
        ]));
    }

    public function isAdmin(): bool
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            throw new Exception(json_writer(['error' => "Connection issue."]), 500);
        }
        $userid = require_auth($db);
        $selectPrep = $db->prepare("SELECT role FROM users WHERE userId=?");
        $selectPrep->bind_param("i", $userid);
        $selectPrep->execute();
        $userInfo = $selectPrep->get_result();
        $userData = $userInfo->fetch_assoc();
        if ($userData["role"] == "admin") {
            return true;
        } else {
            return false;
        }
    }

    public function isStaff(): bool
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            throw new Exception(json_writer(['error' => "Connection issue."]), 500);
        }
        $userid = require_auth($db);
        $selectPrep = $db->prepare("SELECT role FROM users WHERE userId=?");
        $selectPrep->bind_param("i", $userid);
        $selectPrep->execute();
        $userInfo = $selectPrep->get_result();
        $userData = $userInfo->fetch_assoc();
        if ($userData["role"] == "admin" || $userData["role"] == "teacher") {
            return true;
        } else {
            return false;
        }
    }

    public function isTeacher(): bool
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            throw new Exception(json_writer(['error' => "Connection issue."]), 500);
        }
        $userid = require_auth($db);
        $selectPrep = $db->prepare("SELECT role FROM users WHERE userId=?");
        $selectPrep->bind_param("i", $userid);
        $selectPrep->execute();
        $userInfo = $selectPrep->get_result();
        $userData = $userInfo->fetch_assoc();
        if ($userData["role"] == "teacher") {
            return true;
        } else {
            return false;
        }
    }

    public function getRole(): ?string
    {
        $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            throw new Exception(json_writer(['error' => "Connection issue."]), 500);
        }
        $userid = require_auth($db);
        $selectPrep = $db->prepare("SELECT role FROM users WHERE userId=?");
        $selectPrep->bind_param("i", $userid);
        $selectPrep->execute();
        $userInfo = $selectPrep->get_result();
        $userData = $userInfo->fetch_assoc();
        return $userData["role"];
    }

    public function getCurrentUser(int $userId): ?array
    {
        try{
            $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            if ($db->connect_error) {
                throw new \Exception("Connection issue.", 500);
            }
            $getUser = $db->prepare("SELECT id, email, role FROM users WHERE id = ? LIMIT 1");
            $getUser->bind_param("i", $userId);
            $getUser->execute();
            $userData = $getUser->get_result();
            if ($userData->num_rows === 0) {
                throw new \Exception("User not found.", 404);
            }
            $userInfo = $userData->fetch_assoc();

            $getUser->close();
            $db->close();

            return [
                'id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'role' => $userInfo['role']
            ];

        }catch(\Exception $e){
                throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
?>