<?php
session_start();
$num_titre=$_SESSION["num_titre"];
/***********************************
 * module de nettoyage du parametrage 
 * des position et orientation des terrains
 ****************************************/ 
  include("../connect.php");
  $sql = "DELETE 
          FROM `param`
          WHERE num_titre=".$num_titre;
  echo $sql;
  $result=mysqli_query($connect,$sql);
?>