<?php

/*
 *Module d'enregistrement decalage horaire pour échéancier convocation
 * FU
 * 01/04/2014
 */
include ("../connect.7.php");
foreach ($_POST as $key=>$e_POST) {
        $$key=$e_POST;
}
$sql="UPDATE titre 
      SET decalage_horaire_convocation='".$decalage."'
      WHERE num_titre=$num_titre";
    @mysqli_query($connect,$sql);
?>
