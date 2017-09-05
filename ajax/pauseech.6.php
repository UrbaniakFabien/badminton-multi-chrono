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
if ($num!=0) {
  $nbr_absent=0;
  //On extrait tous les joueurs dont la liste des matchs connus contient le N° de match passé en parametre
  $sql="SELECT Joueur, Matchs, etat 
         FROM joueurs where num_titre=".$num_titre ." and matchs like '%".$num."%'";
  $result=mysqli_query($connect,$sql);
  while ($data=mysqli_fetch_assoc($result)) {
        //Pour affiner le résultat on decompose la liste de matchs connus du joueurs en tableau
        $tab_match=explode(",",$data["Matchs"]);
        //On compare chaque éléments avec le N° passé en parametre
        foreach ($tab_match as $e_tab_match) {
          //Si un des éléments correspond, test l'état du joueur  et on compte le nombre de joueurs en état <> 1
          if ($e_tab_match==$num) {
              if ($data["etat"]!=1) {$nbr_absent++;}
              break;
          }
        }
  }
}
$retour=array('num_match'=>$num,'absent'=>$nbr_absent);
echo json_encode ($retour);
?>
