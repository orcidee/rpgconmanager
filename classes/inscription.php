<?php
require_once(dirname(__FILE__).'/../classes/user.php');

class Inscription {
    private $inscriptionId;
    private $userId;
    private $partyId;
    public $isValid;
    public $status;
    private $mysqli;

    /**
    * Procède à l'inscription de l'utilisateur ($userId) à la partie ($partyId).
    */
    public function __construct ($userId, $partyId) {
        $this->mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        $userId = addslashes($userId);
        $partyId = addslashes($partyId);

        $this->isValid = false;
        // Verifier la nouveauté
        $sql = "SELECT * FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'";
        $res = $this->mysqli->query($sql);
        if($res && ($res->num_rows === 0)){
            // Procède à l'inscription
            $sql = "INSERT INTO Inscriptions SET `userId`='$userId', `partyId`='$partyId'";
            $res = $this->mysqli->query($sql);
            if($res){
                // Get automatic inscriptionId
                $sql = "SELECT * FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'";
                $res = $this->mysqli->query($sql);
                if($res && ($res->num_rows === 1)){
                    $row = $res->fetch_assoc();
                    $this->inscriptionId = $row['inscriptionId'];
                    $this->userId = $row['userId'];
                    $this->partyId = $row['partyId'];
                    $this->isValid = true;
                    $this->status = "created";
                }
            }
        }elseif($res && ($res->num_rows === 1)){
            // Deja inscrit
            $row = $res->fetch_assoc();
            $this->inscriptionId = $row['inscriptionId'];
            $this->userId = $row['userId'];
            $this->partyId = $row['partyId'];
            $this->isValid = true;
            $this->status = "old";
        }
        if( ! $this->isValid){
            $this->status = "error";
        }
    }
    /**
    * Supprime une inscription existante, sur la base d'un utilisateur ($userId) et d'une partie
    * ($partyId).
    */
    public static function unsubscribe ($partyId, $userId){
        $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        $userId = addslashes($userId);
        $partyId = addslashes($partyId);
        return $mysqli->query("DELETE FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'");
    }
}