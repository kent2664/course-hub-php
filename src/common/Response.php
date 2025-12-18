<?php

namespace Src\Common;

class Response {
    public static function json($data = [], $statusCode = 200, $message = '') {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($statusCode);
        
        $response = [
            "success" => $statusCode >= 200 && $statusCode < 300,
            "message" => $message,
            "data" => $data
        ];

        if ($data == []) { 
            unset($response['data']); 
        }

        echo json_encode($response);
        exit;
    }
    
    public static function error($message, $statusCode = 400) {
        self::json(null, $statusCode, $message);
    }
}

?>