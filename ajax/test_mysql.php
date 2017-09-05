<?php
/*******************************************************************************
 *Module test parametrage MySQL
 *Fu
 *12/2013
 *******************************************************************************/
 $nom_serveur_sql=$_POST["nom_serveur_mysql"];
 $nom_utilisateur_sql=$_POST["utilisateur_mysql"];
 $mdp_sql=$_POST["mdp_mysql"];
 $nom_base_sql=$_POST["nom_db_mysql"];
 
 $connect = @mysqli_connect($nom_serveur_sql,$nom_utilisateur_sql,$mdp_sql) or die(json_encode(array("color"=>"red","msg"=>"Erreur lors de l'ouverture de la connexion MYSQL!"))); 
 echo json_encode(array("color"=>"green","msg"=>"Connexion r&eacute;ussie !"));
   
?>