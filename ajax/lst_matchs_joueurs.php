<?php
session_start();
$num_titre=$_SESSION["num_titre"];
	include ("../connect.7.php");
	$filtre="";
	$donnee = array();
	if (isset($_GET["licence"])) {
		$filtre=" and licences = '" .$_GET["licence"] . "' ";
	}
  //Extraction des matchs de la liste de pointage pour le joueur selectionné et pour le lieu date choisit
	$sql="select matchs from joueurs 
        WHERE num_titre=".$num_titre .$filtre . ";";
	$result=mysqli_query($connect,$sql);
	while ($data=mysqli_fetch_assoc($result))  {
    $tab_tmp=explode(",",$data["matchs"]);
    for ($i=0;$i<count($tab_tmp);$i++) {
        $donnee[]=$tab_tmp[$i]+0;
    }
				
	}
  echo json_encode($donnee);
?>