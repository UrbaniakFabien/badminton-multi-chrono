<?php

/* * ******************************************
 * Liste des titres
 * **************************************** */
include ("../connect.7.php");
//term -> ce que l'on cherche
$term = isset($_GET["term"]) ? $_GET["term"] : "";
//action -> si raz alors 'Tous' apparait dans la liste retournée
//action -> si lst et cible echeancier alors 'Aucun' apparait dans la liste retournée
$action = isset($_GET["action"]) ? $_GET["action"] : "";
//cible ->lst ou ech selon que raz s'applique a liste joueurs ou echeancier
//sert a affiner les titres en fonction des cibles
//cible=ech : On retourne la liste des titre si existe un echéancier correspondant
//cible=lst : On retourne la liste des titre si existe une liste correspondante
$cible = isset($_GET["cible"]) ? $_GET["cible"] : "";
//format permet de particulariser le format de retour
$format = isset($_GET["format"]) ? $_GET["format"] : 0;

$filtre = "";
if ($term != "") {
    $filtre = "WHERE lieu_date like '%" . $term . "%' ";
}

if ($format == 0) {
    $sql = "SELECT titre.num_titre as id, `lieu_date` as value ";
} else {
    $sql = "SELECT titre.num_titre as value, `lieu_date` as label ";
}
//Si pas de cible alors on ne prend que la table titre
if ($cible == "") {
    $sql.="FROM `titre` " . $filtre . " ORDER BY lieu_date";
} else {
    //Cible=ech =>on regarde si les titres ont des lignes d'échéancier
    if ($cible == "ech") {
        $sql.="FROM `titre` 
                      inner join echeancier on (titre.num_titre=echeancier.num_titre) " . $filtre . " group by lieu_date,titre.num_titre";
    } else {
        //sinon on cherche si les titre on une liste de joueurs
        $sql.="FROM titre inner join joueurs on (titre.num_titre=joueurs.num_titre) " . $filtre . " group by lieu_date,titre.num_titre";
    }
}
$result = mysqli_query($connect,$sql);
$donnee = array();
/* Ajout du choix 'Tous' si demande de RAZ */
if ($action == "raz") {
    if ($format == 0) {
        $donnee[] = array("id" => "0", "value" => "Tous");
    } else {
        $donnee[] = array("value" => "0", "label" => "Tous");
    }
}
/* Ajout du choix 'Aucun' si demande de Liste echeancier */
if (($action == "lst") and ($cible == "ech")) {
    if ($format == 0) {
        $donnee[] = array("id" => "-1", "value" => "Aucun");
    } else {
        $donnee[] = array("value" => "-1", "label" => "Aucun");
    }
}
while ($data = mysqli_fetch_assoc($result)) {
    $donnee[] = $data;
}
echo json_encode($donnee);
?>
