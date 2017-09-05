<?php
/********************************************************************************
 * Module enregistre  config.ini
 * FU
 * 12/2013
 *******************************************************************************/
 $salt = 'BwGk15l8WX'; // $salt permet d'avoir un mot de passe plus sécurisé
 $version=$_POST["version"];
 file_put_contents("../config.ini",";Configuration multi_chono ".$version."
 
;Configuration serveur MySQL
[mysql]
 nom_serveur_sql=".$_POST["nom_serveur_mysql"]."
 nom_utilisateur_sql=".$_POST["utilisateur_mysql"]."
 mdp_sql=".$_POST["mdp_mysql"]."
 nom_base_sql=".$_POST["nom_db_mysql"]."

;Configuration accés protegé 
[multi_chrono]
 version = ".$version."
 nom_administrateur=".$_POST["nom_administrateur"]."
 mdp_administrateur=". md5($_POST["mdp_administrateur"].$salt)."
 nom_utilisateur=".$_POST["nom_utilisateur"]."
 mdp_utilisateur=".md5($_POST["mdp_utilisateur"].$salt)
 );   
?>                                        