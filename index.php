<?php
require_once(dirname(__FILE__).'/conf/conf.php');
?>
<!DOCTYPE html> 
<html lang="fr" >

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" lang="fr" content="Module de gestion des parties Orc'Idee">
        <meta name="keywords" lang="fr" content="orcidee">
		<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
        <link rel="stylesheet" type="text/css" href="css/styles.css?_<?php echo CACHE_KILL;?>" />
		<link rel="stylesheet" type="text/css" href="css/2013.css?_<?php echo CACHE_KILL;?>"/>

    </head>
    <body>

<?php
require_once(dirname(__FILE__).'/conf/bd.php');
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/classes/controls.php');

require_once(dirname(__FILE__).'/classes/user.php');
require_once(dirname(__FILE__).'/classes/view.php');

if(!$db){
    echo "<p class='dbg'>Impossible de selectionner la base de donnees</p>";
}else{

    echo "<p class='dbg'>Connexion BD ok</p>";

    $view = new View();
    $user = User::getFromSession();
    
    echo "<div class='dbg'>User:";
    echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
    echo "</div>";
    
    $title = null;
    
    switch (@$_GET['page']){
        case "create": $title = "Proposer une partie ou une animation";
        break;
        case "edit": $title = "Editer une animation existante";
        break;
        case "profile": $title = "Editer mon profil";
        break;
        case "list": $title = "Liste des parties";
        break;
        case "print": $title = "Imprimer les plans";
        break;
        case "conf": $title = "Contrôles de l'application";
        break;
        case "tables": $title = "Définir les numéros de table";
        break;
        case "logout": $title = "Déconnexion";
        break;
    }
    
    
    if(Controls::isAppOpen()){
        
        if($user && ($user->getRole() == "animator" || $user->getRole() == "administrator") ){
        
            // Ajouter une partie / Editer une partie
            if(@$_GET['page'] == "create" || @$_GET['page'] == "edit"){
                if(Controls::isMjOpen() || isset($_GET['partyId'])){
                    $view->content = "create";
                }
            }
            
            // Editer mon profil
            if(@$_GET['page'] == "profile"){
                $view->content = "profile";
            }

        }
        
        // Liste des parties (avec ou sans filtre)
        if(@$_GET['page'] == "list"){
                $view->content = "list";
        }
        
    }
    
    if($user && $user->getRole() == "administrator"){
        
        // Impressions (plans)
        if(@$_GET['page'] == "print"){
            $view->content = "print";
        }
        
        // Contrôles de l'application
        if(@$_GET['page'] == "conf"){
            $view->content = "conf";
        }
        
        // Définition des numéros de table
        if(@$_GET['page'] == "tables"){
            $view->content = "tables";
        }
        
        // Définition des numéros de table
        if(@$_GET['page'] == "users"){
            $view->content = "users";
        }
    }
    
    if(@$_GET['page'] == "logout"){
        $view->content = "logout";
    }

    if(!$user){
        
        // By default: No forward after authentification
        $forward = "";
        
        // $title not null means we know the feature requested (see switch case, upper in this page)
        if(!is_null($title)){
        
            $forwardValues = "";
            // Add here valid forward parameter
            if(isset($_GET['page']) && $_GET['page'] != 'logout'){
                $forwardValues .= "page=".$_GET['page']."&";
            }
            if(isset($_GET['partyId'])){
                $forwardValues .= "partyId=".$_GET['partyId']."&";
            }
            $forward = "?forward=".urlencode($forwardValues);
        }
    }
    
    include("menu.php");
    
    if(!is_null($view->content)){
        $view->html();
    }
    
    

}
mysql_close($dbServer);

include("scripts.php");

?>
<script type="text/javascript">
if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){ //test for MSIE x.x;
 var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number
 if (ieversion>=9)
  alert("Nous vous recommandons Firefox, Chrome, Opera ou Safari pour l'usage de cette page. Sous IE9, pour afficher correctement le module de gestion des parties, vous devez aller activer les scripts de IE9 (options internet>sécurité>personnaliser le niveau>Scripts> tout cocher sur activer) puis vider votre cache! ")
 else if (ieversion<=8)
  alert("Votre navigateur ne lit pas correctement notre module de gestion de parties. Nous vous recommandons de le mettre à jour, sans quoi vous pouvez rencontrer des problèmes d'affichage. Nous vous conseillons Firefox, Chrome, Opera ou Safari pour une utilisation optimale du site.")
}

</script>
    </body>
</html>