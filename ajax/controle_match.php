<?php
session_start();
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;
/********************************************
 * Module pour retourner l'etat du match dont le N°
 * a été passé en parametre 
 * *******************************************/  
include ("../connect.7.php");
$etat=0;
$num=0;
try {
      if (isset($_POST["num"])) {$num=$_POST["num"];}
      if (isset($_GET["num"])) {$num=$_GET["num"];}
      if (($num!=0) and ($num_titre>0)) {
          $sql = "SELECT etat FROM `echeancier` WHERE `num_match`=".$num . " and num_titre=".$num_titre;
          $result=mysqli_query($connect,$sql);
          if ($data=mysqli_fetch_assoc($result)) {
            $etat= $data["etat"];
          }
      }
      echo $etat;
}
catch(Exception $e){
  //echo $e->getMessage();
  echo 0;
}
?>
