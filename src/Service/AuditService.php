<?php

namespace App\Services;

class AuditService
{
    private string $logFile;

    function __construct(string $logPath = __DIR__ . '/../../logs/audit.log')
    {
        $this->logFile = $logPath;
        $this->ensureLogDirectory();
    }

    // This function will see if the directory exists, if no, then it will create, with read and write permission for THIS code,
    // and the "true" means that php will see if this entire path exists, then if not, will create the path (recursive mode) 
    private function ensureLogDirectory(): void
    {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // This function will log every time someone login in, into an log audit file.
    public function logLogin(string $email, bool $success, string $ipAddress, ?int $id): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logEntry = "[{$timestamp}] | IP: {$ipAddress} | EMAIL: {$email} | ACTION: LOGIN ATTEMPT | STATUS: {$status} | USER ID: {$id}" . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    // This function will log every time someone logout, into an log audit file.
    public function logLogout(string $email, string $ipAddress = null, ?int $id): void
    {

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] | IP: {$ipAddress} | EMAIL: {$email} | ACTION: LOGOUT | USER ID: {$id}" . "\n" . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function logRegister(string $email, string $ipAddress, string $role, string $message): void
    {

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] | IP: {$ipAddress} | EMAIL: {$email} | ACTION: REGISTER | STATUS: {$message} | USER ROLE: {$role}" . "\n" . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    // This function will log every time someone login in, into an log audit file.
    public function outputLog(string $userid, bool $success, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logEntry = "[{$timestamp}] | USERID: {$userid} | STATUS: {$status} | MESSAGE: {$message}" . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    // This function will log every time someone login in, into an log audit file.
    public function outputLog(string $userid, bool $success, string $message): void{
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logEntry = "[{$timestamp}] | USERID: {$userid} | STATUS: {$status} | MESSAGE: {$message}"  . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function getAuditLog(): string
    {
        if (file_exists($this->logFile)) {
            return file_get_contents($this->logFile);
        }
        return "No audit logs found.";
    }
}

?>