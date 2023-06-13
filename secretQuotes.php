<?php

include_once 'config/db.php';
include_once 'quote.php';
include_once 'auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$auth->isAuthorized();
$quote = new Quote($db);

if( $auth->isAdmin && $auth->isInternal() ){
	$data = json_decode(file_get_contents("php://input")) or die(json_encode(array("message" => "Not valid JSON body")));
	$password = $data->password;
	if ( $password === getenv("SECRET") ) {
		$quote->getSecretQuote();
		echo json_encode(array(
            "quoteID" => $quote->id,
            "author" => $quote->author,
            "quote" => $quote->quote,
            "image" => $quote->downloadImage($quote)
	    ));

	}else{
		http_response_code(401);
		die(json_encode(array("message" => "Invalid password.")));
	}
}else{
	http_response_code(401);
	die(json_encode(array("message" => "Internal access only.")));
}
?>
