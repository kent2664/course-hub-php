<?php
    function json_response(string $jsonData, int $status = 200):void{
        http_response_code($status);
        header('Content-Type: application/json; charset-utf-8');
        echo $jsonData;
        exit;
    }
    function json_writer(array $data):string{
        return json_encode($data,JSON_UNESCAPED_UNICODE);;
    }
    function read_input():array{
        if(!empty($_POST)){
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw,true);
        return is_array($data) ? $data : [];
    }
    function get_bearer_token():?string{
        $auth = $_SERVER["HTTP_AUTHORIZATION"] ?? ''; //isset($_SERVER["HTTP_AUTHORIZATION"])&&$_SERVER["HTTP_AUTHORIZATION"]!=null?$_SERVER["HTTP_AUTHORIZATION"]:''
        
        if($auth === '' && function_exists('apache_request_headers')){ //in case that the server may not populate the HTTP_AUTHORIZATION
            $header = apache_request_headers();
            $auth = $header['Authorization'] ?? '';
        }
        if(!preg_match('/^Bearer\s+(.+)$/i',trim($auth),$m)){
            return null;
        }
        return trim($m[1]);
    }
    function generate_token(int $bytes=32):string{
        return bin2hex((random_bytes($bytes)));
    }
?>