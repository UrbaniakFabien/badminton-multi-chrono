<?php
/*******************************************************************************
 *Module test utilisateur Multi_chrono
 *FU
 *12/2013
 *Lecture des parametrage dans le fichier config.ini 
 *gestion d'un compte utilisateur et d'un compte administrateur=>changement dans le menu général 
 *******************************************************************************/   
$tab_config=parse_ini_file("config.ini");
$salt = 'BwGk15l8WX'; // $salt permet d'avoir un mot de passe plus sécurisé
//$_admin_pass = md5($tab_config["mdp_multi_chono"].$salt); // on crypt pour pouvoir comparer - plus sécurisé
$_admin_pass = $tab_config["mdp_administrateur"]; // Mot de passe cripté
$_admin_login = $tab_config["nom_administrateur"];
$_user_pass= $tab_config["mdp_utilisateur"]; // Mot de passe cripté
$_user_login=$tab_config["nom_utilisateur"];
?>