<?php
require_once(dirname(__FILE__).'/../classes/user.php');
class View {
    
    public $content;
    
    public function __construct () {
        $this->content = null;
    }
	
	public static function MultilineFormat($str, $isTextArea = false ) {
		if($isTextArea) {
			return htmlentities($str, ENT_COMPAT, "utf-8");
		} else {
			return nl2br(htmlentities($str, ENT_COMPAT, "utf-8"));
		}
	}
    
    public function html (){
        
        ?>
        
        <div id="main">
            <?php
                $path = dirname(__FILE__)."/../pages/$this->content.php";                        
                if(is_file($path)){
                    include $path;
                }else{
                    header("HTTP/1.0 404 Not Found");
                    include dirname(__FILE__)."/../pages/404.html";
                }
            ?>
        </div>
        
        <?php
        
    }
    
}