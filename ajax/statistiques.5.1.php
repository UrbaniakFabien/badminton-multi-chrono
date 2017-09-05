<?php
session_start();
$num_titre=$_SESSION["num_titre"];
 /*************************************
  * Module de statistiques
  * 
  * Nombre de match joués
  * Nombre de match en cours
  * Nombre de match à jouer
  * Durée moyenne des matchs
  * durée la plus longue
  * Durée la plus courte  
  * Fu
  * 01/2014
  * Ajout comptage WO                
  **************************************/
  
  /*************************************
   * Début des fonctions
   ************************************/         
  function conv_temp($heure) {
    $tab_temp=explode("h",$heure);
    return ($tab_temp[0]*60)+$tab_temp[1];
  }
  
  function ConvertSecondesEnHeures($time) {
      $tabTemps = array("j" => 86400,
      "h" => 3600,
      "min" => 60,
      "sec" => 1);
      
      $result = "";
      
      foreach($tabTemps as $uniteTemps => $nombreSecondesDansUnite) {
          $$uniteTemps = floor($time/$nombreSecondesDansUnite);
          
          $time = $time%$nombreSecondesDansUnite;
          
          if($$uniteTemps > 0 || !empty($result))
          $result .= $$uniteTemps." $uniteTemps ";
      }
      return $result;
  }
  /********************************************************************
   * fin des fonctions 
   ********************************************************************/     
  include ("../connect.7.php");
  //Nbr matchs joués
  $sql="select count(Num_match) as nbr from echeancier where etat=2 and num_titre=".$num_titre.";";   
  $result=mysqli_query($connect,$sql);
  if ($data=mysqli_fetch_assoc($result)) {
      $nbr_match_joue=$data["nbr"];
  }
  else {
      $nbr_match_joue=0;
  }
 //Nbr Matchs en cours 
  $sql="select count(Num_match) as nbr from echeancier where etat=1 and num_titre=".$num_titre.";";   
  $result=mysqli_query($connect,$sql);
  if ($data=mysqli_fetch_assoc($result)) {
      $nbr_match_en_cours=$data["nbr"];
  }
  else {
      $nbr_match_en_cours=0;
  }
   //Nbr matchs WO
  $sql="select count(Num_match) as nbr from echeancier where etat=3 and num_titre=".$num_titre.";";   
  $result=mysqli_query($connect,$sql);
  if ($data=mysqli_fetch_assoc($result)) {
      $nbr_match_wo=$data["nbr"];
  }
  else {
      $nbr_match_wo=0;
  }
  // Nombre de match à jouer
   $sql="select count(Num_match) as nbr from echeancier where not (etat in (3,2,1)) and spe<>'Pause' and num_titre=".$num_titre.";";   
  $result=mysqli_query($connect,$sql);
  if ($data=mysqli_fetch_assoc($result)) {
      $nbr_match_a_joue=$data["nbr"];
  }
  else {
      $nbr_match_a_joue=0;
  }
  //Calcul du plus long, plus cours et temps moyens
  $sql="select heure_debut,heure_fin, num_match from echeancier where etat=2 and num_titre=".$num_titre.";";   
  $result=mysqli_query($connect,$sql);
  $duree_min=9999999;
  $duree_max=0;  
  $temps_passe=0;
  $num_match_cours=0;
  $num_match_long=0;
  while ($data=mysqli_fetch_assoc($result)) {
      $duree=conv_temp($data["heure_fin"])-conv_temp($data["heure_debut"]);
      //Si durée=0 on n'en tient pas compte
      if ($duree!=0) {
                 if ($duree<$duree_min) {
                      $duree_min=$duree;
                      $num_match_cours=$data["num_match"];
                 }
                 if ($duree>$duree_max) {
                      $duree_max=$duree;
                      $num_match_long=$data["num_match"];
                }
                $temps_passe=$temps_passe+$duree;
      } else {
         $nbr_match_joue--; //durée=0 match non comptabilisé dans le calcul du temps moyen
      }
  }
  
  if ($nbr_match_joue>0) {
    $moy_sec=($temps_passe*60)/$nbr_match_joue;
  } 
  else {
     $moy_sec=0;
     $duree_min=0;
     $duree_max=0;
  } 
  $temps_moyen=ConvertSecondesEnHeures($moy_sec);
  $duree_max= ConvertSecondesEnHeures($duree_max*60);
  $duree_min=ConvertSecondesEnHeures($duree_min*60);
  $tab= array( "joues"=>$nbr_match_joue,
               "en_cours"=>$nbr_match_en_cours, 
               "wo"=>$nbr_match_wo,
               "restant"=>$nbr_match_a_joue,
               "temps_moyen"=>$temps_moyen,
               "temps_max"=>$duree_max,
               "temps_min"=>$duree_min,
               "num_long"=>$num_match_long, 
               "num_court"=>$num_match_cours
               );
  echo json_encode($tab);   
?>
