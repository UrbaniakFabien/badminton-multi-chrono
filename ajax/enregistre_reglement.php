<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once '../connect.7.php';
foreach  ($_GET as $key=> $value) {
    $$key = $value;
}
//Mise a jour du joueur
$sql = "UPDATE tbl_regl_joueurs SET reg_joueurs_regle = $reg_joueurs_regle WHERE reg_joueurs_id = $reg_joueurs_id";
$result = mysqli_query($connect,$sql);

//mise a jour du club
$sql = "SELECT reg_joueurs_id_fk_club,SUM(reg_joueurs_montant) as total_regle
        FROM tbl_regl_joueurs
        WHERE reg_joueurs_id_fk_club =(SELECT reg_joueurs_id_fk_club from tbl_regl_joueurs WHERE reg_joueurs_id = $reg_joueurs_id) and reg_joueurs_regle = 1
        GROUP BY reg_joueurs_id_fk_club";
$query = mysqli_query($connect, $sql);
$data = mysqli_fetch_assoc($query);
$sql = "UPDATE tbl_regl_clubs SET reg_deja_regle = {$data["total_regle"]} WHERE reg_id_club = {$data["reg_joueurs_id_fk_club"]}";
$query = mysqli_query($connect, $sql);
