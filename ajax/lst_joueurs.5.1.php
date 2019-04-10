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
$num_titre = $_SESSION["num_titre"];
include ("../connect.7.php");
$filtre = "";
$donnee = array();
$term = isset($_GET["term"])?$_GET["term"]:"";

    if ($term != "") {
    $filtre = " and Joueur like '%" . $_GET["term"] . "%' ";
    }

$format = isset($_GET["format"]) ? $_GET["format"] : 0;
$sql = "SELECT ";
if ($format == 0) {
    $sql .= "Joueur as value, Licences as id ";
    $donnee[] = array("value" => "Tous", "id" => 0);
} else {
    $sql .= "Joueur as label, Licences as value ";
    $donnee[] = array("label" => "Tous", "value" => 0);
}
$sql .= " FROM joueurs
          WHERE num_titre=" . $num_titre . $filtre . " AND NOT ISNULL(Joueur)  GROUP BY `Joueur`,`Licences`;";
//echo $sql;
$result =exec_commande( $sql);

while ($data = mysqli_fetch_assoc($result)) {
    $donnee[] = $data;
}
echo json_encode($donnee);
