<?php
session_start();
/********************************************************
 * Mise a jour des donnée de l'échéancier
 * 
 *******************************************************/  
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;
include ("../connect.7.php");
//Limite à 30 secondes le traitement
set_time_limit(30);
  $num=0;
  $etat=0;
  $terrain=0;
  $heure="00h00";
  
  if (isset($_POST["num"])) {$num=substr($_POST["num"],4);}
  if (isset($_POST["etat"])) {$etat=$_POST["etat"];}
  if (isset($_POST["terrain"])) {
      $terrain=$_POST["terrain"];
      if ($terrain=="undefined") {$terrain=0;}
  }
  if ($terrain=="") {$terrain=0;}
  if (isset($_POST["heure"])) {$heure=$_POST["heure"];}
 
  $sql = "update echeancier set etat=" .$etat.", terrain=".$terrain;
  switch ($etat) {
        case 0 : $sql.=", heure_debut='00h00' ,heure_fin='00h00' ";
                break;
        case 1 : if ($heure!="99h99") {
                    //si l'heure est <>99h99 on met a jour l'heure de debut du match
                    $sql.=", heure_debut='".$heure."' " ;}
                break;
        case 2 : $sql.=", heure_fin='".$heure."' ";
                break;
  }
  $sql.=" where num_match=".$num." and num_titre=".$num_titre.";";
 exec_commande($sql);

  /* Gestion des match mis en WO par le pointage des joueurs */
  $sql="SELECT num_match, etat
        FROM echeancier
        WHERE num_titre=".$num_titre." and etat in (3,-1)";
  $result=mysqli_query($connect,$sql);
  $donne = array();
  while ($data=mysqli_fetch_assoc($result)) {
      if ($data["etat"]==-1) {$data["etat"]=0;}
      $donne[]=$data;
  } 
  mysqli_close($connect);
  echo json_encode($donne);
?>
