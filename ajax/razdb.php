<?php
 /**************************************************
  * module pour detruire la base dans son ensemble
  * FU 12/2013
  * 
  *A n'utiliser que si l'on veut réinitialiser la base
  **************************************************/
  session_start();
  include ("../connect.7.php");
  $sql="DROP DATABASE ".$db;
  mysqli_query($connect,$sql);
  unset($_SESSION['_login']);
  unset($_SESSION['_pass']);
  unset($_SESSION["num_titre"]);        
?>
