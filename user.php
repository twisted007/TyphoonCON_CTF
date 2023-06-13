<?php

class User{
    private $conn;
    private $table_name = "users";
    public $id;
    public $username;
    public $uuid;
    public $password;
 
    public function __construct($db){
        $this->conn = $db;
    }
 
    function register(){
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username = :username,
                    uuid = :uuid,
                    password = :password,
                    role = :role";
     
        $stmt = $this->conn->prepare($query);
        $this->username=htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':uuid', $this->uuid);
        $stmt->bindParam(':role', $this->role);
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    function userExists(){
        $query = "SELECT id, username, password, uuid, role
                FROM " . $this->table_name . "
                WHERE username = ?
                LIMIT 0,1";
     
        $stmt = $this->conn->prepare( $query );
        $this->username=htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        $num = $stmt->rowCount();
        if($num>0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->uuid = $row['uuid'];
            $this->role = $row['role'];
            return true;
        }

        return false;
    }
     
    function getInfo($userid){
        $query = "SELECT id, username, uuid, role
                FROM " . $this->table_name . "
                WHERE id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );
        $this->id = (int)$userid;
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $num = $stmt->rowCount();
        if($num>0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->username = $row['username'];
            $this->uuid = $row['uuid'];
            $this->role = $row['role'];
        }
    }

}
