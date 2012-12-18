<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');

/**
* Cette classe contient une série de méthode static qui servent à controler l'état de l'application,
* ainsi que quelques méthodes transversales utiles au fonctionnement de l'application.
*/
class Controls {
    
    /**
    * Retourne TRUE si l'application est ouverte, FALSE sinon.
    * Lorsque l'application est ouverte, tous les services sont disponibles,
    * à moins qu'un contrôle dise le contraire.
    * Cette fonction sert par exemple à fermer tous les services d'un coup si nécessaire.
    */
    public static function isAppOpen(){
        $sql = "SELECT * FROM Controls WHERE `key` = 'appOpenDate'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
        if($nb == 1){
            $row = mysql_fetch_assoc($res);
            // exemple: 2012/01/01 00:00
            $openStamp = strtotime($row['value']);
            
            $sql = "SELECT * FROM Controls WHERE `key` = 'appCloseDate'";
            $res = mysql_query ( $sql );
            $nb = mysql_num_rows($res);
            if($nb == 1){
                $row = mysql_fetch_assoc($res);
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
    public static function isMjOpen(){
        if(self::isAppOpen()){
            $sql = "SELECT * FROM Controls WHERE `key` = 'mjOpenDate'";
            $res = mysql_query ( $sql );
            $nb = mysql_num_rows($res);
            if($nb == 1){
                $row = mysql_fetch_assoc($res);
                // exemple: 2012/01/01 00:00
                $openStamp = strtotime($row['value']);
                $sql = "SELECT * FROM Controls WHERE `key` = 'mjCloseDate'";
                $res = mysql_query ( $sql );
                $nb = mysql_num_rows($res);
                if($nb == 1){
                    $row = mysql_fetch_assoc($res);
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
    public static function isPlayerOpen(){
        if(self::isAppOpen()){
            $sql = "SELECT * FROM Controls WHERE `key` = 'playerOpenDate'";
            $res = mysql_query ( $sql );
            $nb = mysql_num_rows($res);
            if($nb == 1){
                $row = mysql_fetch_assoc($res);
                // exemple: 2012/01/01 00:00
                $openStamp = strtotime($row['value']);
                $sql = "SELECT * FROM Controls WHERE `key` = 'playerCloseDate'";
                $res = mysql_query ( $sql );
                $nb = mysql_num_rows($res);
                if($nb == 1){
                    $row = mysql_fetch_assoc($res);
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
    * Retourne la date (timestamp) de la prochaine convention.
    */
    public static function getConvDate($pattern = null){
        return self::getDatesOfKey($pattern, "convDate");
    }
    /**
    * Retourne la date (timestamp) d'ouverture de l'application, ou False si inconnue.
    */
    public static function getAppOpenDate($pattern = null){
        return self::getDatesOfKey($pattern, "appOpenDate");
    }
    /**
    * Retourne la date (timestamp) de fermeture de l'application, ou False si inconnue.
    */
    public static function getAppCloseDate($pattern = null){
        return self::getDatesOfKey($pattern, "appCloseDate");
    }
    /**
    * Retourne la date (timestamp) d'ouverture des services MJ, ou False si inconnue.
    */
    public static function getMjOpenDate($pattern = null){
        return self::getDatesOfKey($pattern, "mjOpenDate");
    }
    /**
    * Retourne la date (timestamp) de fermeture des services MJ, ou False si inconnue.
    */
    public static function getMjCloseDate($pattern = null){
        return self::getDatesOfKey($pattern, "mjCloseDate");
    }
    /**
    * Retourne la date (timestamp) d'ouverture des services Joueur, ou False si inconnue.
    */
    public static function getPlayerOpenDate($pattern = null){
        return self::getDatesOfKey($pattern, "playerOpenDate");
    }
    /**
    * Retourne la date (timestamp) de fermeture des services Joueur, ou False si inconnue.
    */
    public static function getPlayerCloseDate($pattern = null){
        return self::getDatesOfKey($pattern, "playerCloseDate");
    }
    
    /**
    * Defini en BD la date (timestamp) de la prochaine convention. Retourne true réussi.
    */
    public static function setConvDate($stamp){
        return self::setDatesOfKey($stamp, "convDate");
    }
    /**
    * Defini en BD la date (timestamp) d'ouverture de l'application. Retourne true réussi.
    */
    public static function setAppOpenDate($stamp){
        return self::setDatesOfKey($stamp, "appOpenDate");
    }
    /**
    * Defini en BD la date (timestamp) de fermeture de l'application. Retourne true réussi.
    */
    public static function setAppCloseDate($stamp){
        return self::setDatesOfKey($stamp, "appCloseDate");
    }
    /**
    * Defini en BD la date d'ouverture des services animateurs (MJ). Retourne true réussi.
    */
    public static function setMjOpenDate($stamp){
        return self::setDatesOfKey($stamp, "mjOpenDate");
    }
    /**
    * Defini en BD la date de fermeture des services animateurs (MJ). Retourne true réussi.
    */
    public static function setMjCloseDate($stamp){
        return self::setDatesOfKey($stamp, "mjCloseDate");
    }
    /**
    * Defini en BD la date d'ouverture des services joueurs. Retourne true réussi.
    */
    public static function setPlayerOpenDate($stamp){
        return self::setDatesOfKey($stamp, "playerOpenDate");
    }
    /**
    * Defini en BD la date de fermeture des services joueurs. Retourne true réussi.
    */
    public static function setPlayerCloseDate($stamp){
        return self::setDatesOfKey($stamp, "playerCloseDate");
    }
    
    
    /**
    * Retourne la date (timestamp) d'une propriété passée en parametre (par defaut: appOpenDate).
    * Retourne true si la nouvelle date a pu etre enregistree en BD.
    */
    private static function getDatesOfKey($pattern = null, $key) {
        $key = (is_null($key)) ? "appOpenDate" : $key;
        $sql = "SELECT * FROM Controls WHERE `key` = '$key'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
        if($nb == 1){
            $row = mysql_fetch_assoc($res);
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
    private static function setDatesOfKey($stamp, $key = NULL) {
        $key = (is_null($key)) ? "appOpenDate" : $key;
        $sql = "SELECT * FROM Controls WHERE `key` = '$key'";
        $res = mysql_query ( $sql );
        $nb = mysql_num_rows($res);
        $res = false;
        if ($nb == 0){
            $sql = "INSERT INTO Controls (`key`,`value`) VALUES ('$key','$stamp')";
            $res = mysql_query ( $sql );
        }elseif($nb == 1){
            $sql = "UPDATE Controls SET value = '$stamp' WHERE `key` = '$key'";
            $res = mysql_query ( $sql );
        }
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
            $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"]."/".MODULE_PATH."/";
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"]."/orcidee/".MODULE_PATH."/";
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
?>