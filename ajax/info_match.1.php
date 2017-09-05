<?php
session_start();
$num_titre=$_SESSION["num_titre"];
/*Ce module est chargé de retouner l'etat de chaque match
*/

//parametres de connection
include ("../connect.7.php");



$sql="SELECT Num_match, terrain, etat, heure_debut, heure_fin ,spe, tableau
      FROM echeancier 
      WHERE num_titre=".$num_titre ." 
            and num_match=(
                          select max(num_match) as num 
                          from echeancier 
                          where etat=1 and num_titre=".$num_titre.")";
$result=mysqli_query($connect,$sql);
$num_rows=mysqli_num_rows($result); 

//Tableau de réponse
$reponse=Array();
if ($num_rows>0) {
    //Boucle pour réccuperer les réponses
    // et pour nettoyer la table tampon
    while ($data=mysqli_fetch_assoc($result)) {
      //On stocke les lignes dans le tableau
      // Cas de match mis en WO par erreur à la table de pointage 
      //si etat =-1 =>etat=0
      if ($data["etat"]==-1) {$data["etat"]=0;}
      $reponse[]=$data;
     
    }
}
    //Renvoi du tableau vers la page cliente
     echo json_encode($reponse);
?>
