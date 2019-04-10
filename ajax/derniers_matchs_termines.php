<?php
session_start();
/***************************************************************
 * Retourne la liste derniére heure de fin en cas d'interruption de service
 */
$num_titre=$_SESSION["num_titre"];
	include ("../connect.7.php");
	
	$donnee = array();
	
  //Extraction des matchs encours pour le lieu date choisit
	
 $sql = "select max(h_f) as h_fin,terrain 
         from (select right(concat(\"0\",trim(heure_fin)),5) as h_f,terrain  
               from echeancier 
               where etat=2 and num_titre=".$num_titre.") as tmp\n"
      . "group by terrain;";
// on envoie la requête

$result =exec_commande($sql); //or die('Erreur SQL !'.'<br>'.mysqli_error());
     
while ($data=mysqli_fetch_assoc($result))  {
         $donnee[]=$data;	
}
  echo json_encode($donnee);
?>
