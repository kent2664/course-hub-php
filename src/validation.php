<?php

function input_sanitizer(?string $value, string $type = 'text'): ?string{
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
        case "pass":
            // Sanitize the password, removing those characters that can break the password (breaking a line, for example)
            $value = str_replace(["\r", "\n"], '', $value);
            return $value;
        case "text":
        default:
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return $value === '' ? null : $value;
    }
}

?>