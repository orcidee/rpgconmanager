<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../classes/user.php');

class Inscription {
    private $inscriptionId;
    private $userId;
    private $partyId;
    public $isValid;
    public $status;
    
    /**
    * Procède à l'inscription de l'utilisateur ($userId) à la partie ($partyId).
    */
    public function __construct ($userId, $partyId) {
    
        $userId = addslashes($userId);
        $partyId = addslashes($partyId);
    
        $this->isValid = false;
        // Verifier la nouveauté
        $sql = "SELECT * FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
        if($res && ($nb === 0)){
            // Procède à l'inscription
            $sql = "INSERT INTO Inscriptions SET `userId`='$userId', `partyId`='$partyId'";
            $res = mysql_query ( $sql );
            $nb = mysql_affected_rows();
            if($res && ($nb === 1)){
                // Get automatic inscriptionId
                $sql = "SELECT * FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'";
                $res = mysql_query ( $sql );
                $nb = mysql_num_rows($res);
                if($res && ($nb === 1)){
                    $row = mysql_fetch_assoc($res);
                    $this->inscriptionId = $row['inscriptionId'];
                    $this->userId = $row['userId'];
                    $this->partyId = $row['partyId'];
                    $this->isValid = true;
                    $this->status = "created";
                }
            }
        }elseif($res && ($nb === 1)){
            // Deja inscrit
            $row = mysql_fetch_assoc($res);
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
        $userId = addslashes($userId);
        $partyId = addslashes($partyId);
        $sql = "DELETE FROM Inscriptions WHERE userId='$userId' AND partyId='$partyId'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        return $res && ($nb === 1);
    }
}
?>
