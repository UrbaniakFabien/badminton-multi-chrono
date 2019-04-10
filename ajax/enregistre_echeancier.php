<?php
 /******************************************************************************
  * Module de generation d'un echéancier non importé
  * Fu
  * 02/2014
  ******************************************************************************/
  include ("../connect.7.php");
  foreach ($_POST as $key=>$valeur) {
      $$key=isset($valeur) ? $valeur :"";   
  }
  /*****************************************************************************
   * Enregistrement lieu et date du tournoi
   * Reccuperation du num_titre
   *****************************************************************************/        
  $lieu_date=$lieu." le " .$date;
  $sql="SELECT `num_titre` FROM `titre` WHERE  lieu_date='".$lieu_date."'";
  $result=mysqli_query($connect,$sql);
  
  if ($data=mysqli_fetch_assoc($result)) {
    $num_titre=$data["num_titre"];
    $sql="DELETE 
          FROM echeancier
          WHERE num_titre=".$num_titre;
    
   exec_commande($sql);//On supprime si les info existent déjà dans echeancier      
  } 
  else {
    $sql="INSERT INTO titre (lieu_date)
          VALUES ('".addslashes($lieu_date)."');";
         
    $result=mysqli_query($connect,$sql);
    $sql="SELECT MAX(num_titre) as id from titre;";
    $result=mysqli_query($connect,$sql);
    $data=mysqli_fetch_assoc($result);
    $num_titre=$data["id"];
  
  }
  /*****************************************************************************
   * generation de l'échéancier
   *****************************************************************************/ 
   $horaire=$heure_debut;
   $num_match=$premier_match;
   $spe="";
   $tableau="";
   $tab_pause=explode(";",$liste_pauses);
   //Boucle sur nombre de match        
  for ($nbr_match=1;$nbr_match<=$nombre_match;$nbr_match++)  {
      //Boucle sur nbr de terrain
     for ($terrain=1;$terrain<=$nombre_terrain && $nbr_match<=$nombre_match;$terrain++) {
      //Traitement des pauses
       if (in_array($num_match,$tab_pause,false)==true) {
              $spe="Pause";
       }
       else {
              $spe="";
       }
       //Enregistrement de la 'case' 
       $sql="INSERT INTO `echeancier` (num_titre,`Horaire`, `num_match`, `spe`, `tableau`, `etat`, heure_debut, heure_fin)
                               VALUES (".$num_titre.",'".str_replace(":","h",$horaire)."','".$num_match."','".$spe."','".addslashes($tableau)."',0,'00h00','00h00')";
      exec_commande($sql);
       $num_match++;
       $nbr_match++;
    }
     $nbr_match--;
    //Calcul de la nouvelle tranche horaire
    $date=new DateTime($horaire);
    $date->add(new DateInterval('PT'.$temps_match.'M'));
    $horaire=$date->format('H:i');
    
  }
       
?>