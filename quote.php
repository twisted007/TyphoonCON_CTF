<?php

class Quote{
    private $conn;
    private $table_name = "quotes";
    private $secret_table_name = "secret_quotes";
    public $id;
    public $quote;
    public $author;
    public $url;
    public $LastRowID;

 	public function __construct($db){
        $this->conn = $db;
    }

	function getQuote($quoteID=null){
		if (!$quoteID) {
			$quoteID = rand(1,10); // will add more later
		}
	    $query = "SELECT id, quote, author, url
	            FROM " . $this->table_name . "
	            WHERE id = ?
	            LIMIT 0,1";
	 
	    $stmt = $this->conn->prepare( $query );
	    $quoteID=(int)$quoteID;
	    $stmt->bindParam(1, $quoteID);
	    $stmt->execute();
	    $num = $stmt->rowCount();
	    if($num>0){
	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
	        $this->id = $row['id'];
	        $this->quote = $row['quote'];
	        $this->author = $row['author'];
	        $this->url = $row['url'];
	    }
	}

	function getSecretQuote(){
	    $query = "SELECT id, quote, author, url
	            FROM " . $this->secret_table_name . "
	            WHERE id = ?
	            LIMIT 0,1";
	 
	    $stmt = $this->conn->prepare( $query );
	    $quoteID = 1 ; // temporary equal 1
	    $stmt->bindParam(1, $quoteID);
	    $stmt->execute();
	    $num = $stmt->rowCount();
	    if($num>0){
	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
	        $this->id = $row['id'];
	        $this->quote = $row['quote'];
	        $this->author = $row['author'];
	        $this->url = $row['url'];
	        return true;
	    }
	    return false;
	}

	function downloadImage($quote_object){
		$ch = curl_init($quote_object->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Accept: */*",
			"Accept-Encoding: gzip",
			"X-Quote-Author: " . $quote_object->author
		]);
		$image = curl_exec($ch);
		curl_close($ch);
		$encodedImage = base64_encode($image);
		$finalImage = "data:image/png;base64," . $encodedImage;
		return $finalImage;
	}

	function createQuote(){
        $query = "INSERT INTO " . $this->table_name . "
                SET quote = :quote,
                    author = :author,
                    url = :url";
     
        $stmt = $this->conn->prepare($query);
        $this->quote=strip_tags($this->quote);
        $this->author=strip_tags($this->author); 
		if ( (strtolower(substr($this->url, 0, 7)) === "http://" || strtolower(substr($this->url, 0, 8)) === "https://")  && filter_var($this->url, FILTER_VALIDATE_URL)){
			$this->url=strip_tags($this->url);
		}else{
			$this->url = "https://t4.ftcdn.net/jpg/00/84/67/19/360_F_84671939_jxymoYZO8Oeacc3JRBDE8bSXBWj0ZfA9.jpg";
		}
        $stmt->bindParam(':quote', $this->quote);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':url', $this->url);
        if($stmt->execute()){
        	$this->LastRowID = $this->conn->lastInsertId();
            return true;
        }
        return false;
	}

}

?>