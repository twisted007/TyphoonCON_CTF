<?php

require 'config/core.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth{

    private $table_name = "users";
    private $jwtToken;
    public $jwtTokenContents;
    public $id;
    public $role;
    public $isValidToken;
    public $isAdmin;

    
    function __construct($db){
        $this->conn = $db;
        $headers = null;
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $this->jwtToken = $matches[1];
        }else{
            http_response_code(401);
            die(json_encode(array(
                "message" => "Unauthorized."
            )));
        }
    }

    function isAuthorized(){
        try {
            $decoded = JWT::decode($this->jwtToken, new Key(getenv("SECRET"), 'HS256'));
            $this->jwtTokenContents = $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            die(json_encode(array(
                "message" => "Invalid token.",
                "error" => $e->getMessage()
            )));
        }

        $query = "SELECT id, role
                FROM " . $this->table_name . "
                WHERE uuid = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );
        $user_uuid = $this->jwtTokenContents->uuid;
        $stmt->bindParam(1, $user_uuid);
        $stmt->execute();
        $num = $stmt->rowCount();
        if($num>0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->role = $row['role'];
            if ( $this->id !== $this->jwtTokenContents->id ) { die(json_encode(array("message" => "Bad token."))); }
            if ( $this->role === "ADMIN" ) {
                $this->isAdmin = true;     
            }
        }
    }

    function isInternal(){
        if ($_SERVER['REMOTE_ADDR'] === "127.0.0.1") { return true; }
        return false;
    }
}
