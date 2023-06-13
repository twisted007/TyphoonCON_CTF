<?php

include_once 'config/db.php';
include_once 'user.php';
include_once 'auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$auth->isAuthorized();
$user = new User($db);

if (isset($_GET['userid'])) {
	$userid = (int)$_GET['userid'];
	$user->getInfo($userid);
    echo json_encode(array(
        "message" => "Access granted.",
        "id" => $user->id,
        "username" => $user->username,
        "uuid" => $user->uuid,
        "role" => $user->role
    ));
}else{
	echo json_encode(array(
		"message" => "Access granted.",
	));
}

?>