<?php
/*******************************************************************************
 * Creation d'un nouvel enregistrement dans la table pref_titre
 * et
 * retour du n° créé
 * FU
 * 05/2014
 * 
 * FU
 * 06/2014
 * Test si une preference exisqte avec le même nom et supprime si oui         
 *******************************************************************************/
 include ("../connect.7.php");
 $pref_nom=isset($_POST["pref_nom"]) ? $_POST["pref_nom"]:"";
 $pref_description=isset($_POST["pref_description"]) ? $_POST["pref_description"]:"";
 //Cherche si preference existe déjà
 $sql="SELECT num_titre 
       FROM pref_titre
       WHERE pref_nom='$pref_nom'";
 $result=mysqli_query($connect,$sql);
 //Si oui alors supprime dans pref_titre et pref_param
 if ($data=mysqli_fetch_assoc($result)) {
     $sql="DELETE
           FROM pref_titre
           WHERE num_titre=".$data["num_titre"];
     mysqli_query($connect,$sql); 
      //On nettoie la table param
     $sql = "DELETE 
             FROM pref_param
             WHERE num_titre=".$data["num_titre"];
     $result=mysqli_query($connect,$sql);  
 }
 //Ajout du nouvel enregistrement
 $sql="INSERT INTO pref_titre (`pref_nom`,`pref_description`) 
      VALUE ('".$pref_nom."','".$pref_description."');";
 mysqli_query($connect,$sql);
 $sql="SELECT max(num_titre) as num_pref 
       FROM pref_titre";
 $result=mysqli_query($connect,$sql);
 $data=mysqli_fetch_assoc($result);
 echo json_encode($data);     
?>