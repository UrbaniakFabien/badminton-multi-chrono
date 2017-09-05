<?php
session_start();
/***************************************************************
 * Retourne la liste des match en cours en cas d'interruption de service
 */
$num_titre=$_SESSION["num_titre"];
	include ("../connect.7.php");
	
	$donnee = array();
	
  //Extraction des matchs encours pour le lieu date choisit
	$sql = "SELECT num_match,terrain FROM `echeancier` 
                WHERE num_titre=" .$num_titre."
                      and etat=1 ";

// on envoie la requête
$result = mysqli_query($connect,$sql); //or die('Erreur SQL !'.'<br>'.mysqli_error());
     
while ($data=mysqli_fetch_assoc($result))  {
   $donnee[]=$data;
				
	}
  echo json_encode($donnee);
?>
