<?php

include_once 'config/db.php';
include_once 'quote.php';
include_once 'user.php';
include_once 'auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$auth->isAuthorized();
$quote = new Quote($db);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
	$quote->getQuote(); 
	echo json_encode(array(
        "author" => $quote->author,
        "quote" => $quote->quote,
        "image" => $quote->downloadImage($quote)
    ));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $auth->isAdmin ) {
	$data = json_decode(file_get_contents("php://input")) or die(json_encode(array("message" => "Not valid JSON body")));
	$quote->quote = $data->quote;
	$quote->author = $data->author;
	$quote->url = $data->url;
	if ($quote->createQuote()) {
		$quote->getQuote($quote->LastRowID);
		echo json_encode(array(
			"message" => "New quote added successfully.",
	        "author" => $quote->author,
	        "quote" => $quote->quote,
	        "image" => $quote->downloadImage($quote)
    	));
	}else{
		http_response_code(400);
		die(json_encode(array("message" => "Error.")));
	}
}

?>