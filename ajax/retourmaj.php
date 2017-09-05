<?php
/*Ce module est chargé de retouner l'etat  de chaque joueurs
*/

//parametres de connection
include ("../connect.7.php");



$sql="select num, etat,  commentaire  from joueurs order by num"; //Trié dans le même ordre que le tableau original
$result=mysqli_query($connect,$sql);
$num_rows=mysqli_num_rows($result); 

//Tableau de réponse
$reponse=Array();
if ($num_rows>0) {
    //Boucle pour réccuperer les réponses
    // et pour nettoyer la table tampon
    while ($data=mysqli_fetch_assoc($result)) {
      //On stocke les lignes dans le tableau
      if ($data["etat"]==null) {$data["etat"]="0";}
      $reponse[]=$data;
     
    }
}
    //Renvoi du tableau vers la page cliente
     echo json_encode($reponse);
?>
