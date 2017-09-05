<?php

/*Ce module est chargé de retouner les couleurs etc
* des terrains dans la salle ainsi que celles de la salle et de l'échéancier
* issus des preferences
*/

//parametres de connection
include ("../connect.7.php");

$num_titre=$_GET["num_pref"];

$sql="SELECT * 
      FROM `pref_tbl_config_chrono` 
      WHERE `num_titre`=".$num_titre;
 $result=mysqli_query($connect,$sql);
 $data=mysqli_fetch_array($result);
 echo json_encode($data);
?>
