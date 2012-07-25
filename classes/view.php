<?php
require_once(dirname(__FILE__).'/../classes/user.php');
class View {
    
    public $menu;
    public $content;
    
    public function __construct () {
        
        $this->menu = Array (   "list"      => FALSE,
                                "create"    => FALSE, 
                                "plan"      => FALSE, 
                                "controls"  => FALSE,
                                "logout"    => FALSE,
                                "login"     => FALSE, 
                                "register"  => FALSE, 
                                "profile"   => FALSE );
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
        
        if(isset($_GET['page'])){
            $this->content = $_GET['page'];
            if($this->content == 'edit'){
                $this->content = 'create';
            }
        }
        
        ?>
        <nav id="main-nav"><?php
        
            $user = User::getFromSession();
			If(!$user && isset($_SESSION["userEmail"])){
				$user = User::pseudoAuth($_SESSION["userEmail"]);
			}

            if($user && $this->content != "logout"){
                echo "<a href='?page=logout'>Déconnexion</a>";
                if($this->content == 'list'){
                    echo "<a href='?page=create'>Inscrire une partie</a>";
                }
            }
        /*
            print "<ul>";
            
                print (($this->menu["list"]) ? "<li><a href='?page=list'>Parties</a></li>" : "");
                print (($this->menu["create"]) ? "<li><a href='?page=create'>Inscrire une partie</a></li>" : "");
                print (($this->menu["plan"]) ? "<li><a href='?page=print'>Impressions</a></li>" : "");
                print (($this->menu["controls"]) ? "<li><a href='?page=conf'>Configuration</a></li>" : "");
                print (($this->menu["logout"]) ? "<li><a href='?page=logout'>Déconnexion</a></li>" : "");
                print (($this->menu["login"]) ? "<li><a href='?page=login'>S'authentifier</a></li>" : "");
                print (($this->menu["register"]) ? "<li><a href='?page=register'>Inscription MJ</a></li>" : "");
                print (($this->menu["profile"]) ? "<li><a href='?page=profile'>Profil</a></li>" : "");
            
            print "</ul>"; */ ?>
        </nav>
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
        <nav id="bottom-nav"><?php
        
            $user = User::getFromSession();
            if($user && $this->content != "logout"){
                echo "<a href='?page=logout'>Déconnexion</a>";
                if($this->content == 'list'){
                    echo "<a href='?page=create'>Inscrire une partie</a>";
                }
            }
        ?>
        </nav>
        <?php
        
    }
    
}