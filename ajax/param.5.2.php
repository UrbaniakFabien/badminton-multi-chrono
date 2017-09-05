<?php
/*******************************************************************************
 * Module d'enregistrement des caractéristiques des terrains
 *  Identification
 *  Top
 *  Left
 *  Orientation
 *Par Fu
 *12/2013
 *
 * 04/2014
 * Gestion du type de configuration a sauvegarder   
 *******************************************************************************/       
session_start();
$num_titre=$_SESSION["num_titre"];
/*******************************************
 * enregistrement de la position des terrains
 * *****************************************/  
 include ("../connect.7.php");
  
 $num="";           //identification du terrain
 $top="";           // Position Top
 $left="";          // Position Gauche
 $orientation="";   //Orientation
 $tab=$_POST["donnees"];
 $type_sauvegarde=isset($_POST["type_sauvegarde"]) ? $_POST["type_sauvegarde"]:"";
 $num_pref=isset($_POST["num_pref"]) ? $_POST["num_pref"]:"";
 
 // Si la sauvegarde concerne une preference alors on enregistre
 //avec le num_pref et non le num_titre
if ($type_sauvegarde!="") {
    $num_titre=$num_pref;
} 
 //On nettoie la table param
 $sql = "DELETE 
         FROM ".$type_sauvegarde."param
         WHERE num_titre=".$num_titre;
 $result=mysqli_query($connect,$sql);
 //puis on enregistre le parametrage des terrains
 foreach ($tab as $e_tab) {
    
     // Mémorise Nouveau parametrage
     $tbnum=explode("_",$e_tab["id"]);
     $num=$tbnum[1];               //identification du terrain
     $top=$e_tab["top"];           // Position Top
     $left=$e_tab["left"];         // Position Gauche
     $orientation=$e_tab["orient"];   //Orientation
     $sql="insert into ".$type_sauvegarde."param values (".$num.",".$top.",".$left.",'".$orientation."',".$num_titre.");";     
     $result=@mysqli_query($connect,$sql);
     if ($result==false) {
            die (mysqli_error()."<br><b>SQL:</b><br>$sql<br>");}
 }
?>