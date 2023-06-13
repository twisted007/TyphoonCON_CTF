<?php

include_once 'config/db.php';
include_once 'config/core.php';
include_once 'user.php';
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input")) or die(json_encode(array("message" => "Not valid JSON body")));
$user = new User($db);

 
$user->username = $data->username;
$user->password = $data->password;
$user->uuid = Uuid::uuid4(); 
$user->role = "USER";

if( !empty($user->username) && !empty($user->password) &&  $user->userExists() ){
    http_response_code(409);
    die(json_encode(array("message" => "User already exsits.")));
}elseif( !empty($user->username) && !empty($user->password) &&  !$user->userExists() && $user->register() ){
    http_response_code(200);
    die(json_encode(array("message" => "User created successfully.")));
}else{
    http_response_code(400);
    die(json_encode(array("message" => "Unable to create user.")));
}

?>