<?php
require_once(dirname(__FILE__).'/../classes/user.php');
class View {
    
    public $content;
    
    public function __construct () {
        $this->content = null;
    }

    /**
     * @param String $str The text to display
     * @param bool $isRTF Set to true if $str is Rich Text Format
     * @return string The text ready for display
     */
	public static function MultilineFormat($str, $isRTF = false) {
        if($isRTF){
            return "<div class='rtf'>$str</div>";
        }else{
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