<?php
session_start();
function convert_heure_str_minute($horaire) {
       list($h,$m) = explode("h",$horaire);
       return ($h*60)+$m;
}
/***************************************************************
 * Reprise des horaires pour mise a jour de l'échéancier avec les valeurs réélles
 * *************************************************************/ 
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;
 include ("../connect.7.php");
//Liste des horaire théorique et plus petite heure de début de match pour cet horaire théorique pour les match en cours ou joués
// +
//Liste des horaires pour les matchs non joués qui ne sont pas dans la liste des matchs en cours ou joués
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
      //On ne traite que les horaires pour les tranches non commencées
      
      if ($ecart==0) {    //Calcul de l'ecart théorique entre deux tranches horaire  : ne se calcul qu'une fois
          //Horaire tranche précédente
          $t_precedent = convert_heure_str_minute($tab[$i-1]["horaire"]);
          //Horaire tranche en cours          
          $t_courant   = convert_heure_str_minute($tab[$i]["horaire"]);
          //Ecart par defaut
          $ecart =  $t_courant-$t_precedent;
      } 
      $t_reel =convert_heure_str_minute($tab[$i-1]["reel"]);
      $t_reel+= $ecart;
      $tab[$i]["reel"]=sprintf("%02dh%02d",(int)($t_reel/60), $t_reel%60 );
    }
  }
 //fur
 //12/12/2017
 //Recalcul en cas de besoin du début d'une tranche si elle débute avant la tranche précédente (cas de matchs lancés par avance)
  for ($i=1;$i<count($tab);$i++) {
     $t_reel_precedent = convert_heure_str_minute($tab[$i-1]["reel"]);
     $t_reel_courant   = convert_heure_str_minute($tab[$i]["reel"]);
     if ($t_reel_courant < $t_reel_precedent) {
         $t_reel_courant = $t_reel_precedent + $ecart;
         $tab[$i]["reel"]=sprintf("%02dh%02d",(int)($t_reel_courant/60), $t_reel_courant%60 );
     }
  }
  echo json_encode($tab);

