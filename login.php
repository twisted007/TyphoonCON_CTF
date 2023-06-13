<?php

include_once 'config/db.php';
include_once 'config/core.php';
include_once 'user.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input")) or die(json_encode(array("message" => "Not valid JSON body")));
$user = new User($db);
$user->username = $data->username;
$user_exists = $user->userExists();

if($user_exists && password_verify($data->password, $user->password)){
    $token = array(  
       "iat" => $issued_at,
       "exp" => $expiration_time,
       "id" => $user->id,
       "uuid" => $user->uuid,
    );
    $jwt = JWT::encode($token, getenv("SECRET"), "HS256");
    http_response_code(200);
    echo json_encode(array(
        "message" => "Successful login.",
        "id" => $user->id,
        "jwt" => $jwt
    ));
 
}else{
    http_response_code(401);
    die(json_encode(array("message" => "Login failed.")));
}
?>