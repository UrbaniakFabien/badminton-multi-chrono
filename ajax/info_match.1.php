<?php

session_start();
$num_titre = $_SESSION["num_titre"];
/* Ce module est chargé de retouner l'etat de chaque match
 */

//parametres de connection
include ("../connect.7.php");

//Recherche le N° du dernier match de l'échéancier 

$sql = "SELECT MAX(num_match) as num 
        FROM echeancier 
        WHERE spe<>'Pause' AND num_titre=" . $num_titre;
$result = exec_commande($sql);
$num_rows = mysqli_num_rows($result);

$dernier_match = 0;
if ($num_rows > 0) {
    //Boucle pour réccuperer les réponses
    // et pour nettoyer la table tampon
    while ($data = mysqli_fetch_assoc($result)) {
        $dernier_match = $data["num"];
    }
}
//Recherche les informations du N° match en cours le plus grand

$sql = "SELECT Num_match, terrain, etat, heure_debut, heure_fin ,spe, tableau
        FROM echeancier 
        WHERE num_titre=" . $num_titre . " 
            and num_match=(
                          select max(num_match) as num 
                          from echeancier 
                          where (etat=1  or etat=2)  and num_titre=" . $num_titre . ")";
$result = exec_commande($sql);
$num_rows = mysqli_num_rows($result);

//Tableau de réponse
$reponse = [];
if ($num_rows > 0) {
    //Boucle pour réccuperer les réponses

    while ($data = mysqli_fetch_assoc($result)) {
        $reponse["encours"] = $data;
    }


// recherche du plus grand+1 qui n'est pas une Spe = pause ou un WO (etat=3)
    $i = $reponse["encours"]["Num_match"] + 1;
    $pause = true;
    While ($pause) {
        $pause = false;
        $sql = "SELECT Num_match, terrain, etat, heure_debut, heure_fin ,spe, tableau
        FROM echeancier 
        WHERE num_titre=" . $num_titre . " 
            and num_match=$i";

        $result = exec_commande($sql);
        $num_rows = mysqli_num_rows($result);
        if ($data = mysqli_fetch_assoc($result)) {
            if (($data["spe"] == 'Pause') || ($data["etat"] == 3 )) {
                $i++;
                $pause = true;
            }
        }
    }
    $reponse["encours"]["Num_match"] = (string) ($i - 1); //N° du plus grand match en cours ou de la pause qui le suit
} else {
    $reponse["encours"] = ["Num_match" => $dernier_match]; //il n'y a plus ou pas de match en cours
}


//Pour connaitre les matchs en attente dans le cas où on a avancé le lancement  de certain match
$sql = "SELECT num_match 
      FROM echeancier
      WHERE num_match < {$reponse["encours"]["Num_match"]} 
            AND 
            num_titre=$num_titre 
            AND etat=0
            and spe <> 'Pause'
      ORDER BY num_match";

$result = exec_commande($sql);
$num_rows = mysqli_num_rows($result);
$liste = ""; //liste des en attente
if ($num_rows > 0) {
    $sep = "";
    $liste = "Match(s) en attente : ";
    while ($data = mysqli_fetch_assoc($result)) {
        //On stocke les lignes dans le tableau
        // Cas de match mis en WO par erreur à la table de pointage 
        //si etat =-1 =>etat=0
        $liste .= $sep . $data["num_match"];
        $sep = " - ";
    }
}
$reponse["en_attente"] = $liste;
//si on est en fin de tournoi =>pas de match aprés le dernier !
if ($dernier_match == $reponse["encours"]["Num_match"]) {
    $reponse["encours"] = ["Num_match" => "Aucun"];
    $reponse["en_attente"] ="";
}
//Renvoi du tableau vers la page cliente
echo json_encode($reponse);
