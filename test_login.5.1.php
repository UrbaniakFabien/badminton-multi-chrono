<?php
/*******************************************************************************
 *Pour tester le droit a l'application
 *
 *******************************************************************************/  
include ("loginconfig.5.php");
function redirect() {
  include ("login.7.php");

}
if(!isset($_SESSION['_login']) || !isset($_SESSION['_pass']))
{
    // si on ne détecte aucune sessions, c'est que cette personne n'est pas connecté
    // on affiche le formulaire de connexion

    redirect();
    exit();
}
// les sessions existe ... reste à savoir si les informations sont correct ou non
if (!((($_admin_login == $_SESSION['_login']) && ($_SESSION['_pass'] == $_admin_pass)) || (($_user_login == $_SESSION['_login']) && ($_SESSION['_pass'] == $_user_pass))))
    {
         redirect();
         exit();   
        
    }

?>