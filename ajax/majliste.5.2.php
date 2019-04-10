<?php
/*****************************************************************************
 * Mise a jour de la liste des joueurs
 * 
 *FU 
 *Mars 2013
 *  Reperage des matchs WO du joueurs WO dans l'échéancier => ajout d'un etat 3 = WO    
 *   et -1=etat modifié : permet le forçage de la prise en compte dns les requete de mise a jour de l'échéancier
 *FU Novembre 2013
 *  Remise à l'etat initial des matchs du joueur si passage de WO à absent  
 *FU Décembre 2013
 *  Traitement du cas de plusieurs joueurs WO sur le même match  
 *****************************************************************************/ 
include ("../connect.7.php");
set_time_limit(30);
  $num=substr($_POST["num"],3);
  $etat_joueur=$_POST["etat"];//0=joueur non pointé, 1=joueur pointé present, 2=joueur pointé WO ,3 = joueur absence autorisé
  $etat_match=0;              //0=match non joué, 1=match en cours, 2=match terminé, 3=match WO -1=Match modifié a prendre en compte lors du raffraichissement
  $commentaire=isset($_POST["commentaire"])? $_POST["commentaire"] :"";
  //Mise a jour de l'état du joueur
  $sql = "UPDATE joueurs 
          SET etat=" .$etat_joueur.",
              commentaire='".addslashes($commentaire)."' 
          WHERE num=".$num.";";
 exec_commande($sql);
  //Mise a jour de l'échéancier
  //Si WO alors mise à jours des match connu à l'état WO
  //Si retour à la position absent alors annule les matchs en WO
  if ($etat_joueur!=1) {
    $sql="SELECT matchs, num_titre 
          FROM joueurs
          WHERE num=".$num;
    $result=mysqli_query($connect,$sql);
    $data=mysqli_fetch_assoc($result);
    $num_titre=$data["num_titre"];
    if ($etat_joueur==2) {
      $etat_match=3;   //Match WO
    } 
    else {
      $etat_match=-1;   //Match état "modifié" pour forcer la prise en compte lors du rafraichissement de l'affichage écéancier
    }  
    $sql="UPDATE echeancier
          SET etat=".$etat_match."
          WHERE num_match in (".$data["matchs"]. ") and num_titre=".$num_titre;
   exec_commande($sql); 
    
    //Cas de joueurs WO sur le même match  (cas rare!!)
    //Recherche des autres joueurs en WO dans cette même liste de joueurs
    $sql="SELECT matchs
          FROM joueurs
          WHERE etat=2 
                AND num_titre=".$num_titre." 
                AND num<>".$num;
    $result=mysqli_query($connect,$sql);
    //Mise a jour dans echeancier des matchs en WO si pas déjà en WO.
    while ($data=mysqli_fetch_assoc($result)) {
         $sql="UPDATE echeancier
               SET etat=3
               WHERE num_match in (".$data["matchs"]. ") 
                    AND num_titre=".$num_titre." 
                    AND etat<>3" ;
        exec_commande($sql);
         //echo $sql.PHP_EOL; 
    }
  }
  
  mysqli_close($connect);
?>
