<?php

class User {

    const ROLE_PJ = "player";
    const ROLE_MJ = "animator";
    const ROLE_ADMIN = "administrator";

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
        $this->mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        $sql = "SELECT * FROM Users WHERE userId = '$userId'";
        $res = $this->mysqli->query($sql);

        if($res->num_rows == 1){
            // User found
            $row = $res->fetch_assoc();

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
            $res = $this->mysqli->query($sql);
            $nb = $res->num_rows;
            if($nb == 1){
                // We have an ADMIN
                $this->role = "administrator";
            }elseif($nb == 0){
                $sql = "SELECT * FROM Animators WHERE userId = '$this->userId'";
                $res = $this->mysqli->query($sql);
                if($res->num_rows == 1){
                    $this->role = "animator";
                }elseif($res->num_rows == 0){
                    $this->role = "player";
                }else{
                    // WTF
                }
            }else{
                // WTF
            }
        }elseif($res->num_rows == 0){
            // User does not exists
        }else{
            // WTF
        }
    }

    public static function getUsers($role = "all", $sort = null){

        if($role == 'player'){
            $sql = "SELECT u.* FROM Users u WHERE u.userId not in (select a.userId FROM Animators a)";
        }elseif($role == 'animator'){
            $sql = 'SELECT * FROM Users u INNER JOIN Animators a ON u.userId = a.userId';
        }elseif($role == 'administrator'){
            $sql = 'SELECT * FROM Users u INNER JOIN Administrators a ON u.userId=a.userId';
        }else{
            $sql = "SELECT * FROM Users";
        }

        if(!is_null($sort) && ""!=$sort){
            $sql .= " ORDER BY $sort";
        }
        $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        $res = $mysqli->query($sql);
        $mysqli->close();

        return $res;
    }

    /**
     * @param string $role
     * @param null $year
     * @param null $sort
     * @return bool|mysqli_result|resource
     */
    public static function getUsersByYear($role = 'all', $year=null, $sort = null){
        if( ! is_numeric($year)){
            return self::getUsers($role, $sort);
        }

        $sql = 'SELECT DISTINCT u.* FROM Users u ';

        if($role == 'player'){
            $sql .= 'WHERE u.userId not in (select a.userId FROM Animators a)';
        }elseif($role == 'animator'){
            $sql .= 'INNER JOIN Animators a ON u.userId = a.userId';
        }elseif($role == 'administrator'){
            $sql .= 'INNER JOIN Administrators a ON u.userId=a.userId';
        }

        $sql .= " JOIN Parties ON u.userId = Parties.UserId".
            " WHERE Parties.year = ".$year;

        if(!is_null($sort) && ""!=$sort){
            $sql .= " ORDER BY u.$sort";
        }

        $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        $res = $mysqli->query($sql);
        $mysqli->close();

        return $res;
    }

    /**
    * Met à jours les données de l'utilisateur en BD, avec les données passées en paramètre.
    * Retourne un tableau associatif "user" (mis à jour) et "msg".
    * $d Tableau associatif dont les clés ont les mêmes noms que les propriétés de l'objet user.
    */
    public function updateData($d){

        $sql = "SHOW COLUMNS FROM Users";
        $res = $this->mysqli->query($sql);
        $fields = array();
        while ($row = $res->fetch_assoc()) {
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
        $res = $this->mysqli->query($sql);

        $result['user'] = new User($this->userId);

        if($res){
            $result['msg'] = 'Tes données ont été mises à jour.';
        }else{
            $result['msg'] = "Echec. Désolé, visiblement, un alignement imprévu des planètes nous a empêché de mettre à jour tes données. Merci de réessayer plus tard ou de <a href=''>nous contacter</a>.";
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
        $res = $this->mysqli->query($sql);

        return ($res && ($res->num_rows === 1));
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
        $res = $this->mysqli->query($sql);
        return ($res && ($res->num_rows === 1));
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
                $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
                if ($mysqli->connect_error) {
                    die("Connection failed: " . $mysqli->connect_error);
                }

                // Get User's fields
                $sql = "SHOW COLUMNS FROM Users";
                $res = $mysqli->query($sql);
                $fields = array();
                while ($row = $res->fetch_assoc()) {
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
                if($mysqli->query($sql)){

                    // Get automatic userId
                    $sql = "SELECT userId FROM Users WHERE email = '".$d['email']."'";
                    $res = $mysqli->query($sql);

                    if($res && ($res->num_rows === 1)){
                        $row = $res->fetch_assoc();
                        $userId = $row['userId'];

                        // Insert animator
                        $sql = "INSERT INTO Animators SET `userId`='$userId', `password`='".sha1($d['password'])."'";
                        if($mysqli->query($sql)){

                            // Validate it by authentification, and return it
                            $auth = self::auth($d['email'], $d['password']);
                            if($auth['status'] == 2){
                                $mysqli->close();
                                return new User($auth['userId']);
                            }
                        }
                    }
                }
            }
        }
        $mysqli->close();
        return FALSE;
    }

    /**
     * @param $user User A player to upgrade to MJ
     * @param $d array data, containing new password
     * @return bool|mixed
     */
    public static function upgradeToMJ($user, $d){
        $user = new User($user->getId());
        if ( $user || $user->getRole() == 'player' ){
            // Insert animator
            $sql = "INSERT INTO Animators SET `userId`='".$user->getId()."', `password`='".sha1($d['password'])."'";
            $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }
            $res = $mysqli->query($sql);
            if($res){
                // Validate it by authentification, and return it
                $auth = self::auth($d['email'], $d['password']);
                if($auth['status'] == 2){
                    $mysqli->close();
                    return $user->updateData($d);
                }
            }
            $mysqli->close();
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
            $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Build SQL request
            $sql = "INSERT INTO Users SET `email`='".strtolower($email)."', `lastname`='".strtolower(addslashes($lastname))."', `firstname`='".strtolower(addslashes($firstname))."'";

            // Insert User
            $res = $mysqli->query($sql);

            if($res){

                // Get and return new userId
                $sql = "SELECT userId FROM Users WHERE email = '$email'";
                $res = $mysqli->query($sql);
                if($res && ($res->num_rows === 1)){
                    $row = $res->fetch_assoc();
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
            return new User($_SESSION['userId']);
        }
        return FALSE;
    }

    /**
     * True si l'email est déjà enregistré en BD, false sinon.
     * Ajouter un deuxieme attribut pour spécifier un ID d'utilisateur, afin de vérifier si l'email
     * est associée à un autre utilisateur.
     *
     * @param $email
     * @param null $excludeId
     * @return bool
     */
    public static function emailExists($email, $excludeId = null){
        $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        $sql = "SELECT userId FROM Users WHERE email = '$email'";
        $res = $mysqli->query($sql);
        $exists = false;
        if(is_null($excludeId)){
            if($res->num_rows > 0){
                $exists = true;
            }
        }else{
            while ($row = $res->fetch_assoc()) {
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

        $result = array ('status' => 0);

        if(Controls::validateEmail($email)){

            // encode password
            $password = sha1($password);

            $sql = "SELECT userId FROM Users WHERE email = '$email'";

            $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            $res = $mysqli->query($sql);

            if($res->num_rows == 1){
                // User found
                $row = $res->fetch_assoc();

                $userId = $row["userId"];

                $sql = "SELECT * FROM Administrators WHERE userId = '$userId'";
                $res = $mysqli->query($sql);
                $nb = $res->num_rows;
                if($nb == 1){
                    // ADMIN password
                    $row = $res->fetch_assoc();
                    if($password == $row['password']){
                        $result['userId'] = $userId;
                        $result['status'] = 2;
                    }
                }elseif($nb == 0){
                    $sql = "SELECT * FROM Animators WHERE userId = '$userId'";
                    $res = $mysqli->query($sql);
                    if($res->num_rows == 1){
                        // MJ password
                        $row = $res->fetch_assoc();
                        if($password == $row['password']){
                            $result['userId'] = $userId;
                            $result['status'] = 2;
                        }
                    }
                }
            }
            $mysqli->close();
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
                $mysqli = new mysqli(HOST, USER, PASSWORD, DB);
                if ($mysqli->connect_error) {
                    die("Connection failed: " . $mysqli->connect_error);
                }
                $sql = "SELECT * FROM Users WHERE email = '$email'";
                $res = $mysqli->query($sql);
                if($res->num_rows == 1){
                    $row = $res->fetch_assoc();
                    $user = new User($row["userId"]);
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
        $res = $this->mysqli->query($sql);
        return ($res && $res->num_rows > 0);
    }
    /*
    * Returns true if "this" user animates the given party.
    */
    public function animates ($partyId){
        if($this->getRole() == 'animator' || $this->getRole() == 'administrator'){
            $sql = "SELECT * FROM Parties WHERE partyId = ".$partyId." AND userId = ".$this->userId;
            $res = $this->mysqli->query($sql);
            return ($res && $res->num_rows > 0);
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
    public function isAdmin(){
        return ($this->role == 'administrator');
    }
}
