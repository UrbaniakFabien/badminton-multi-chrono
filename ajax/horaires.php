<?php
session_start();
/***************************************************************
 * Reprise des horaires pour mise a jour de l'échéancier avec les valeurs réélles
 * *************************************************************/ 
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;
 include ("../connect.7.php");
 $sql="SELECT `horaire`, min(`heure_debut`) as reel
       FROM `echeancier`
       WHERE etat in (1,2)
              AND `num_titre` = $num_titre
       GROUP BY `horaire`
      UNION
      SELECT `horaire`, horaire as reel
      FROM `echeancier`
      WHERE etat =0
            and `num_titre` = $num_titre
            and horaire not in (SELECT `horaire`
                                FROM `echeancier`
                                WHERE etat in (1,2)
                                and `num_titre` =$num_titre)
      GROUP BY horaire
      ORDER BY `horaire` ASC ";
  $result = mysqli_query($connect,$sql);
  while ($data=mysqli_fetch_assoc($result)) {
    $data["horaire"] = trim($data["horaire"]);
    $data["reel"]    = trim($data["reel"]);
    $tab[]=$data;
  }
  $ecart=0;
  for ($i=1;$i<count($tab);$i++) {
    if ($tab[$i]["reel"]==$tab[$i]["horaire"]) {
      //Temps réél
      
      if ($ecart==0) {    //Calcul de l'ecart théorique entre deux tranche horaire ne se calcul qu'une fois
          //Temps précédent
          list($h,$m) = explode("h",$tab[$i-1]["horaire"]);
          $t_precedent = ($h*60)+$m;
          //Temps courant
          list($h,$m) = explode("h",$tab[$i]["horaire"]);
          $t_courant = ($h*60)+$m;
          //Ecart par defaut
          $ecart =  $t_courant-$t_precedent;
      } 
      
      list($h,$m) = explode("h",$tab[$i-1]["reel"]);
      $t_reel =($h*60)+$m;
      $t_reel+= $ecart;
      
      $tab[$i]["reel"]=sprintf("%02dh%02d",(int)($t_reel/60), $t_reel%60 );
    }
  }
  echo json_encode($tab);
?>