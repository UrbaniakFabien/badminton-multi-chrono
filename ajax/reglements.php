<?php

/*
 * retourne le detail des dû du club
 * 
 * FUR
 * 07/2017 
 */
include_once '../connect.7.php';
$id = $_GET["id"];
$sql = "SELECT reg_joueurs_id,reg_joueurs_nom,reg_joueurs_montant,reg_joueurs_regle,reg_mode_reglement
        FROM tbl_regl_joueurs
        WHERE reg_joueurs_id_fk_club =(SELECT reg_joueurs_id_fk_club from tbl_regl_joueurs WHERE reg_joueurs_id = $id) 
        ORDER BY reg_joueurs_nom";
$query = mysqli_query($connect, $sql);
$ligne = "<div id='frm_reg'><table><thead><tr><th>Nom Joueur</th><th>Montant</th><th>Réglement</th></tr></thead><tbody>";
$total = 0;
$total_regle = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $ligne .= "<tr>";
    foreach ($row as $key => $value) {
        if (($key != "reg_joueurs_id") && ($key !="reg_mode_reglement")) {
            $ligne .= "<td style='text-align:center;'>";
            if ($key == "reg_joueurs_regle") {
                $ligne .= "<img  src='../images/" . ($value == 1 ? "regle.png" : "en_attente.png") . "' style='width:25%;' class='saisie_reglement' data-id_reglement='{$row["reg_joueurs_id"]}' data-mode_reglement='{$row["reg_mode_reglement"]}'/>";
                  $total_regle += $row["reg_joueurs_montant"]*$value; 
            } else {
                $ligne .= utf8_encode($value);
            }
            $ligne .= "</td>";
        }
    }
    $ligne .= "</tr>";
    $total +=$row["reg_joueurs_montant"];
}
$ligne .="<tr><td>&nbsp;</td><td style='text-align:right;'>&nbsp;</td><td style='text-align:right;'>&nbsp;</td></tr>";
$ligne .="<tr><td>TOTAL CLUB</td><td style='text-align:right;'>$total</td><td style='text-align:right;'>$total_regle</td></tr>";
$ligne .= "</tbody></table><div>";
echo $ligne;
