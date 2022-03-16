<?php
require_once(dirname(__FILE__).'/conf/conf.php');
?>
<!DOCTYPE html>
<html lang="fr" >

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	    <title>Module de gestion des parties Orc/'idée</title>
        <meta name="description" lang="fr" content="Module de gestion des parties Orc'Idee">
        <meta name="keywords" lang="fr" content="orcidee">
		<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
        <link rel="stylesheet" type="text/css" href="css/styles.css?_<?php echo CACHE_KILL;?>" />
		<link rel="stylesheet" type="text/css" href="css/2022.css?_<?php echo CACHE_KILL;?>"/>
        <link href='http://fonts.googleapis.com/css?family=Playfair+Display+SC|Playfair+Display:400,400italic,700italic,700,900,900italic' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Orbitron:400,500,700" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bangers&family=Josefin+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

        <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea.tiny-mce",
                menubar: false,
                plugins: "link hr code table textcolor preview",
                toolbar: [
                    "bold italic underline forecolor | table | link bullist numlist | alignleft aligncenter alignright | removeformat | preview"
                ],
                init_instance_callback: function(editor) {
                    setTimeout(function(){
                        orcidee.manager.adaptHeight();
                    }, 200);
                }
            });
        </script>

    </head>
    <body>

<?php
require_once(dirname(__FILE__).'/classes/controls.php');
require_once(dirname(__FILE__).'/classes/user.php');
require_once(dirname(__FILE__).'/classes/view.php');



$view = new View();
$user = User::getFromSession();
$controls = new Controls();

if(IS_DEBUG){
    echo "<div class='dbg'>
        <p>Database</p>
         <ul>
            <li>Connexion ok</li>
            <li>Using ".DB."</li>
            </ul>
        <p>User: ". (($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0");
    echo "</p></div>";
}

$title = null;

switch (@$_GET['page']){
    case "create": $title = "Proposer une partie ou une animation";
    break;
    case "edit": $title = "Editer une animation existante";
    break;
    case "party": $title = "Détail de la partie";
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


if($controls->isAppOpen()){

    if($user && ($user->getRole() == "animator" || $user->getRole() == "administrator") ){

        // Ajouter une partie / Editer une partie
        if(@$_GET['page'] == "create" || @$_GET['page'] == "edit"){
            if($controls->isMjOpen() || isset($_GET['partyId'])){
                $view->content = "create";
            }
        }

        // Editer mon profil
        if(@$_GET['page'] == "profile"){
            $view->content = "profile";
        }

  // Contacter l'équipe orc'idee
  if(@$_GET['page'] == "contact"){
            $view->content = "contact";
        }

    }

    // Open pages
    if ( in_array (@$_GET['page'], array('list', 'party'))) {
        $view->content = $_GET['page'];
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

if( ! @$_GET["modal"] == true ){
  include("menu.php");
}

if(!is_null($view->content)){
    $view->html();
} else {
    echo "<br/><br/><br/><br/><div style='text-align:center; font-weight:bold;font-size:1.2em;'>Bienvenue sur le module de gestion des parties d'Orc'idée !</div>
    <br/><br/>
    <div style='margin:auto; text-align:center;'><img src='https://parties.orcidee.ch/img/BanFb.jpg' alt='' style='max-width:90%;'/></div>";
}


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
</script></body>
</html>
