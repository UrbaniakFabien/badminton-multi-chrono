<?php

/*****************************************************************************
 * module de generation de l'échéancier commun à
 * mon_echeancier.x.x.php
 * chrono_echeancier.x.x.php
 * echeancier.x.x.php
 * FU
 * 12/2013
 * 
 * Fu
 * 10/01/2013
 * traitement des class et des specialité en majuscule
 * FU
 * 01/04/2014
 * Gestion horaire des matchs si échéancier de convocation ou match       
 * ************************************************************************** */
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
$mois=array("janvier","février","mars","avril","mai","juin","juillet","aôut","septembre","octobre","novembre","décembre");
$month=array("january","febuary","march","april","may","june","july","august","september","october","november","december");
$_SESSION["num_titre"] = $num_titre; //Mémorise pour la session le N° de lieu date en cours
$path=isset($path)? $path : "";
include($path."connect.7.php");
include($path."couleurs_ech.5.2.php");
$tab_classe = str_replace(" ", "", str_replace(".", "','", $lst_style)) . "'"; //Chaines passée comme tableau a Java pour le changement des couleurs de l'échéancier 
$tab_classe = substr($tab_classe, 2);
$sql = "SELECT * 
        FROM titre 
        WHERE num_titre=" . $num_titre;
$result = mysqli_query($connect,$sql);
$data = mysqli_fetch_assoc($result);
$site=$data["lieu_date"];
//Traitement des données pour decalage horaire : par defaut 00h30
$decalage_horaire=($data["decalage_horaire_convocation"]!="") ? $data["decalage_horaire_convocation"]:"00h30";
/*Si type demandé=Convocation on test si date d'échéancier>date du jour
    si oui alors on affiche léchéancier en horaire de convocation
    si non on affiche l'échéancier en heure de match
*/
if ($type_echeancier=="C") {
    $date_ech=substr($data["lieu_date"],(strripos($data["lieu_date"],"le")+3));
    if (strtotime(str_replace($mois,$month,$date_ech))>time()) {
        $titre= "Echéancier de <span class='titre'>CONVOCATION</span> " . $data["lieu_date"];
        $decalage=heure_minute($decalage_horaire);
    }
    else {
        $type_echeancier="M";
  }
}

if ($type_echeancier=="M") {
    $titre = "Echéancier " . $data["lieu_date"];
    $decalage="0";
}


//Valeur par défaut pour l'échéancier
$tableau = "<table><tr><td>Pas d'information d'échéancier ! Importez un échéancier pour utiliser cette page </td></tr></table>";

// on crée la requête SQL avec le N° choisi
$sql = "SELECT * FROM `echeancier` 
                where num_titre=" . $num_titre . "
                order by `Horaire`,`num_match`";

// on envoie la requête
$result = mysqli_query($connect,$sql); //or die('Erreur SQL !'.'<br>'.mysqli_error());
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        
        // on fait une boucle qui va faire un tour pour chaque enregistrement
        $anc_horaire = "";
        $nbr_col_max = 0;
        $ecart = 0; //ecart entre deux tranches horaire
        $tableau = "<table id='echeancier' style='width:100%'>";
        $go = ($data = mysqli_fetch_assoc($result));
        while ($go == true) {
            $tableau.="<tr id='" . trim($data["horaire"]) . "'><td class='horaire' >" .minute_heure( heure_minute($data["horaire"])-$decalage) . "</td>";
            if (($anc_horaire!="") && ($ecart==0)) {
              $ecart=heure_minute($data["horaire"])-heure_minute($anc_horaire); //Calcul de l'écart
            }
            $anc_horaire = $data["horaire"];
            $nbr_col = 0;
            while ($anc_horaire == $data["horaire"]) {
                $nbr_col++;
                if ($data["etat"] == -1) {
                    $data["etat"] = 0;
                }
                $tableau.="<td id='num_" . $data["num_match"] . "' class='case ";
                if (($data["etat"] == 0) || ($data["etat"] == 3)) {
                    $tableau.=strtoupper(trim(str_replace($sign, $chg_sign, rtrim($data["spe"]))));
                    if ($data["etat"] == 3) {
                        $tableau.=" etat_" . $data["etat"];
                    }
                } else {
                    $tableau.="etat_" . $data["etat"];
                }

                $tableau.="' >
                                        Match N° " . $data["num_match"] . "<br />";
                if (strpos($data["spe"], "Pause") === false) {
                    $tableau.="<span style='display:";
                    if ($data["terrain"] > 0) {
                        $tableau.="block";
                    } else {
                        $tableau.="none";
                    }
                    $tableau.=";' class='terrain'>Terrain N° <span class='num_match' id='input_num_" . $data["num_match"] . "' >" . $data["terrain"] . "</span><br /></span>
                                        <span>";
                    if ($data["heure_debut"] != "00h00") {
                        $tableau.= "D&eacute;but : " . $data["heure_debut"] . "<br />";
                    }
                    $tableau.="</span><span>";
                    if ($data["heure_fin"] != "00h00") {
                        $tableau.= "Fin : " . $data["heure_fin"] . "<br />";
                    }
                    $tableau.="</span>";
                }
                $tableau.=$data["spe"] . " " . ($data["tableau"]) . "<input id='classe_" . $data["num_match"] . "' type='hidden' value='" . strtoupper(str_replace($sign, $chg_sign, rtrim($data["spe"]))) . "' /></td>";
                $go = ($data = mysqli_fetch_assoc($result));
                if ($go == false) {
                    if ($nbr_col < $nbr_col_max) {
                        $tableau.="<td class='aucun' colspan='" . ($nbr_col_max - $nbr_col) . "'>&nbsp;</td>";
                    }
                    break;
                }
            }
            if ($nbr_col > $nbr_col_max) {
                $nbr_col_max = $nbr_col;
            }
            $tableau.="</tr>";
        }
        $tableau.="</table>";
    }
}
