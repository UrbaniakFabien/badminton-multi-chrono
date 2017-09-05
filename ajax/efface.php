<?php
/***************************************************************
 * module de suppression des lignes dans echeancier ou joueurs
 * selon le num_titre passé
 * les titres correspondants sont supprimés s'il n'y a plus de lignes dans les deux tables pour le titre passé
 ***************************************************************/   
include ("../connect.7.php");
$num_titre= isset($_POST["num_titre"]) ? $_POST["num_titre"]:0;
$cible= isset($_POST["cible"]) ? $_POST["cible"]:"";

$tab["lst"]="joueurs";
$tab["ech"]="echeancier";
$filtre="";
if ($num_titre!=0) {
  $filtre=" WHERE num_titre=".$num_titre;  
}
//on supprime les lignes ciblées dans la table cible
$sql="DELETE
      FROM " .$tab[$cible] .$filtre;
     
mysqli_query($connect,$sql);
// on compte le nombre de lignes ciblées restantes dans les tables cibles potentielles
$sql="SELECT count(*) as nbr
      FROM ".$tab["ech"].$filtre;
     
$nbr=0;
$result=mysqli_query($connect,$sql);
if ($data=mysqli_fetch_assoc($result)) {
  $nbr=$data["nbr"];
  //S'il n'y a plus de ligne d'écheancier pour ce filtre,
  //On supprime le parametrage de l'échéancier et des chronos
  if ($nbr==0) {
      $sql="DELETE 
            FROM param ".$filtre;
      mysqli_query($connect,$sql);
      $sql="DELETE 
            FROM tbl_config_chrono ".$filtre;
      mysqli_query($connect,$sql);
     /*$sql="DELETE 
           FROM tbl_couleurs ".$filtre;
      mysqli_query($connect,$sql);*/
  }
}            
$sql="SELECT count(*) as nbr
      FROM ".$tab["lst"].$filtre;

$result=mysqli_query($connect,$sql);
if ($data=mysqli_fetch_assoc($result)) {
  $nbr+=$data["nbr"];
}

//si toutes les lignes sont effacées alors on supprime le lieu et date
if ($nbr==0) {
    $sql="DELETE 
          FROM titre ". $filtre;    
    mysqli_query($connect,$sql);
}
?>