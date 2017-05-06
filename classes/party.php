<?php
require_once(dirname(__FILE__).'/user.php');
require_once(dirname(__FILE__).'/orcimail.php');
require_once(dirname(__FILE__).'/inscription.php');

class Party {

    private $partyId;
    private $userId;
    private $typeId;
    private $name;
    private $kind;
    private $scenario;
    private $playerMin;
    private $playerMax;
    private $level;
    private $duration;
    private $start;
    private $description;
    private $note;
    private $language;
    private $year;
    private $state;
    
    public $isValid; // true: Données valides, prêtes pour creneau et sauvegarde
    public $isFake; // true: Pas encore sauvé en BD
    public $errors;
    public $infos;
    
    /**
    * Create a new party (without saving it in DB).
    * If parameter "new" is unset or true, will create a new fake party,
    * meaning that data comes from user interface.
    * Parameter "new" as false means that data comes from DB.
    */
    public function __construct ($arg, $new = true) {
        
        $this->isValid = true;
        $this->errors = array();

        // validate data
        if($new && is_array($arg)){
            $data = $arg;
            if( isset($data['userId']) && strlen($data['userId']) > 0 &&
                isset($data['typeId']) && strlen($data['typeId']) > 0 &&
                isset($data['name']) && strlen($data['name']) > 0 &&
                isset($data['playerMin']) && strlen($data['playerMin']) > 0 &&
                isset($data['playerMax']) && strlen($data['playerMax']) > 0 &&
                is_numeric($data['playerMin']) && is_numeric($data['playerMax']) &&
                0 < $data['playerMin'] && $data['playerMin'] <= $data['playerMax'] &&
                isset($data['level']) && strlen($data['level']) > 0 &&
                isset($data['start']) && strlen($data['start']) > 0 &&
                isset($data['duration']) && strlen($data['duration']) > 0 &&
                isset($data['tableAmount']) && in_array($data['tableAmount'], array(0,1,2,3))
            ) {
                
                $this->userId = $data['userId'];
                $this->typeId = $data['typeId'];
                $this->name = $data['name'];
                $this->kind = $data['kind'];
                $this->scenario = $data['scenario'];
                $this->playerMin = $data['playerMin'];
                $this->playerMax = $data['playerMax'];
                $this->level = $data['level'];
                $this->duration = $data['duration'];
                $this->start = $data['start'];
                $this->description = $data['description'];
                $this->note = $data['note'];
                $this->language = $data['language'];
                $this->year = Controls::getDate(Controls::CONV_START, '%Y');
                $this->state = "created";
                $this->isFake = true;
                $this->tableAmount = $data['tableAmount'];
                
                if(isset($data['partyId']) && @$data['action'] == 'edit'){
                    // Trying to update existing
                    $this->partyId = $data['partyId'];
                }
                
            }else{
                $this->isValid = false;
            }
        }elseif( !$new && is_numeric($arg) ){
            $partyId = $arg;
            $sql = "SELECT * FROM Parties WHERE partyId='$partyId'";
            $res = mysql_query ( $sql );
            $nb = mysql_num_rows($res);
			if ($nb == 1){
				$row = mysql_fetch_assoc($res);
				
				foreach($row as $key => $value){
					$this->$key = $value;
				}
				$this->isFake = false;
			}else{
				$this->isValid = false;
			}
        }else{
            $this->isValid = false;
        }
        
    }
    
    public function getPlayers(){
        $players = array();
        $sql = "SELECT * FROM Inscriptions WHERE partyId = '$this->partyId'";
        $res = mysql_query ( $sql );
        while ($row = mysql_fetch_assoc($res)) {
            $players[] = new User($row['userId']);
        }
        return $players;
    }
    
    public static function getYears(){
        $years = array();
        $sql = "SELECT DISTINCT year FROM Parties order by year DESC";
        $res = mysql_query ( $sql );
        while ($row = mysql_fetch_assoc($res)) {
            $years[] = $row['year'];
        }
        return $years;
    }
    
    /**
    * Save "this" party in DB
    */
    public function save (){
        
        if($this->isFake && $this->isValid && ($this->state == "created" || $this->state == "validated")){
        
        
            // validate userId
            $sql = "SELECT * FROM Animators WHERE userId = '$this->userId'";
            $res = mysql_query ($sql);
            $nb = mysql_num_rows($res);
            
            if($nb === 0){
                $sql = "SELECT * FROM Administrators WHERE userId = '$this->userId'";
                $res = mysql_query ($sql);
                $nb = mysql_num_rows($res);
            }
            
            if($nb === 1){
                // user ok

                // UPDATE PARTY
                if(isset($this->partyId) && strlen($this->partyId) > 0){
                
                    if($this->state == 'created'){
                
                        $sql = "UPDATE Parties SET ".
                        "`typeId`='".addslashes($this->typeId)."',".
                        "`name`='".addslashes($this->name)."',".
                        "`kind`='".addslashes($this->kind)."',".
                        "`scenario`='".addslashes($this->scenario)."',".
                        "`playerMin`='".addslashes($this->playerMin)."',".
                        "`playerMax`='".addslashes($this->playerMax)."',".
                        "`level`='".addslashes($this->level)."',".
                        "`duration`='".addslashes($this->duration)."',".
                        "`start`='".addslashes($this->start)."',".
                        "`description`='".addslashes($this->description)."',".
                        "`note`='".addslashes($this->note)."',".
                        "`language`='".addslashes($this->language)."',".
                        "`state`='".addslashes($this->state)."',".
                        "`tableAmount`='".addslashes($this->tableAmount)."'".
                        " WHERE `partyId` = '".$this->partyId."'";

                    } elseif($this->state == 'validated') {
                        $sql = "UPDATE Parties SET ".
                        "`description`='".addslashes($this->description)."',".
                        "`note`='".addslashes($this->note)."'".
                        " WHERE `partyId` = '".$this->partyId."'";
                    }
                    $res = mysql_query ($sql);
                
                    if($res && "" === mysql_error()){
                        $this->isFake = false;
                        return true;
                    }
                
                // INSERT PARTY
                }else{
                    $sql =  "INSERT INTO Parties (`userId`,`typeId`,`name`,`kind`,`scenario`,".
                    "`playerMin`,`playerMax`,`level`,`duration`,`start`,`description`,`note`,".
                    "`language`,`year`,`state`,`tableAmount`)".
                    "VALUES ('".addslashes($this->userId)."','".
                    addslashes($this->typeId)."','".
                    addslashes($this->name)."','".
                    addslashes($this->kind)."','".
                    addslashes($this->scenario)."','".
                    addslashes($this->playerMin)."','".
                    addslashes($this->playerMax)."','".
                    addslashes($this->level)."','".
                    addslashes($this->duration)."','".
                    addslashes($this->start)."','".
                    addslashes($this->description)."','".
                    addslashes($this->note)."','".
                    addslashes($this->language)."','".
                    addslashes($this->year)."','".
                    addslashes($this->state)."','".
                    addslashes($this->tableAmount)."')";
                    
                    $res = mysql_query ($sql);
                
                    if($res && "" === mysql_error()){
                        $this->isFake = false;
                        
                        $sql = "SELECT LAST_INSERT_ID() FROM Parties";
                        $res = mysql_query($sql);
                        
                        if($res && "" === mysql_error()){
                            $row = mysql_fetch_array( $res );
                            $this->partyId = $row[0];
                            return true;
                        }
                    }
                }
            }
        }
        echo "<p class='dbg'>".mysql_error()."</p>";
        return false;
    }
    
	// met à jour la table pour une partie
	public static function setTableForParty($partyId, $table){
		if (isset($partyId) && is_numeric($partyId) && isset($table) && strlen($table) > 0){
            $sql = "UPDATE Parties SET ".
				"`table`='".addslashes($table)."'".
				" WHERE `partyId` = '".$partyId."'";
			$res = mysql_query ($sql);
		
			if($res && "" === mysql_error()){
				return true;
			}
		}
		return false;
	}
	
    public static function getTypes($typeId = null){
        if(!is_null($typeId)){
            $sql = "SELECT * FROM Types WHERE typeId='$typeId'";
            $res = mysql_query ( $sql );
            $row = mysql_fetch_assoc($res);
            $type = array("name" => $row['name'], "description" => $row['description'], "typeId" => $row['typeId']);
            return $type;
        }else{
            $types = array();
            $sql = "SELECT * FROM Types";
            $res = mysql_query ( $sql );
            while ($row = mysql_fetch_assoc($res)) {
                $types[$row['typeId']] = array("name" => $row['name'], "description" => $row['description']);
            }
            return $types;
        }
    }
    
    public function cancel(){
        $this->state = "canceled";
        $sql = "UPDATE Parties SET  `state`='canceled' WHERE `partyId` = '".$this->partyId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        
        if($res && ($nb === 1)){
			// unsubscribe all players and notify them !
			$players = $this->getPlayers();
			foreach($players as $player){
				Inscription::unsubscribe($this->getId(), $player->getId());
				Orcimail::unsubscribedToCanceledParty($this, $player);
			}
            // TODO Insert history line
            // Send a mail
            Orcimail::notifyCancel($this);
            return true;
        }
        return false;
    }
    
    public function refuse(){
        $this->state = "refused";
        $sql = "UPDATE Parties SET  `state`='refused' WHERE `partyId` = '".$this->partyId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        
        if($res && ($nb === 1)){
            // TODO Insert history line
            // Send a mail
            Orcimail::notifyRefuse($this);
            return true;
        }
        return false;
    }
    
    public function validate(){
        $this->state = "validated";
        $sql = "UPDATE Parties SET  `state`='validated' WHERE `partyId` = '".$this->partyId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        
        if($res && ($nb === 1)){
            // TODO Insert history line
            // Send a mail
            Orcimail::notifyValidate($this);
            return true;
        }
        return false;
    }
    public function verify(){
        $this->state = "verified";
        $sql = "UPDATE Parties SET  `state`='verified' WHERE `partyId` = '".$this->partyId."'";
        $res = mysql_query ( $sql );
        $nb = mysql_affected_rows();
        
        if($res && ($nb === 1)){
            // TODO Insert history line
            // Send a mail
            Orcimail::notifyVerify($this);
            return true;
        }
        return false;
    }

    
    public function toArray(){
        
        $res = array(
            "partyId"     => $this->partyId,
            "userId"      => $this->userId,
            "typeId"      => $this->typeId,
            "name"        => $this->name,
            "kind"        => $this->kind,
            "scenario"    => $this->scenario,
            "playerMin"   => $this->playerMin,
            "playerMax"   => $this->playerMax,
            "level"       => $this->level,
            "duration"    => $this->duration,
            "start"       => $this->start,
            "startDay"    => $this->getStartDay(),
            "description" => $this->description,
            "note"        => $this->note,
            "language"    => $this->language,
            "year"        => $this->year,
            "state"       => $this->state,
            "isValid"     => $this->isValid,
            "errors"      => $this->errors,
            "tableAmount" => $this->tableAmount,
        );
        
        return $res;
    }
    
    /**
    * Retourne un tableau associatif à deux dimensions, représentatif de la charge actuelle.
    * Ajoute la partie (start, duration) au tableau, si ces paramètres sont fournis.
    */
    public static function getCurrentSlots($wishStart = null, $wishDuration = null, $existingId = null, $tableAmount = 1)
    {

        $result = array();
        $result["status"] = "ok";

        $start = Controls::getDate(Controls::CONV_START);
        $end = Controls::getDate(Controls::CONV_END);
        $nb = ($end - $start) / 60 / 30;

        // Slots[partyId's]
        $slots = array();
        for ($i = 0; $i < $nb; $i++) {
            $slots[] = array();
        }

        // Initialisation des slots
        $year = Controls::getDate(Controls::CONV_START, "%Y");
        $sql = "SELECT * FROM Parties WHERE state not in ('canceled','refused') AND YEAR(`start`) = $year";
        $res = mysql_query($sql);

        if ($res) {
            while ($row = mysql_fetch_assoc($res)) {
                $start = self::dateToSlot($row['start']);
                $duration = $row['duration'];
                $partyId = $row['partyId'];

                for ($i = $start; $i < ($duration + $start); $i++) {
                    for ($j = 0; $j < intval($row['tableAmount']); $j++) {
                        $slots[$i][$j] = $partyId;
                    }
                }
            }
        }

        if (!is_null($wishStart) && !is_null($wishDuration)) {
            $wishStart = self::dateToSlot($wishStart);
            // Convert half-hours to hours
            $wishDuration = 2 * $wishDuration;
            if (($wishStart + $wishDuration) > $nb) {
                $result["message"] = "Selon ces données, tu prévois de terminer après la fin de la convention, cela ne nous convient pas.";
                $result["status"] = "ko";
            } else {

                // Efface toutes les charges que représente existingId
                if (!is_null($existingId) && $existingId != "") {
                    foreach ($slots as $i => $slot) {
                        // FIXME: Maybe use array_filter($data, $callback) function, if possible.
                        foreach ($slots[$i] as $j => $pId) {
                            if ($pId == $existingId) {
                                unset ($pId);
                            }
                        }
                    }
                }
                // Ajoute une charge aux slots souhaités
                for ($i = $wishStart; $i < ($wishDuration + $wishStart); $i++) {
                    for ($j = 0; $j < $tableAmount; $j++){
                        $slots[$i][] = (!is_null($existingId) && $existingId != "") ? $existingId : "new";
                    }
                }
            }
        }

        $result["slots"] = $slots;

        return $result;
    }
    
    public function getAnimator(){
        return new User($this->userId);
    }
    
    public function getId(){
        return $this->partyId;
    }
    public function getUserId(){
        return $this->userId;
    }
    public function getType(){
        $sql = "SELECT * FROM Types WHERE typeId = ".$this->typeId;
        $res = mysql_query ( $sql );
        return mysql_fetch_assoc($res);
    }
    public function getTypeName() {
        return stripslashes($this->getType()['name']);
    }
    public function getName(){
		return stripslashes($this->name);
	}
    public function getKind(){
		return $this->kind;
	}
    public function getScenario(){
		return $this->scenario;
	}
    public function getPlayerMin(){
		return $this->playerMin;
	}
    public function getPlayerMax(){
		return $this->playerMax;
	}
    public function getLevel(){

        $levels = array(
            'low' => 'Débutant',
            'middle' => 'Initié',
            'high' => 'Expert',
            'anyway' => 'Peu importe'
        );

		return $levels[$this->level];
	}
    public function getDuration(){
		return $this->duration;
	}
    public function getStart(){
		return $this->start;
	}
    public function getStartDay(){
        return intval(strftime('%d', strtotime($this->start))) - Controls::getDate(Controls::CONV_START, '%d') + 1;
    }
    public function getDescription(){
        return $this->description;
    }
    public function setDescription($desc){
        $this->description = $desc;
		$this->isFake = true;
    }
    public function getNote(){
		return $this->note;
	}
    public function setNote($note){
		$this->note = $note;
		$this->isFake = true;
	}
    public function getLanguage(){
		return $this->language;
	}
    public function getYear(){
		return $this->year;
	}
    public function getState(){
		return $this->state;
	}
    public function getTableAmount(){
        return $this->tableAmount;
    }
    
	public function accMail(){
		$sql = 'SELECT * FROM Users WHERE userId = (SELECT userId FROM Parties where partyId = ' . $this->partyId . ')';
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		if($row['accepteMail']){
			return true;
		}else{
			return false;
		}
	}
	
	public function freeSlot(){
		$sql = "SELECT COUNT(*) AS nbr FROM Inscriptions WHERE partyId = " . $this->partyId . ";";
		$res = mysql_query($sql);
		$res = mysql_fetch_assoc($res);
		
		return ($this->getPlayerMax() - $res['nbr']);
	}
	
    public static function mailAnim($pId, $pBody, $pEmail){
		$id = htmlentities($pId, ENT_QUOTES, "UTF-8");
		$sql = 'SELECT email FROM Users WHERE userId = (SELECT userId FROM Parties WHERE partyId = ' . $id . ') AND accepteMail = 1;';
		
		$res = mysql_query($sql);
		
		if(mysql_num_rows($res) == 1){
			$row = mysql_fetch_assoc($res);
			$email = $row['email'];
			return Orcimail::contactAdmin(htmlentities($pBody, ENT_QUOTES, "UTF-8"), htmlentities($pId, ENT_QUOTES, "UTF-8"), $email, $pEmail);
		}else{
			return false;
		}
		
	}
    
    
	
    private static function dateToSlot($date){
    
        $start = Controls::getDate(Controls::CONV_START);
        $d = strtotime($date);
        
        $nb = ($d - $start) / 60 / 30 ;
        return $nb;
    }
    private static function slotsCharge($slots){
        $max = 0;
        foreach($slots as $parties){
            if($max < count($parties)){
                $max = count($parties);
            }
        }
        return $max;
    }
}