<?php
/*******************************************************************************
 * Efface les préférences num_pref
 * dans les deux tables 
 *  pref_param
 *  tbl_pref_config_chrono  
 *******************************************************************************/
 include("../connect.7.php");
 
 $num_pref=isset($_GET["num_pref"])? $_GET["num_pref"] : 0;
 if ($num_pref>0) {
    $sql="DELETE 
          FROM pref_param 
          WHERE num_titre=".$num_pref.";";
   exec_commande($sql);
    $sql="DELETE 
          FROM pref_tbl_config_chrono 
          WHERE num_titre=".$num_pref.";";
   exec_commande($sql);
    $sql="DELETE FROM pref_titre
          WHERE num_titre=".$num_pref.";";
   exec_commande($sql);
    
 } 
 echo $sql;
?>