<?php
session_start();
/***************************************************************
 * Enregistrement de la configuration générale des chronos
 * *************************************************************/ 
$num_titre=isset($_SESSION["num_titre"])?$_SESSION["num_titre"] : 0;
 include ("../connect.7.php");
 // Valeurs par défaut
 $t_l="00FF00"; //couleurs : terrain libre
 $t_o="#FA8072";//Occupé   
 $t_neutre="#A7BDE9"; //couleur terrain neutre
 $c_s="#ffffff";//salle
 $tp1="2:00";   //tempo 1
 $tp2="3:00";   //tempo 2
 $sens="1";     //sens de comptage
 $zoom=0;       //valeur du zoom
 $son=0;        //Joue son
 $info_bulle=0;
 foreach($_POST as $key=>$e_POST) {
    $$key=$e_POST;
 }
 // Si la sauvegarde concerne une preference alors on enregistre
 //avec le num_pref et non le num_titre
if ($type_sauvegarde!="") {
    $num_titre=$num_pref;
}
 $sql="DELETE 
       FROM ".$type_sauvegarde."tbl_config_chrono
       WHERE num_titre=".$num_titre;
 mysqli_query($connect,$sql);

 $sql = "INSERT ".$type_sauvegarde."tbl_config_chrono values('".$t_l."', 
                                            '".$t_o."',
                                            '".$t_neutre."', 
                                            '".$c_s."',
                                            '".$tp1."',
                                            '".$tp2."',
                                            '".$sens."',
                                             ".$zoom.",
                                             ".$son.",
                                             ".$num_titre.",
                                             ".$info_bulle.");";
mysqli_query($connect,$sql); 
echo $sql;
?>
