<?php

/**
* Cette classe contient une série de méthode static qui servent à controler l'état de l'application,
* ainsi que quelques méthodes transversales utiles au fonctionnement de l'application.
*/
class Controls {
    const CONV_START = "convStart";
    const CONV_END = "convEnd";
    const APP_OPEN = "appOpenDate";
    const APP_CLOSE = "appCloseDate";
    const MJ_OPEN = "mjOpenDate";
    const MJ_CLOSE = "mjCloseDate";
    const PLAYER_OPEN = "playerOpenDate";
    const PLAYER_CLOSE = "playerCloseDate";
    const NB_TABLES = "numberOfTables";

    protected $mysqli;

    function __construct() {
        $this->mysqli = new mysqli(HOST, USER, PASSWORD, DB);
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getNbTables(){
        $sql = "SELECT * FROM Controls WHERE `key` = '".self::NB_TABLES."'";
        $result = $this->mysqli->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            return intval($result->fetch_assoc()['value']);
        } else {
            return 0;
        }
    }

    public function setNbTables($nbTables){
        return $this->setProperty(self::NB_TABLES, $nbTables);
    }

    /**
    * Retourne TRUE si l'application est ouverte, FALSE sinon.
    * Lorsque l'application est ouverte, tous les services sont disponibles,
    * à moins qu'un contrôle dise le contraire.
    * Cette fonction sert par exemple à fermer tous les services d'un coup si nécessaire.
    */
    public function isAppOpen(){
        $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'appOpenDate'");
        if($this->mysqli->affected_rows == 1){
            $row = mysqli_fetch_assoc($res);
            // exemple: 2012/01/01 00:00
            $openStamp = strtotime($row['value']);

            $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'appCloseDate'");
            if($this->mysqli->affected_rows == 1){
                $row = mysqli_fetch_assoc($res);
                // exemple: 2012/01/01 00:00
                $closeStamp = strtotime($row['value']);
                $now = time();

                if($openStamp <= $now && $closeStamp > $now) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Retourne TRUE si l'inscription de partie par les MJ est ouverte, FALSE sinon.
    * Cette fonction retournera toujours FALSE si la fonction isAppOpen() retourne FALSE.
    */
    public function isMjOpen(){
        if($this->isAppOpen()){
            $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'mjOpenDate'");
            if($this->mysqli->affected_rows == 1){
                $row = mysqli_fetch_assoc($res);
                // exemple: 2012/01/01 00:00
                $openStamp = strtotime($row['value']);
                $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'mjCloseDate'");
                if($this->mysqli->affected_rows == 1){
                    $row = mysqli_fetch_assoc($res);
                    // exemple: 2012/01/01 00:00
                    $closeStamp = strtotime($row['value']);
                    $now = time();
                    if($openStamp <= $now && $closeStamp > $now) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
    * Retourne TRUE si l'inscription aux parties est ouverte, FALSE sinon.
    * Cette fonction retournera toujours FALSE si la fonction isAppOpen() retourne FALSE.
    */
    public function isPlayerOpen(){
        if($this->isAppOpen()){
            $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'playerOpenDate'");
            if($this->mysqli->affected_rows == 1){
                $row = mysqli_fetch_assoc($res);
                // exemple: 2012/01/01 00:00
                $openStamp = strtotime($row['value']);
                $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = 'playerCloseDate'");
                if($this->mysqli->affected_rows == 1){
                    $row = mysqli_fetch_assoc($res);
                    // exemple: 2012/01/01 00:00
                    $closeStamp = strtotime($row['value']);
                    $now = time();
                    if($openStamp <= $now && $closeStamp > $now) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    /**
     * Retourne le timestamp ou la date formattée selon $pattern, d'une date identifiée par $dateIdentifier.
     *
     * @param string $dateIdentifier L'identifiant de la date. Devrait se baser sur une constant de Controls.
     * @param null $pattern Le pattern de formattage pour retourner la date en String.
     * @return bool|int|string
     */
    public function getDate($dateIdentifier, $pattern = null){
        return $this->getDatesOfKey($dateIdentifier, $pattern);
    }

    /**
     * @param $dateIdentifier
     * @param $stamp
     * @return bool
     */
    public function setDate($dateIdentifier, $stamp){
        return $this->setDatesOfKey($stamp, $dateIdentifier);
    }

    /**
    * Defini en BD la date (timestamp) de la prochaine convention. Retourne true réussi.
    */
    public function setConvDate($stamp){
        return $this->setDatesOfKey($stamp, "convDate");
    }
    /**
    * Defini en BD la date (timestamp) d'ouverture de l'application. Retourne true réussi.
    */
    public function setAppOpenDate($stamp){
        return $this->setDatesOfKey($stamp, "appOpenDate");
    }
    /**
    * Defini en BD la date (timestamp) de fermeture de l'application. Retourne true réussi.
    */
    public function setAppCloseDate($stamp){
        return $this->setDatesOfKey($stamp, "appCloseDate");
    }
    /**
    * Defini en BD la date d'ouverture des services animateurs (MJ). Retourne true réussi.
    */
    public function setMjOpenDate($stamp){
        return $this->setDatesOfKey($stamp, "mjOpenDate");
    }
    /**
    * Defini en BD la date de fermeture des services animateurs (MJ). Retourne true réussi.
    */
    public function setMjCloseDate($stamp){
        return $this->setDatesOfKey($stamp, "mjCloseDate");
    }
    /**
    * Defini en BD la date d'ouverture des services joueurs. Retourne true réussi.
    */
    public function setPlayerOpenDate($stamp){
        return $this->setDatesOfKey($stamp, "playerOpenDate");
    }
    /**
    * Defini en BD la date de fermeture des services joueurs. Retourne true réussi.
    */
    public function setPlayerCloseDate($stamp){
        return $this->setDatesOfKey($stamp, "playerCloseDate");
    }

    /**
     * Retourne la date (timestamp) d'une propriété passée en parametre (par defaut: appOpenDate).
     * Retourne true si la nouvelle date a pu etre enregistree en BD.
     * @param $key
     * @param null $pattern
     * @return bool|false|int|string
     */
    protected function getDatesOfKey($key, $pattern = null) {
        $key = (is_null($key)) ? "appOpenDate" : $key;
        $res = $this->mysqli->query("SELECT * FROM Controls WHERE `key` = '$key'");
        if($this->mysqli->affected_rows == 1){
            $row = mysqli_fetch_assoc($res);
            // exemple: 2012/01/01 00:00
            if(is_null($pattern)){
                return strtotime($row['value']);
            }else{
                return strftime($pattern, strtotime($row['value']));
            }
        }
        return false;
    }

    /**
    * Defini la date (timestamp) d'une propriété passée en parametre (par defaut: appOpenDate).
    * Retourne true si la nouvelle date a pu etre enregistree en BD.
    */
    protected function setDatesOfKey($stamp, $key = NULL) {
        $key = (is_null($key)) ? "appOpenDate" : $key;
        return $this->setProperty($key, $stamp);
    }

    /**
     * Defini la date (timestamp) d'une propriété passée en parametre (par defaut: appOpenDate).
     * Retourne true si la nouvelle date a pu etre enregistree en BD.
     */
    protected function setProperty($key, $val) {
        $res = $this->mysqli->query(
            "INSERT INTO Controls (`key`,`value`) VALUES('$key','$val') ON DUPLICATE KEY UPDATE `value`='$val'"
        );
        return ($res)?true:false;
    }

    /**
    * Retourne l'URL courrante sous forme de String
    */
    public static function currentURI() {
        $pageURL = 'http';
        if(isset($_SERVER["AUTH_TYPE"]) && $_SERVER["AUTH_TYPE"] !== "Basic"){
            if ($_SERVER["HTTPS"] == "on") {
                $pageURL .= "s";
            }
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
    * Retourne l'URL de la home de l'application sous forme de String
    */
    public static function home() {
        $pageURL = 'http';
        /* // https
        if($_SERVER["AUTH_TYPE"] !== "Basic"){
            if ($_SERVER["HTTPS"] == "on") {
                $pageURL .= "s";
            }
        }*/
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"]."/".MODULE_PATH;
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"]."/".MODULE_PATH;
        }
        return $pageURL;
    }

    /**
    * Retourne TRUE si l'email est valide selon RFC 2822 et 1035, ou FALSE sinon.
    */
    public static function validateEmail($email){
        $atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';
        $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)';
        $regex = '/^'.$atom.'+'.'(\.'.$atom.'+)*'.'@'.'('.$domain.'{1,63}\.)+'.$domain.'{2,63}$/i';
        return (preg_match($regex, $email));
    }

	/**
	* Supprime les éléments html et php d'une chaine, et ajoute des antishlashes
	* Laisse les balises de mise en forme <i><b><u><br><ul><ol><li>, et converti les <br>
	*/
	public static function cleanInputString($string) {
		$string = str_replace(array("<br>","<br />"),"\r\n",$string);
		$string = strip_tags($string, '<i><b><u><br><ul><ol><li>');
		$string = addslashes($string);

		return $string;
	}

}
