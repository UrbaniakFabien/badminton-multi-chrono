<?php
/************************************************
 * module retournant pour un N° de match la liste et l'état des joueurs
 * par consultation de la liste de pointage
 *
 *le 01/2014
 *par FU 
 *   Correction sur nom ou prénom composés dans la liste des joueurs d'un match  
 ************************************************/
session_start();
$liste=array();
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;

include ("../connect.7.php");
$num_match=isset($_POST["num_match"])? $_POST["num_match"] : 0;  


if ($num_match!=0) {
  //On extrait tous les joueurs dont la liste des matchs connus contient le N° de match passé en parametre
  $sql="SELECT Joueur, Matchs, etat 
         FROM joueurs where num_titre=".$num_titre ." and matchs like '%".$num_match."%'";
  $result=mysqli_query($connect,$sql);
  while ($data=mysqli_fetch_assoc($result)) {
        //Pour affiner le résultat on decompose la liste de matchs connus du joueurs en tableau
        $tab_match=explode(",",$data["Matchs"]);
        //On compare chaque éléments avec le N° passé en parametre
        foreach ($tab_match as $e_tab_match) {
          //Si un des éléments correspond, on mémorise le nom et l'état du joueur 
          if ($e_tab_match==$num_match) {
              $tab_nom=explode("-",$data["Joueur"]);
              // le nom d'un joueur ou son prenom pouvant contenir un -
              // on reconstitue le nom prenom sans -
              $nom_joueur="";
              for ($i=0 ; $i<=count($tab_nom)-3;$i++) {
                $nom_joueur.=$tab_nom[$i]." ";
              }
              $tab_club=explode("(",$data["Joueur"]);
              $liste[]=array("nom"=>substr($nom_joueur,0,-1),"etat"=>is_null($data["etat"])? 0 : $data["etat"],"club"=>substr($tab_club[1],0,-1));
              break;
          }
        }
  }
}
if (count($liste)==0) {
      $liste[]=array("nom"=>"Pas de joueurs connus","etat"=>2,"club"=>"");
      $liste[]=array("nom"=>"pour ce match","etat"=>2,"club"=>"");
    }
    
foreach ($liste as $key => $row) {
    $club[$key]  = $row['club'];
}
 
// Trie les données par club croissant =>rassemble les joueurs en paire d'un même club
// Ajoute $array en tant que dernier paramètre, pour trier par la clé commune
array_multisort($club, SORT_ASC, $liste);
//On verifie dans le cas de paires de doubles que le tri à bien rassemblé les joueurs d'un même club en paire n° une ou en paire n°deux
if (count($liste)==4) {
    if ($liste[1]["club"]==$liste[2]["club"]) {
        $tmp=$liste[0];
        $liste[0]=$liste[2];
        $liste[2]=$tmp;
}
}
echo json_encode($liste);    
?>