<?php

/* * *********************************************************************
 * Impression de l'échéancier en cours au format PFD
 * FU
 * 2012
 * 
 *  Modification dans mc_table pour prendre en compte
 *  les couleurs par cellule
 *  le centrage vertical  
 *  la couleur du texte par cellule 
 *  
 * FU
 * 10/01/2014
 *  Gestion des nom de classe des spécialités en majuscule   
 * 
 * FU
 * 11/03/2014
 *  Reprise des couleurs passées en parametre 
 *  impression en fonction du type d'échéancier demandé 
 *   M (defaut) ou 
 *   C convocation
 *   avec decalage en format xxhmm  
 * ******************************************************************* */
session_start();
function html2rgb($color){
    if ($color[0] == '#')
    $color = substr($color, 1);
    
    if (strlen($color) == 6)
    list($r, $g, $b) = array($color[0].$color[1],
    $color[2].$color[3],
    $color[4].$color[5]);
    elseif (strlen($color) == 3)
    list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1],$color[2].$color[2]);
    else
    return false;
    
    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    
return array("r"=>$r,"g"=>$g,"b"=>$b);
}
/*******************************************************************************
 * Conversion heure en minutes
 * Recois un format xxhmm
 * Retourne mm  
 *******************************************************************************/ 
function heure_minute($heure_minute) {
  list($hh,$mm)=explode("h",$heure_minute);
  return $mm+(60*$hh);
}
/*******************************************************************************
 * Conversion minutes en heures et retour au format xxhxx
 *******************************************************************************/ 
function minute_heure($minute_heure){
    $hh=floor($minute_heure / 60);
    $mm=($minute_heure%60);
    return substr("00".$hh,-2)."h".substr("00".$mm,-2);
}
/*******************************************************************************
 * Reprise des couleurs passées en parametres
 * au lieu de reprendre les variables stockées dans la session
 *******************************************************************************/
$tab_couleur_str=explode("@",$_GET["tab_couleur"]);
foreach ($tab_couleur_str as $e_tab_couleur) {
    $tmp=explode("|",$e_tab_couleur);
    $tab_couleur[$tmp[0]]=html2rgb("#".$tmp[1]);
}
/*******************************************************************************
 * Parametrage type echeancier et decalage 
 *******************************************************************************/
$type_echeancier= isset($_GET["type_echeancier"])  ? $_GET["type_echeancier"] : "M";
$decalage_horaire=isset($_GET["decalage_horaire"]) ? $_GET["decalage_horaire"] : 0; 

/*******************************************************************************
 * Format de sortie 
 * Orientation
 *******************************************************************************/
 $format=isset($_GET["format"]) ?   $_GET["format"] : "A4";
 $orientation=isset($_GET["orientation"]) ?   $_GET["orientation"] : "L";
  
$couleur = isset($_GET["couleur"]) ? $_GET["couleur"] : "";
$num_titre = $_SESSION["num_titre"];
//table de conversion des signes pour le nom des classes
$sign = array(" ", "+", "-", "/");
$chg_sign = array("_", "plus", 'moins', "_");

include("connect.7.php");
include ("fpdf/mc_table.php");

//Titre donné à la page
$sql = "SELECT lieu_date from titre where num_titre=" . $num_titre;
$result = mysqli_query($connect,$sql);
$data = mysqli_fetch_assoc($result); 

//Titre en fonction du type d'échéancier demandé
if ($type_echeancier=="M") {
    $titre = "Echéancier " . $data["lieu_date"];
    $decalage=0;
}
else {
    $titre= "Echéancier de  C O N V O C A T I ON " . $data["lieu_date"];
    $decalage=heure_minute($decalage_horaire);
}
$titre = utf8_decode($titre);
date_default_timezone_set('Europe/Paris');

$font = "Arial"; //Police par defaut de tout le document
if ($orientation=="L") {
    if ($format=="A4") {
        $largeur_page = 287;
    }
    else {
        $largeur_page = 410;
    }
} else {
   if ($format=="A4") {
        $largeur_page = 200;
    }
    else {
        $largeur_page = 287;
    }
}
$pdf = new PDF_MC_Table();
//Pour activer la gestion des N° de pages au format page n / n pages
//la définition du pied de page est dans mc_table.php>Footer
$pdf->AliasNbPages();
$pdf->SetLineWidth(0.4);  //Epaisseur des bordures
if ($couleur!="En couleur") {
    $pdf->SetDrawColor(255);  //Couleur des bordures
}
else {
    $pdf->SetDrawColor(0);  //Couleur des bordures 
}
$pdf->SetTextColor(0);    //Couleur du texte par defaut
$pdf->SetFont($font, '', 10); //Taille du texte par defaut
$pdf->SetLeftMargin(4);
//Definition des table de parametrage du tableau
$tab_largeur = array(); // Table des largeur de colonne
$tab_align = array();   // Table des alignement dans les colonnes
$tab_fond = array();    // Table des couleurs de fond des cellules
$tab_textcolor = array(); // Table des couleur de texte par cellules
//format de la premiére colonne du tableau
$tab_largeur[] = 15;
$tab_align[] = "C";
//Couleurs des textes par défaut
$tab_textcolor[] = array("r" => 0, "g" => 0, "b" => 0);

//Parce que le nombre max de match horaire peut être n'importe ou si
//fusion d'échéancier :
$sql = "SELECT max(nbr_match) as max_nbr_match 
        FROM (select Horaire, count(num_match) as nbr_match
              from echeancier 
              where num_titre=" . $num_titre . " group by Horaire) tbl_tmp";
// on envoie la requête
$result = mysqli_query($connect,$sql); //or die('Erreur SQL !'.'<br>'.mysqli_error());
$data = mysqli_fetch_assoc($result);
$nbr_col = $data["max_nbr_match"]; //Nombre de lignes retournées = nombre de colonnes a imprimer
//On initialise les tables de mises en forme du tableau
$colonne_nombre = $nbr_col;
$colonne_largeur = ($largeur_page-15) / ($colonne_nombre);
for ($i = 1; $i <= $colonne_nombre; $i++) {
    $tab_largeur[] = $colonne_largeur;
    $tab_align[] = "C";
    $tab_textcolor[] = $tab_couleur["Couleur_texte"]; // Selon le parametrage
}
//Initialisation des largeurs
$pdf->SetWidths($tab_largeur);
//    des alignements horizontaux
$pdf->SetAligns($tab_align);
//    des couleurs si impression en couleur demandée alors texte en blanc 
if ($couleur != "En couleur") {
    $pdf->SetTextColors($tab_textcolor);
}
$marge = ($largeur_page - (($colonne_nombre * $colonne_largeur) + $tab_largeur[0])) / 2;
$marge=$marge+5;
//echo $largeur_page ,"-",$colonne_nombre,"-",$colonne_largeur,"-",$tab_largeur[0];
/* * ********************************************************
 * Page 1
 * ******************************************************* */
$pdf->AddPage($orientation,$format); //on passe les parametres
$pdf->SetLeftMargin($marge);                                                 
//Impression du titre
$pdf->SetFont('', 'B', 14);
$pdf->MultiCell(0, 5, $titre, 0, "C");
$pdf->SetFont('', '', 10);
$pdf->Ln(2);
//Début du tableau
// on crée la requête SQL
$sql = "SELECT * FROM `echeancier` 
        WHERE num_titre=" . $num_titre . "
        ORDER BY `Horaire`,`num_match`";
// on envoie la requête
$result = mysqli_query($connect,$sql); //or die('Erreur SQL !'.'<br>'.mysqli_error());
//Flag pour indiquer que la boucle  tourne pour la premiere  fois =>on connait alors le nombre de colonne de l'echeancier
$prem = true;
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        // on fait une boucle qui va faire un tour pour chaque enregistrement
        $anc_horaire = "";
        $go = ($data = mysqli_fetch_assoc($result));
        while ($go == true) {
            $ligne = array();
            $tab_fond = array();
            $ligne[] =  minute_heure( heure_minute($data["horaire"])-$decalage); //$data["horaire"];
            $tab_fond[] = $tab_couleur["horaire"];
            $anc_horaire = $data["horaire"];
            $nbr_col = 1;
            //Boucle sur même horaire => une ligne de l'echeancier
            while ($anc_horaire == $data["horaire"]) {
                $ligne[] = utf8_decode("Match N° " . $data["num_match"] . "\n" . trim($data["spe"]) . "\n" . trim($data["tableau"]));
                $tab_fond[] = $tab_couleur[strtoupper(trim(str_replace($sign, $chg_sign, rtrim($data["spe"]))))];
                $nbr_col++;
                $go = ($data = mysqli_fetch_assoc($result));
            }

            //On test pour savoir si on a une ligne complete
            if (count($ligne) < $colonne_nombre) {
                //si non on complete avec des cases vides.
                for ($i = count($ligne); $i <= $colonne_nombre; $i++) {
                    $ligne[] = "";
                }
            }

            if ($couleur != "En couleur") {
                $pdf->SetFonds($tab_fond);
            }
            $pdf->Row($ligne);
            unset($ligne);
        }
        // on ferme la connexion à mysql
        mysqli_close($connect);
        //On previsualise l'échéancier
        $pdf->Output();
    }
}


