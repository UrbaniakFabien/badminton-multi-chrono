<?php

/* * *****************************************************************************
 * Generation du tableau de base pour les listes de pointages en table de pointage 
 *  et table de marque
 * FU
 * 04/2014
 * ***************************************************************************** */
//TODO table des décalages par id tournoi en minute pour le calcul des heures de convocation en fonction de l'heure de match

$result =exec_commande( "SELECT * from titre");
$tab_decalage = array();
while ($data = mysqli_fetch_array($result)) {
    if ($data["decalage_horaire_convocation"] != "") {
        list($h, $m) = explode("h", $data["decalage_horaire_convocation"]);
        $data["decalage_horaire_convocation"] = ($h * 60) + $m;
    } else {
        $data["decalage_horaire_convocation"] = 0;
    }

    $tab_decalage[] = $data;
}

$entete = "<table id='liste'>
         <thead><tr>";
$result =exec_commande( "show columns from joueurs");
if (!$result) {
    $entete .= "<th>Pas d'information à traiter. Importez un fichier</th></tr></thead></table>";
    $corps = "";
} else {
    $nb_col = 0;
    $tab_non_colonne = array('Num', 'etat', 'num_titre', 'commentaire');
    $entete .= "<th>etat</th>";
    while ($row = mysqli_fetch_assoc($result)) {
        $nb_col++;
        if (!(in_array($row["Field"], $tab_non_colonne, true))) {
            $entete .= "<th>" . $row["Field"] . "</th>";
        }
    }
    //Jout de la colonne 'heure de convocation' dans le tableau 
    //FU
    //04/2015
    //$nb_col--;
    $entete .= "<th>Heure de convocation</th>";

    $entete .= "</tr>";
    // on crée la requête SQL
    //Ajout horaire_convocation et num_terrain
    //FU
    //04/2015
    $sql = 'SELECT `Num`,`Joueur`,`Licences`,`Matchs`,`Salle`,`Convoqué le`,"00h00" as horaire_convocation, num_titre,`etat`, commentaire FROM joueurs order by num';

// on envoie la requête
    $req =exec_commande( $sql) or die('Erreur SQL !<br>' . $sql . '<br>' . mysqli_error());

    $corps = "</thead><tbody>";
    while ($data = mysqli_fetch_assoc($req)) {
        $num = $data["Num"];
        $corps .= "<tr id='num" . $num . "'  class='";
        switch ($data["etat"]) {
            case 1:
            case 2:
            case 3:
                $corps .= "etat" . $data["etat"];
                break;
            default:
                $corps .= "etat0";
        }
        $corps .= "' title='" . $data["commentaire"] . "' commentaire='" . $data["commentaire"] . "'>
      <td id='etatnum" . $num . "'>";
        if ($data["etat"] == "") {
            $corps .= "0";
        } else {
            $corps .= $data["etat"];
        }
        $corps .= "</td>";
        $i = 0;
        //Recherche de l'horaire de convocation pour le joueur   dans l'échéancier
        //FU
        //04/2015
        $lst_match = $data["Matchs"];
        //les N° de match dans l'échéancier qui sont <10 ne sont pas sous la forme 0x comme dans la liste des matchs
        //on enleve le 0 s'il est présent en début de la liste
        if (substr($lst_match, 0, 1) == "0") {
            $lst_match = substr($lst_match, 1);
        }
        $sql = 'SELECT MIN(horaire) as horaire_convocation
			    FROM echeancier
			    WHERE num_titre = ' . $data["num_titre"] . '
					AND FIND_IN_SET (num_match,"' . $lst_match . '")>0';
        //Recherche match du joueur dans l'échéancier
        // et traitement si existe
        $result =exec_commande( $sql);
        if ($data_ = mysqli_fetch_array($result)) {
            $decalage = 30; //décalage par défaut 30 minutes
            foreach ($tab_decalage as $e) {
                if ($e["num_titre"] == $data["num_titre"]) {
                    if ($e["decalage_horaire_convocation"] != 0) {
                        $decalage = $e["decalage_horaire_convocation"];
                        break;
                    }
                }
            }
            if ($data_["horaire_convocation"] != "") {
                //Mise a jour de la donnée dans le tableau principal
                list($h, $m) = explode("h", $data_["horaire_convocation"]);
                $convoc = ($h * 60) + $m - $decalage;
                $h = intval($convoc / 60);
                $m = $convoc % 60;
                $data["horaire_convocation"] = ($h < 10 ? "0" . $h : $h) . "h" . ($m < 10 ? "0" . $m : $m);
            }
        }

        //------------------------------------------------------------
        //Création de la ligne
        foreach ($data as $e_data) {

            $i++;
            //on evite la clef ,l'etat du joueur, le commentaire, le numero id du tournoi
            if (($i < ($nb_col - 1)) && ($i > 1)) {
                $corps .= "<td>" . $e_data . "</td>";
            }
        }
        $corps .= "</tr>";
    }
    $corps .= "</tbody></table>";
}
// on ferme la connexion à mysql
mysqli_close($connect);
$formulaire = '
<div id="frm_commentaire">
  <input type="hidden" id="id_lig">
  <table style="broder:none;"><tr><td style="vertical-align:top;border:none;">
  Commentaire :</td><td style="border:none;"> <textarea id="commentaire"  /></textarea>
  </td></tr></table>
</div>
';
