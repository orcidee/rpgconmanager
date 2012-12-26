<?php
require_once(dirname(__FILE__).'/../classes/user.php');

class Orcimail {

    public static function sendPassword($user, $pwd){
        $to = $user->getEmail();
        $body = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Suite à ta demande, ton mot de passe a été réinitialisé.</p>
		<p>Voici ton nouveau mot de passe:  $pwd  </p>
        
        <p></p><p>Nous te suggérons de vérifier tes informations et de modifier ton mot de passe,
        via ta page de profil (<a href='http://localhost/orcidee/rpgconmanager/?page=profile'>
        http://localhost/orcidee/rpgconmanager/?page=profile)</a>)</p>
        
        <p>Pour toute information supplémentaire, n'hésite pas à nous contacter à: 
        <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a>.</p><br/>
        
        <p><strong>Nous nous réjouissons de te voir à Orc'idée et espérons que tu passeras un excellent week-end !</strong></p>";
    
        return self::sendMail (array(
            'body' => $body,
            'to' => $to,
            'subject' => "Orc'idee - Réinitialisation du mot de passe"
        ));
    }

    public static function notifyCancel($party){

        $u = new User($party->getUserId());
        $to = $u->getEmail();
    
        $body = "
        <p>Bonjour ".$u->getFirstname().",</p>
        <p>L'animation que vous avez inscrite (no ".$party->getId().") a été annulée
			(et les éventuels participants prévenus).</p>
        
        <p>Pour la rétablir ou pour toute autre information, n'hésitez pas à nous contacter: 
        <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a>.</p><br/>
        
        <p><strong>Nous nous réjouissons de vous voir à Orc'idée et espérons que vous passerez un excellent week-end !</strong></p>";
    
        return self::sendMail (array(
            'body' => $body,
            'to' => $to,
            'subject' => "Orc'idee - Animation ".$party->getId()." annulée"
        ));
    }
    
    public static function notifyVerify($party){
        $u = new User($party->getUserId());
        $to = $u->getEmail();
    
        $body = "
        <p>Bonjour ".$u->getFirstname().",</p>
        <p>L'animation que vous avez inscrite (no ".$party->getId().") a été acceptée par le comité d'organisation.</p>
        
        <p>Il vous reste quelques temps pour <strong>la rééditer</strong>, à l'aide du lien suivant: 
        <a href='".Controls::home()."?page=edit&partyId=".$party->getId()."'>".
        Controls::home()."?page=edit&partyId=".$party->getId()."</a>. Dans peu de temps nous 
        validerons cette partie, dès lors elle ne sera plus éditable et apparaîtra dans la liste, 
        disponibles à tous pour s'y inscrire.</p>
        
        <p>N'hésitez pas à nous contacter: <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a>.</p><br/>
        <p><strong>Nous nous réjouissons de vous voir à Orc'idée et espérons que vous passerez un excellent week-end !</strong></p>";
    
        return self::sendMail (array(
            'body' => $body,
            'to' => $to,
            'subject' => "Orc'idee - Animation ".$party->getId()." vérifée"
        ));
    }
    
    public static function notifyValidate($party){
        $u = new User($party->getUserId());
        $to = $u->getEmail();
    
        $body = "
        <p>Bonjour ".$u->getFirstname().",</p>
        <p>'A y'est, votre animation (".$party->getId().") a été validée par le comité d'organisation.</p>
        
        <p>Vous pouvez encore <strong>éditer</strong> la description détaillée, à l'aide du lien suivant: 
        <a href='".Controls::home()."?page=edit&partyId=".$party->getId()."'>".
        Controls::home()."?page=edit&partyId=".$party->getId()."</a>. Votre partie est disponible
        à tous pour s'y inscrire.</p>
        
        <p>Si par malheur vous deviez annuler cette animation, nous vous prions de nous contacter:
        <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a>.</p><br/>
        <p><strong>Nous nous réjouissons de vous voir à Orc'idée et espérons que vous passerez un excellent week-end !</strong></p>";
    
        return self::sendMail (array(
            'body' => $body,
            'to' => $to,
            'subject' => "Orc'idee - Animation ".$party->getId()." validée"
        ));
    }
    
    public static function notifyRefuse($party){
    
        $u = new User($party->getUserId());
        $to = $u->getEmail();
    
        $body = "
        <p>Bonjour ".$u->getFirstname().",</p>
        <p>L'animation que vous avez inscrite (no ".$party->getId().") a été refusée par le comité.</p>
        
        <br/>
        <p>Vous pouvez retenter d'inscrire cette animation, <strong>en la rééditant,</strong> à 
        l'aide du lien suivant: <a href='".Controls::home()."?page=edit&partyId=".$party->getId()."'>".
        Controls::home()."?page=edit&partyId=".$party->getId()."</a></p>
        
        <p>N'hésitez pas à nous contacter: <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a>.
        (Par exemple pour connaître les raisons de ce refus)</p><br/>
        
        <p>Merci pour votre compréhension.</p>";
        
            
        return self::sendMail (array(
            'body' => $body,
            'to' => $to,
            'subject' => "Orc'idee - Animation ".$party->getId()." refusée"
        ));
    }

    public static function subscribeToParty ($p, $user){
    
        $type = $p->getType();
        $mj = $p->getAnimator();
    
        // Corps de l'email à l'inscription joueur, à une partie.
        $message = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Ceci est une confirmation d'inscription à l'animation no ".$p->getId().".</p>
        <br/>
        <p><strong>Vous allez donc participer à l'animation suivante</strong></p>
        <table cellpadding='5' cellspacing='0' border='1'>
            <tr><td>Type</td><td>".stripslashes($type['name'])."</td></tr>
            <tr><td>Titre</td><td>".$p->getName()."</td></tr>
            <tr><td>Genre</td><td>".$p->getKind()."</td></tr>
            <tr><td>Scénario</td><td>".$p->getScenario()."</td></tr>
            <tr><td>Description</td><td>".$p->getDescription()."</td></tr>
            <tr><td>Niveau de jeu</td><td>";
            
            /*
            "<tr><td>Nombre de joueurs minimum</td><td>".$p->getPlayerMin()."</td></tr>
            <tr><td>Nombre de joueurs maximum</td><td>".$p->getPlayerMax()."</td></tr>";*/
            
            
            $lvl = "Peu importe";
            if($p->getLevel() == 'high'){
                $lvl = "Expert";
            }elseif($p->getLevel() == 'low'){
                $lvl = "Débutant";
            }elseif($p->getLevel() == 'middle'){
                $lvl = "Initié";
            }
            
            $message .= $lvl."</td></tr>
            <tr><td>Durée</td><td>".$p->getDuration()." heures</td></tr>
            <tr><td><strong>Date & Heure de début</strong></td><td><strong>";
            
            $date = strftime("%d.%m.%Y à %H:%M", strtotime($p->getStart()));
            
            $message .= $date."</strong></td></tr>
        </table>
        
        <br/><p><strong>Animateur / MJ</strong>
        <p>Au cas où vous auriez besoin de contacter l'animateur de cette partie...</p>
        
        <table cellpadding='5' cellspacing='0' border='1'>
            <tr><td>Nom</td><td>".$mj->getLastname()."</td></tr>
            <tr><td>Prénom</td><td>".$mj->getFirstname()."</td></tr>
            <tr><td>Email</td><td>".$mj->getEmail()."</td></tr>";
            /*
            <tr><td>Télephone</td><td>".$user->getPhone()."</td></tr>
            <tr><td>Adresse</td><td>".$user->getAddress()."</td></tr>
            <tr><td>NPA</td><td>".$user->getNpa()."</td></tr>
            <tr><td>Ville</td><td>".$user->getCity()."</td></tr>
            <tr><td>Pays</td><td>".$user->getCountry()."</td></tr>*/
        
        $unlink = Controls::home()."?page=list&action=unsubscribe&partyId=".$p->getId()."&u=".sha1($user->getId());
        
        $message .= "</table>
        <p>Lien pour <strong>vous désinscrire</strong>: <a href='".$unlink."'>".$unlink."</a></p>
        
        <p>N'hésitez pas à nous contacter si besoin : <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a></p><br/>
        <p><strong>Nous nous réjouissons de vous voir à Orc'idée et espérons que vous passerez un excellent week-end !</strong></p>";
        
        
        
        return self::sendMail(array(
            'to' => $user->getEmail(),
            'subject' => ("Orc'idee - Confirmation d'inscription à la partie ".$p->getId()),
            'body' => $message
        ));
    }

    public static function unsubscribeToParty ($p, $user){
        // Corps de l'email
        $message = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Vous avez demandé à être désinscrit de l'animation no ".$p->getId().",
			intitulée '".stripslashes($p->getName())."',
			et prévue le ".strftime("%d.%m.%Y à %H:%M", strtotime($p->getStart())).".</p>";
			        
        $unlink = Controls::home()."?page=list&action=unsubscribe&partyId=".$p->getId()."&u=".sha1($user->getId());
        
        $message .= "<p><strong>Veuillez confirmer<strong> en cliquant sur le lien suivant :
			<a href='".$unlink."'>".$unlink."</a></p>
		<p>(sinon, ignorez juste ce message)</p>
        <p>N'hésitez pas à nous contacter si besoin : <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a></p>
        <p>Nous espérons vous voir tout de même à Orc'idée en vous souhaitant de passer un excellent week-end !</p>";
        
        return self::sendMail(array(
            'to' => $user->getEmail(),
            'subject' => ("Orc'idee - Demande de désinscription de la partie ".$p->getId()),
            'body' => $message
        ));
    }

    public static function unsubscribedToParty ($p, $user){
        // Corps de l'email
        $message = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Nous vous informons que vous avez été désinscrit de l'animation no ".$p->getId().",
			intitulée '".stripslashes($p->getName())."',
			et prévue le ".strftime("%d.%m.%Y à %H:%M", strtotime($p->getStart())).".</p>
        <p>N'hésitez pas à nous contacter si besoin de détails : <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a></p>
        <p>Nous espérons vous voir tout de même à Orc'idée en vous souhaitant de passer un excellent week-end !</p>";
        
        return self::sendMail(array(
            'to' => $user->getEmail(),
            'subject' => ("Orc'idee - Désinscription de la partie ".$p->getId()),
            'body' => $message
        ));
    }

    public static function ctcAdmin($pBody, $pId, $pMail, $pEmail){
        
        return self::sendMail(array(
            'to' => $pMail,
            'subject' => ("Orc'idee - Contact pour la partie ".$pId),
            'body' => $pBody . "/nPrière de répondre à: " . $pEmail
        ));
    }

    public static function unsubscribedToCanceledParty ($p, $user){    
        // Corps de l'email
        $message = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Nous vous informons de l'annulation de l'animation no ".$p->getId().",
			intitulée '".stripslashes($p->getName())."',
			et prévue le ".strftime("%d.%m.%Y à %H:%M", strtotime($p->getStart())).". Vous avez été désinscrit.</p>
        <p>N'hésitez pas à nous contacter si besoin de détails : <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a></p>
        <p>Nous espérons vous voir tout de même à Orc'idée en vous souhaitant de passer un excellent week-end !</p>";
        
        return self::sendMail(array(
            'to' => $user->getEmail(),
            'subject' => ("Orc'idee - Annulation de la partie ".$p->getId()),
            'body' => $message
        ));
    }
    
    public static function notifyCreate ($p, $edit){
        
        $user = $p->getAnimator();
        $type = $p->getType();
        
        $message = "
        <p>Bonjour ".$user->getFirstname().",</p>
        <p>Ceci est une confirmation ".(($edit)?"de mise à jour":"d'inscription")." de l'animation no ".$p->getId().".</p>
        <br/>
        <p><strong>Votre animation</strong></p>
        <table cellpadding='5' cellspacing='0' border='1'>
            <tr><td>Type</td><td>".stripslashes($type['name'])."</td></tr>
            <tr><td>Titre</td><td>".$p->getName()."</td></tr>
            <tr><td>Genre</td><td>".$p->getKind()."</td></tr>
            <tr><td>Scénario</td><td>".$p->getScenario()."</td></tr>
            <tr><td>Description</td><td>".$p->getDescription()."</td></tr>
            <tr><td>Nombre de joueurs minimum</td><td>".$p->getPlayerMin()."</td></tr>
            <tr><td>Nombre de joueurs maximum</td><td>".$p->getPlayerMax()."</td></tr>
            <tr><td>Niveau de jeu</td><td>";
            
            $lvl = "Peu importe";
            if($p->getLevel() == 'high'){
                $lvl = "Expert";
            }elseif($p->getLevel() == 'low'){
                $lvl = "Débutant";
            }elseif($p->getLevel() == 'middle'){
                $lvl = "Initié";
            }
            
            $message .= $lvl."</td></tr>
            <tr><td>Durée</td><td>".$p->getDuration()." heures</td></tr>
            <tr><td>Date & Heure de début</td><td>";
            
            $date = strftime("%d.%m.%Y à %H:%M", strtotime($p->getStart()));
            
            $message .= $date."</td></tr>
        </table>
        <p><strong>Note aux organisateurs</strong><br/>".$p->getNote()."</p>
        
        <p><strong>Quelques informations vous concernant.</strong>
        <br/>Merci de nous contacter si elles sont incorrectes.</p>
        <table cellpadding='5' cellspacing='0' border='1'>
            <tr><td>Nom</td><td>".$user->getLastname()."</td></tr>
            <tr><td>Prénom</td><td>".$user->getFirstname()."</td></tr>
            <tr><td>Télephone</td><td>".$user->getPhone()."</td></tr>
            <tr><td>Adresse</td><td>".$user->getAddress()."</td></tr>
            <tr><td>NPA</td><td>".$user->getNpa()."</td></tr>
            <tr><td>Ville</td><td>".$user->getCity()."</td></tr>
            <tr><td>Pays</td><td>".$user->getCountry()."</td></tr>
        </table>
        
        <p>Votre animation a désormais le <strong>status \"créée\"</strong>. Cela
        signifie qu'on ne peut pas encore s'y inscrire.
        Elle sera disponible aux joueurs, dès que nous l'aurons validée.
        Merci pour votre compréhension.</p>
        
        <p>Lien pour <strong>modifier votre partie</strong>: <a href='".Controls::home()
        ."?page=edit&partyId=".$p->getId()."'>".
        Controls::home()."?page=edit&partyId=".$p->getId()."</a></p>
        
        <p>N'hésitez pas à nous contacter: <a href='mailto:info@orcidee.ch'>info@orcidee.ch</a></p><br/>
        <p><strong>Nous nous réjouissons de vous voir à Orc'idée et espérons que vous passerez un excellent week-end !</strong></p>";
        
        
        return self::sendMail(array(
            'to' => $user->getEmail(),
            'subject' => "Orc'idee - Partie ".$p->getId()." enregistrée",
            'body' => $message
        ));
    }
    
    private static function sendMail ($data){
        
        $headers =  "From: " . MAIL_FROM . "\r\n" .
                    "Reply-To: " . MAIL_FROM . "\r\n" .
                    "Cc: " . MAIL_CC . "\r\n" .
                    "X-Mailer: PHP/" . phpversion() . "\r\n" .
                    "MIME-Version: 1.0" . "\r\n" .
                    "Content-type: text/html; charset=UTF-8";
        
        $signature = "<div style='clear:both;'><img src='http://www.orcidee.ch/images/divers/Logop.png' alt='Orcidee'/>".
        "<p>Convention lémanique de jeu de simulation</p>".
        "<p>Lausanne</p>".
        "<p><strong><a href='http://www.orcidee.ch'>www.orcidee.ch</a></strong></p></div>";
        
        $body = $data['body'] . $signature ;
        
        return mail ($data['to'],$data['subject'],$body,$headers);
    }
    
}