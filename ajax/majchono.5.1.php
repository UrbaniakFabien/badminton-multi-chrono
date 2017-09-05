<?php
session_start();
/***********************************************
 * Lecture des données de parametrages du chrono
 * *********************************************/ 
$num_titre=$_SESSION["num_titre"];
 include ("../connect.7.php");
 $sql="select * from tbl_config_chrono where num_titre=".$num_titre;
 $result=mysqli_query($connect,$sql);
 $data=mysqli_fetch_array($result);
 echo json_encode($data);
?>
