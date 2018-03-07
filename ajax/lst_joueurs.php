<?php

/* * ***************************************************
 * Module qui retourne la liste des joueurs pour le
 * l'échéancier personnel
 * Deux formats de retour en fontion du programme appelant
 *  value,label
 *  ou
 *  label,value   
 * *************************************************** */
session_start();

include ("../connect.7.php");
require '../loginconfig.5.php';
$filtre = "";
$donnee = [["value"=>$_admin_login,'id'=>0],
           ["value"=>$_user_login,'id'=>0]];
$term = isset($_GET["term"])?$_GET["term"]:"";

    if ($term != "") {
    $filtre = "  Joueur like '%" . $_GET["term"] . "%' ";
    }


$sql = "SELECT Joueur as value, Licences as id 
        FROM joueurs
        WHERE  $filtre  AND NOT ISNULL(Joueur)  GROUP BY `Joueur`,`Licences`;";
//echo $sql;
$result = mysqli_query($connect, $sql);

while ($data = mysqli_fetch_assoc($result)) {
    $donnee[] = $data;
}
echo json_encode($donnee);

