<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$user = User::getFromSession();
if($user && $user->isAdmin()){
	
    // filter setting
    ?>
    <h1>Liste des personnes inscrites</h1>
    
    <div>
        <form action="" method="GET">
            <label for="filter">Role:</label>
            <select name="filter">
                <option value='all' selected='selected'>Tous</option>
                <option value='player'>Joueur</option>
                <option value='animator'>MJ</option>
                <option value='administrator'>Admin</option>
            </select>
            <input type="hidden" name="page" value="users" />
            <input type="submit" value="filtrer"/>
        </form>
    </div>
    
    <div><?php
    
        $fetchAssoc = User::getUsers(@$_GET['filter'], @$_GET['sort']);
        if($fetchAssoc){ ?>
        
            <table>
                <tr>
                    <th><a href="?page=users&filter=<?php echo @$_GET['filter']?>&sort=userId">ID &#8595;</a></th>
                    <th><a href="?page=users&filter=<?php echo @$_GET['filter']?>&sort=lastname">Nom &#8595;</a></th>
                    <th><a href="?page=users&filter=<?php echo @$_GET['filter']?>&sort=firstname">Prénom &#8595;</a></th>
                    <th><a href="?page=users&filter=<?php echo @$_GET['filter']?>&sort=email">Email &#8595;</a></th>
                    <th><a href="?page=users&filter=<?php echo @$_GET['filter']?>&sort=phone">Telephone &#8595;</a></th>
                    <th>Role</a></th>
                    <th>Profil</th>
                </tr><?php
                
                while ($row = mysql_fetch_assoc($fetchAssoc)) {
                
                    $u = new User($row['userId']);
                
                    echo '<tr><td>'.$u->getId().'</td><td>'.$u->getLastname().
                    '</td><td>'.$u->getFirstname().'</td><td>'.$u->getEmail().'</td>
                    <td>'.$u->getPhone().'</td><td>'.$u->getRole().'</td><td>
                    <a href="?page=profile&id='.$u->getId().'">GO</a></td></tr>';
                    
                }
            ?></table><?php
        }else{
            echo "Erreur lors de la requête, veuillez vérifier vos paramètres d'entrée.";
        }
    
    ?></div>
    <?php
}