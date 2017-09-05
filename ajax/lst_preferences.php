<?php                   
/*****************************************************
 * Module qui retourne la liste des préférences
 * FU
 * 05/2014  
 *****************************************************/     
	include ("../connect.7.php");
	$filtre="";
	$donnee = array();
	$sql="SELECT  num_titre as id, pref_nom as value, pref_description as texte 
         FROM pref_titre";
	$result=mysqli_query($connect,$sql);
  
	while ($data=mysqli_fetch_assoc($result))  {   
		$donnee[]=$data;
	}
	echo json_encode($donnee);
?>