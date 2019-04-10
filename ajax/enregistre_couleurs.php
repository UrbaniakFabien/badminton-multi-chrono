<?php

/*
 *Module d'enregistrement des coulerus d'un échéancier
 * FU
 * 22/01/2014
 */
session_start();
include ("../connect.7.php");
foreach ($_POST as $key=>$couleur) {
    $sql="UPDATE tbl_couleurs 
          SET coul_couleur='".str_replace("#","",$couleur)."'
          WHERE coul_id_titre=0 
                AND coul_specialite='".$key."'";
   exec_commande($sql);
}
?>
