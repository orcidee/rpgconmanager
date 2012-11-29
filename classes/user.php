<?php
require_once(dirname(__FILE__).'/../conf/bd.php');

class User {

    private $userId;
    private $lastname;
    private $firstname;
    private $email;
    private $phone;
    private $address;
    private $npa;
    private $city;
    private $country;
    private $role;
    
    public function __construct ($userId) {
        
        $sql = "SELECT * FROM Users WHERE userId = '$userId'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
            
        if($nb == 1){
            // User found
            $row = mysql_fetch_assoc($res);
            
            $this->userId = $row["userId"];
            $this->lastname = stripslashes($row["lastname"]);
            $this->firstname = stripslashes($row["firstname"]);
            $this->email = stripslashes($row["email"]);
            $this->phone = stripslashes($row["phone"]);
            $this->address = stripslashes($row["address"]);
            $this->npa = stripslashes($row["npa"]);
            $this->city = stripslashes($row["city"]);
            $this->country = stripslashes($row["country"]);
                            
            $sql = "SELECT * FROM Administrators WHERE userId = '$this->userId'";
            $res = mysql_query ( $sql );                
            $nb = mysql_num_rows($res);
            if($nb == 1){
                // We have an ADMIN
                $this->role = "administrator";
            }elseif($nb == 0){
                $sql = "SELECT * FROM Animators WHERE userId = '$this->userId'";
                $res = mysql_query ( $sql );
                $nb = mysql_num_rows($res);
                if($nb == 1){
                    $this->role = "animator";
                }elseif($nb == 0){
                    $this->role = "player";
                }else{
                    // WTF
                }
            }else{
                // WTF
            }
        }elseif($nb == 0){
            // User does not exists
        }else{
            // WTF
        }
    }
    
    /**
    * Met à jours les données de l'utilisateur en BD, avec les données passées en paramètre.
    * Retourne un tableau associatif "user" (mis à jour) et "msg".
    * $d Tableau associatif dont les clés ont les mêmes noms que les propriétés de l'objet user. 
    */
    public function updateData($d){
        
        $sql = "SHOW COLUMNS FROM Users";
        $res = mysql_query ( $sql );
        $fields = array();
        while ($row = mysql_fetch_assoc($res)) {
            $fields[] = strtolower($row['Field']);
        }
        $sql = "UPDATE Users SET";
        
        $first = true;
        foreach($d as $key => $value){
            if(in_array(strtolower($key), $fields)){
                $validValue = addslashes($value);
                if($first){
                    $first = false;
                }else{
                    $sql .= ",";
                }
                $sql .= " `$key`='$validValue'";
            }
        }
        $sql .= " WHERE `userId` = '".$this->userId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        
        $result['user'] = new User($this->userId);
        
        if($res && ($nb === 1)){
            $result['msg'] = 'Tes données ont été mises à jours.';
        }else{
            if($res && $nb == 0){
                $result['msg'] = "Il semble que tu n'ais modifié aucune donnée.";
            }else{
                $result['msg'] = "Echec. Désolé, visiblement, un alignement imprévu des planètes nous a empêché de mettre à jour tes données. Merci de réessayer plus tard ou de <a href=''>nous contacter</a>.";
            }
            
        }
        
        return $result;
    }
    
    /**
    * Verifie que le mot de passe fourni en paramètre correspond bien à celui de l'utilisateur
    * courant.
    */
    public function verifyPassword($pwd){

        $pwd = sha1($pwd);
        $id = $this->userId;
    
        if($this->role == "animator"){
            $table = "Animators";
        }elseif($this->role == "administrator"){
            $table = "Administrators";
        }else{
            return false;
        }
        
        $sql = "SELECT userId FROM $table WHERE userId = '$id' AND password='$pwd'";
        $res = mysql_query ($sql);
        echo mysql_error();
        $nb = mysql_num_rows($res);
        
        return ($res && ($nb === 1));
    }
    
    /**
    * Réinitialise le mot de passe de l'utilisateur.
    * Retourne le nouveau mot de passe, ou false en cas d'échec.
    */
    public function resetPassword(){
        $pwd = "";
        $chaine = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        srand((double)microtime()*1000000);
        for($i=0; $i<8; $i++) {
            $pwd .= $chaine[rand()%strlen($chaine)];
        }
        $user = new User($this->userId);
        return ($user->updatePassword($pwd)) ? $pwd : false;
    }
    
    /**
    * Met à jour le mot de passe de l'utilisateur. Retourne true si la mise à jour est un succès.
    */
    public function updatePassword($pwd){
    
        if($this->role == "animator"){
            $table = "Animators";
        }elseif($this->role == "administrator"){
            $table = "Administrators";
        }else{
            return false;
        }
    
        $sql = "UPDATE $table SET `password`='".sha1($pwd)."' WHERE `userId` = '".$this->userId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        return ($res && ($nb === 1));
    }
    
    /**
    * Verifie que le mot de passe fourni en paramètre est utilisable selon nos normes de sécurité.
    */
    public static function validatePassword($pwd){
        return (strlen($pwd) > 4);
    }
    
    /**
    * Only used for MJ.
    * Register a new USER with the datas $d and return it.
    * Or return false if error occurs.
    */
    public static function registerMJ($d){
        
        if( isset($d['email']) && isset($d['password']) ){
            if ( Controls::validateEmail($d['email']) && !self::emailExists($d['email']) ){
                
                // Get User's fields
                $sql = "SHOW COLUMNS FROM Users";
                $res = mysql_query ( $sql );
                $fields = array();
                while ($row = mysql_fetch_assoc($res)) {
                    $fields[] = strtolower($row['Field']);
                }

                // Build SQL request
                $sql = "INSERT INTO Users SET";
                $first = true;
                foreach($d as $key => $value){
                    if(in_array(strtolower($key), $fields)){
                        $validValue = addslashes($value);
                        if(!$first)$sql .= ",";
                        $sql .= " `$key`='$validValue'";
                    }
                    $first = false;
                }
                
                // Insert User
                $res = mysql_query ( $sql );
                $nb = mysql_affected_rows();
                
                if($res && ($nb === 1)){
                
                    // Get automatic userId
                    $sql = "SELECT userId FROM Users WHERE email = '".$d['email']."'";
                    $res = mysql_query ( $sql );
                    $nb = mysql_num_rows($res);
                    
                    if($res && ($nb === 1)){
                        $row = mysql_fetch_assoc($res);
                        $userId = $row['userId'];
                        
                        // Insert animator
                        $sql = "INSERT INTO Animators SET `userId`='$userId', `password`='".sha1($d['password'])."'";
                        $res = mysql_query ( $sql );
                        $nb = mysql_affected_rows();
                        if($res && ($nb === 1)){
                        
                            // Validate it by authentification, and return it
                            $auth = self::auth($d['email'], $d['password']);
                            if($auth['status'] == 2){
                                return self::getFromId($auth['userId']);
                            }
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    
    /**
    * @param $user A player to upgrade to MJ
    * @param $d An array of data, containing new password
    */
    public static function upgradeToMJ($user, $d){
        $user = new User($user->getId());
        if ( $user || $user->getRole() == 'player' ){
            // Insert animator
            $sql = "INSERT INTO Animators SET `userId`='".$user->getId()."', `password`='".sha1($d['password'])."'";
            $res = mysql_query ( $sql );
            $nb = mysql_affected_rows();
            if($res && ($nb === 1)){
                // Validate it by authentification, and return it
                $auth = self::auth($d['email'], $d['password']);
                if($auth['status'] == 2){
                    return $user->updateData($d);
                }
            }
        }
        return FALSE;
    }
    
    /**
    * Register a new USER with the datas $d and return it.
    * Or return false if error occurs.
    * Used for players
    */
    public static function register($email, $lastname, $firstname){
        if ( Controls::validateEmail($email) && !self::emailExists($email) ){
            
            // Build SQL request
            $sql = "INSERT INTO Users SET `email`='".strtolower($email)."', `lastname`='".strtolower(addslashes($lastname))."', `firstname`='".strtolower(addslashes($firstname))."'";

            // Insert User
            $res = mysql_query ( $sql );
            $nb = mysql_affected_rows();
            
            if($res && ($nb === 1)){
            
                // Get and return new userId
                $sql = "SELECT userId FROM Users WHERE email = '$email'";
                $res = mysql_query ( $sql );
                $nb = mysql_num_rows($res);
                if($res && ($nb === 1)){
                    $row = mysql_fetch_assoc($res);
                    return new User($row["userId"]);
                }
            }
        }
        return FALSE;
    }
    
    /**
    * Returns the user corresponding to saved userId SESSION.
    * Or false if SESSION is not available.
    */
    public static function getFromSession(){
        // Si activ session
        if (isset($_SESSION) && isset($_SESSION['userId'])) {
        
            // get User From session
            return self::getFromId($_SESSION['userId']);
            
        }
        return FALSE;
    }
    
    /*
    * @DEPRECATED
    * Use new User($userId)
    */
    public static function getFromId($userId){
        
        $sql = "SELECT userId FROM Users WHERE userId = '$userId'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
        
        if($nb == 1){
            $row = mysql_fetch_assoc($res);
            return new User($row["userId"]);
        }
        return FALSE;
    }
    
    /**
    * True si l'email est déjà enregistré en BD, false sinon.
    * Ajouter un deuxieme attribut pour spécifier un ID d'utilisateur, afin de vérifier si l'email
    * est associée à un autre utilisateur.
    */
    public static function emailExists($email, $excludeId = null){
        $sql = "SELECT userId FROM Users WHERE email = '$email'";
        $res = mysql_query ($sql);
        $nb = mysql_num_rows($res);
        $exists = false;
        if(is_null($excludeId)){
            if($nb > 0){
                $exists = true;
            }
        }else{
            while ($row = mysql_fetch_assoc($res)) {
                if($row['userId'] != $excludeId){
                    $exists = true;
                }
            }
        }
        return $exists;
    }
    
    /**
    * Fonction d'authentification et de sauvegarde de l'id en session
    */
    public static function auth ($email, $password) {
    
        $res = array();
        $result['status'] = 0;
        
        if(Controls::validateEmail($email)){
            
            // encode password
            $password = sha1($password);
        
            $sql = "SELECT userId FROM Users WHERE email = '$email'";
            $res = mysql_query ($sql);
            $nb = mysql_num_rows($res);
                    
            if($nb == 1){
                // User found
                $row = mysql_fetch_assoc($res);
                
                $userId = $row["userId"];

                $sql = "SELECT * FROM Administrators WHERE userId = '$userId'";
                $res = mysql_query ( $sql );                
                $nb = mysql_num_rows($res);
                if($nb == 1){
                    // ADMIN password
                    $row = mysql_fetch_assoc($res);
                    if($password == $row['password']){
                        $result['userId'] = $userId;
                        $result['status'] = 2;
                    }
                }elseif($nb == 0){
                    $sql = "SELECT * FROM Animators WHERE userId = '$userId'";
                    $res = mysql_query ( $sql );
                    $nb = mysql_num_rows($res);
                    if($nb == 1){
                        // MJ password
                        $row = mysql_fetch_assoc($res);
                        if($password == $row['password']){
                            $result['userId'] = $userId;
                            $result['status'] = 2;
                        }
                    }
                }
            }
        } else {
            // echo "L'adresse e-mail $email n'est pas valide";
            $result['status'] = 1;
        }
        return $result;
    }
    
    /**
    * Fonction de pseudo authentification, ne génère pas de session.
    * Retourne un objet user avec le rôle joueur.
    * Si une session est active, si l'email n'est pas valide, ou si l'email est absent de la BD,
    * alors retourne false.
    *
    * Si $data est un tableau associatif, alors vérifie que ses données correspondent à
    * l'utilisateur pêché.
    */
    public static function pseudoAuth ($email, $data = null, $force = false) {
        if ($force || !(isset($_SESSION) && isset($_SESSION['userId']))) {
            if(Controls::validateEmail($email)){
                $sql = "SELECT * FROM Users WHERE email = '$email'";
                $res = mysql_query ($sql);
                $nb = mysql_num_rows($res);
                if($nb == 1){
                    $row = mysql_fetch_assoc($res);
                    $user = self::getFromId($row["userId"]);
                    if($user){
                        $user->role = "player";
                        if(!is_null($data)){
                            foreach($data as $k => $v){
                                if(strtolower($user->$k) != strtolower($v)){
                                    return false;
                                }
                            }
                        }
                        return $user;
                    }
                }
            }
        }
        return false;
    }
    
    /*
    * Returns true if "this" user participates to the given party.
    */
    public function participatesTo ($partyId){
        $sql = "SELECT * FROM Inscriptions WHERE partyId = ".$partyId." AND userId = ".$this->userId;
        $res = mysql_query ( $sql );
        $row = mysql_fetch_assoc($res);
        $nb = mysql_num_rows($res);
        return ($res && $nb > 0);
    }
    /*
    * Returns true if "this" user animates the given party.
    */
    public function animates ($partyId){
        if($this->getRole() == 'animator' || $this->getRole() == 'administrator'){
            $sql = "SELECT * FROM Parties WHERE partyId = ".$partyId." AND userId = ".$this->userId;
            $res = mysql_query ( $sql );
            $row = mysql_fetch_assoc($res);
            $nb = mysql_num_rows($res);
            return ($res && $nb > 0);
        }
        return false;
    }
    
    /** Accessors */
    public function getUserId(){
        return $this->userId;
    }
    public function getId(){
        return $this->userId;
    }
    public function getLastname(){
        return $this->lastname;
    }
    public function getFirstname(){
        return $this->firstname;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getPhone(){
        return $this->phone;
    }
    public function getAddress(){
        return $this->address;
    }
    public function getNpa(){
        return $this->npa;
    }
    public function getCity(){
        return $this->city;
    }
    public function getCountry(){
        return $this->country;
    }
    public function getRole(){
        return $this->role;
    }
    
        
}