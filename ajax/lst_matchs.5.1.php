<?php
/*******************************************************************************
 * Module qui retourne pour un joueur précis
 * la liste des matchs en fonction des N° de matchs dans la liste de pointage
 * et
 * la liste des matchs correspondant au tableaux demandés par ce joueur
 ******************************************************************************/    
session_start();
$num_titre=$_SESSION["num_titre"];
	include ("../connect.7.php");
	$filtre="";  //Filtre dans les requete
	$lst_match=array(); //Tableau des n° de matchs
	if (isset($_GET["licence"])) {
		$filtre=" AND licences = '" .$_GET["licence"] . "' ";
	}
  //Extraction des matchs de la liste de pointage pour le joueur selectionné et pour le lieu date choisit
	$sql="select matchs from joueurs 
        WHERE num_titre=".$num_titre .$filtre . ";";
	$result=mysqli_query($connect,$sql);
	while ($data=mysqli_fetch_assoc($result))  {
		$lst_match[]=$data["matchs"];		
	}
  
	if (count($lst_match)!=0) {
		 //Extraction des informations sur ces premiers matchs depuis l'échéancier
     // identification du/des Tableau(x) et poule(s) auxquels participe le joueur
		 $sql="SELECT num_match, spe, tableau 
  			   FROM echeancier 
  			   WHERE num_match in (".implode(',',$lst_match).") and num_titre=".$num_titre.";";
		 $result=mysqli_query($connect,$sql);
		 unset($lst_match);
		 while ($data=mysqli_fetch_assoc($result)){
		    $tab_ref=explode(" ",$data["tableau"]); //extraction de la lettre de la poule
		    //Matchs dans la specialité et pour la poule de référence
		    $sql="SELECT num_match , tableau , spe
  				   FROM echeancier 
  				   WHERE spe ='".$data["spe"]."' and  tableau like '%poule ".$tab_ref[1]."%' and num_titre=".$num_titre.";";
  			$match_result=mysqli_query($connect,$sql);
  			while ($data_match=mysqli_fetch_assoc($match_result)) {
  				$lst_match[]=$data_match["num_match"];
  				
  			}
  		    //Dernier matchs sortie de poule dans la specialité
  		    $sql="SELECT num_match 
  				   FROM echeancier 
  				   WHERE spe ='".$data["spe"]."' and  not (tableau like '%poule%') and num_titre=".$num_titre.";";
  			$match_result=mysqli_query($connect,$sql);
  			while ($data_match=mysqli_fetch_assoc($match_result)) {
  				$lst_match[]=$data_match["num_match"];
  				
  			}
		}
	}
	echo json_encode($lst_match);
