<?php

/*Ce module est chargé de retouner les positions
* des terrains dans la salle ainsi que celles de la salle et de l'échéancier
* issus des preferences
*/

//parametres de connection
include ("../connect.7.php");

$num_titre=$_GET["num_pref"];

$sql="select * from pref_param 
      WHERE num_titre=".$num_titre."
      ORDER BY num desc";
$result=mysqli_query($connect,$sql);
$num_rows=mysqli_num_rows($result); 

//Tableau de réponse
$reponse=Array();
if ($num_rows>0) {
    //Boucle pour réccuperer les réponses
    // et pour nettoyer la table tampon
    while ($data=mysqli_fetch_assoc($result)) {
      //On stocke les lignes dans le tableau
      $reponse[]=$data;
     
    }
}
    //Renvoi du tableau vers la page cliente
     echo json_encode($reponse);
?>
