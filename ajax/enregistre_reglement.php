<?php

/* 
 * Enregistrement du réglement par joueurs
 */

include_once '../connect.7.php';
foreach  ($_GET as $key=> $value) {
    $$key = $value;
}
//Mise a jour du joueur
$sql = "UPDATE tbl_regl_joueurs SET reg_joueurs_regle = $reg_joueurs_regle , reg_mode_reglement=$reg_mode_reglement WHERE reg_joueurs_id = $reg_joueurs_id";
echo $sql;
$result =exec_commande($sql);

//mise a jour du club
$sql = "SELECT reg_joueurs_id_fk_club,SUM(reg_joueurs_montant) as total_regle
        FROM tbl_regl_joueurs
        WHERE reg_joueurs_id_fk_club =(SELECT reg_joueurs_id_fk_club from tbl_regl_joueurs WHERE reg_joueurs_id = $reg_joueurs_id) and reg_joueurs_regle = 1
        GROUP BY reg_joueurs_id_fk_club";
$query =exec_commande( $sql);
$data = mysqli_fetch_assoc($query);
$sql = "UPDATE tbl_regl_clubs SET reg_deja_regle = {$data["total_regle"]} WHERE reg_id_club = {$data["reg_joueurs_id_fk_club"]}";
$query =exec_commande( $sql);
