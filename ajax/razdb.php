<?php
 /**************************************************
  * module pour detruire la base dans son ensemble
  * FU 12/2013
  * 
  *A n'utiliser que si l'on veut réinitialiser la base
  *
  * FUR 12/2017
  * Suppression des tables uniquement
  **************************************************/
  session_start();
  include ("../connect.7.php");
//Table des tables à effacer
$tables = ['titre','echeancier','joueurs', 'param', 'tbl_config_chrono', 'pref_titre' ,
           'pref_param' ,'pref_tbl_config_chrono','tmp_joueurs','tbl_couleurs','tbl_regl_clubs' ,'tbl_regl_joueurs'];
//Liste des tables à effacer
$liste_tables = implode(",",$tables);
//Effacement de tables
 $sql = "DROP TABLE IF EXISTS $liste_tables";
exec_commande($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($connect));
//Suppression de la session
 
  unset($_SESSION['_login']);
  unset($_SESSION['_pass']);
  unset($_SESSION["num_titre"]);
  unset($_SESSION);
