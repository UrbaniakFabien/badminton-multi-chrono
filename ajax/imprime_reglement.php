<?php

/*
 * Impression  des reglements
 * FU
 * 07/2017
 */
include_once '../connect.7.php';
include_once "../fpdf/mc_table.php";

function html2rgb($color) {
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array($color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return false;
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array("r" => $r, "g" => $g, "b" => $b);
}

$pdf = new PDF_MC_Table();
//Pour activer la gestion des N° de pages au format page n / n pages
//la définition du pied de page est dans mc_table.php>Footer
$pdf->AliasNbPages();
$pdf->SetLineWidth(0.4);  //Epaisseur des bordures
$pdf->SetFont('Arial', '', 10); //Taille du texte par defaut
$pdf->SetLeftMargin(4);
//Initialisation des largeurs
$pdf->SetWidths(array(45, 25, 25,25));
//    des alignements horizontaux
$pdf->SetAligns(array("L", "R", "R","C"));

$sql = "SELECT reg_joueurs_club,reg_joueurs_nom,reg_joueurs_montant,reg_joueurs_regle,reg_mode_reglement
        FROM tbl_regl_joueurs
        
        ORDER BY reg_joueurs_club, reg_joueurs_nom";
$query =exec_commande( $sql);

$total = 0;
$total_regle = 0;
$club = "";
while ($row = mysqli_fetch_row($query)) {
    if ($club != $row[0]) {
        if ($club != "") {
            $pdf->Row(array("Total : ", $du, $regle));
        }
        $pdf->AddPage("P"); //nouvelle page 
        $pdf->Row(array("Club : ", $row[0]));
        $pdf->Row(array("Nom Joueur", utf8_decode("Montant Dû"), utf8_decode("Montant Réglé"),("Mode")));
        $club = $row[0];
        $du = 0;
        $regle = 0;
    }
    if ($row[3] != 1) {
        $couleur_fond = html2rgb("#FF0000");
        $row[3] = "Non";
    } else {
        $couleur_fond = html2rgb("#54F98D");
        $regle += $row[2];
        $row[3] = "Oui";
    }
    $pdf->SetFonds(array($couleur_fond, $couleur_fond, $couleur_fond, $couleur_fond));
    $pdf->Row([$row[1], 
               $row[2], 
               $row[3], 
               utf8_decode($row[3]=="Oui"?($row[4]==1?"Chéque":"Espéces"):"")]);
    $pdf->SetFonds(array());
    $du += $row[2];
}
if ($club != "") {
    $pdf->Row(array("Total : ", $du, $regle));
}
$doc = $pdf->Output('S');
echo base64_encode($doc);
