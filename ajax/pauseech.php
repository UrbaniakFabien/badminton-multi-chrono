<?php
session_start();
$num_titre=$_SESSION["num_titre"];
/********************************************
 * Module pour retourner le premier N° de match qui n'est pas une pause ou WO
 * en fonction du N° de match passé en parametre
 * Retourne 0 si pas de match trouvé 
 * *******************************************/  
include ("../connect.7.php");
$num=0;
if (isset($_POST["num"])) {$num=$_POST["num"];}
if (isset($_GET["num"])) {$num=$_GET["num"];}
if ($num!=0) {
    $sql = "SELECT `num_match` 
            FROM `echeancier` 
            WHERE `num_match`>=".$num." and `spe`<>'Pause' and etat<>3 and num_titre=".$num_titre."
            ORDER BY num_titre, num_match
            LIMIT 0,1";
  
    $result=mysqli_query($connect,$sql);
    if ($data=mysqli_fetch_assoc($result)) {
      $num= $data["num_match"];
    }
}
//echo $sql;
echo $num;
?>
