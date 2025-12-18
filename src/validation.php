<?php

use Src\Common\Response;

function input_sanitizer(?string $value, string $type = 'text'): ?string
{
    $value = trim($value);
    if ($value === null)
        return null;
    switch (strtolower($type)) {
        case "email":
            // Sanitize and validade the email
            $value = filter_var($value, FILTER_SANITIZE_EMAIL);
            if (!filter_var($value, FILTER_VALIDATE_EMAIL))
                return null;
            return $value;
        case "password":
            // Sanitize the password, removing those characters that can break the password (breaking a line, for example)
            $value = str_replace(["\r", "\n"], '', $value);
            return $value;
        case "text":
        default:
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return $value === '' ? null : $value;
    }
}
function checkKeys(string ...$keys): bool
{
    foreach ($keys as $key) {
        if (!isset($_REQUEST[$key])) {
            Response::json([],400,"Missing keys");
            throw new Exception("Invalid request.", 400);
        } else {
            $_REQUEST[$key] = input_sanitizer($_REQUEST[$key], $key);
        }
    }
    return true;
}
// Ensures that the provided data array contains all specified keys
function force_array_keys($data, $keys) {
    foreach ($keys as &$key) {
        if (!isset($data[$key])) {
            Response::json([],400,"Missing keys");
            throw new Exception("Missing key: " . $key);
        }else {
            $data[$key] = input_sanitizer($data[$key], $key);
        }
    }
}

?>