<?php

/* * *****************************************************************************
 * Module qui met en forme et importe les données
 * pour les echeanciers
 *
 * Fu
 * 03/2014
 * Gestion d'une table unique pour les couleurs des spécialités  
 * 
 * Fu
 * 05/2015
 * Gestion de la réinjection d'un échéancier avec conservation des matchs déjà joués
 * ***************************************************************************** */

include ("../../connect.7.php");
$encours = "90EE90"; //couleur pour match en cours
$termine = "FF7F50"; //Couleur match termine
$pause = "DCDCDC"; //Couleur Pause
$horaire = "CCFFFF";  // Couleur pour horaire
//table de conversion des signes pour le nom des classes
$sign = array(" ", "+", "-", "/");
$chg_sign = array("_", "PLUS", 'MOINS', "_");

function analyse($detail) {
    $pos = strpos($detail, "Poule");
    if (!($pos === false)) {
        $reponse[] = substr($detail, 0, $pos - 1);
        $reponse[] = substr($detail, $pos);
    } else {
        $pos = strpos($detail, "de finale");
        if (!($pos === false)) {
            $reponse[] = substr($detail, 0, $pos - 3);
            $reponse[] = substr($detail, $pos - 2);
        } else {
            $pos = strpos($detail, "finale");
            if (!($pos === false)) {
                $reponse[] = substr($detail, 0, $pos - 1);
                $reponse[] = substr($detail, $pos);
            } else {
                $pos = strpos($detail, "Pause");
                if (!($pos === false)) {
                    $reponse[] = $detail;
                    $reponse[] = "";
                } else {
                    $reponse[] = "";
                    $reponse[] = "";
                }
            }
        }
    }
    return $reponse;
}
//Analyse du detail du tableau
//retourne le libelle en deux parties
//1->Specialité
//0->Tableau
function analyse_2($detail) {
	//Parametrage de découpage
	///!\ ordre des éléments important dans le tableau /!\
	$tab_detail = ["Poule"=>[1,0],
			       "quart de finale"=>[1,0],
			       "de finale"=>[3,2],
			       "demi finale"=>[1,0],
			       "finale"=>[1,0],
			       "Pause"=>[0,0]
			  	  ];
	foreach ($tab_detail as $key=>$value) {		  
		$pos = strpos($detail,$key);
		if (!($pos === false)) {
			if ($value[0] == 0 ) {
				$reponse[] = $detail;
				$reponse[] = "";
			}
			else {
				$reponse[] = substr($detail,0,$pos-$value[0]);
				$reponse[] = substr($detail,$pos-$value[1]);
			}
			break;
		}
	}
	if (!(isset($reponse))) {
		$reponse[] = "";
		$reponse[] = "";
	}
return $reponse;
}
function couleur_hasard() {
    global $encours;
    global $termine;
    global $pause;
    global $horaire;
    //couleur de fond au hasard 
    $a = DecHex(mt_rand(0, 15));
    $b = DecHex(mt_rand(0, 15));
    $c = DecHex(mt_rand(0, 15));
    $d = DecHex(mt_rand(0, 15));
    $e = DecHex(mt_rand(0, 15));
    $f = DecHex(mt_rand(0, 15));

    $hexa = $a . $b . $c . $d . $e . $f;
    //Pour éviter les couleur retenue pour : encours et fini et pause
    if (($hexa == $encours) || ($hexa == $termine) || ($hexa == $pause) || ($hexa == $horaire)) {
        $hexa = couleur_aleatoire();
    } else {
        return $hexa;
    }
}

$msg = "";
//Parcour du répértoire pour créé un tableau de nom de fichier
$fichier = array();
$Directory = "server/php/files";
$MyDirectory = opendir($Directory) or die('Erreur');
while ($Entry = @readdir($MyDirectory)) {
    if (is_dir($Directory . '/' . $Entry) && $Entry != '.' && $Entry != '..') {
        
    } else {
        if ($Entry != '.' && $Entry != '..') {
            $fichier[] = $Entry;
        }
    }
}
closedir($MyDirectory);
$tab_titre = array();

//flag pour reprise d'échéancier
// $reprise = o : suppression de la table echeancier des matchs non joués et ajout des matchs non joués depuis le fichier d'import
// $reprise = n : suppression de tous les matchs de la table echeancier et ajout de tous les matchs depuis le fichier d'import
$reprise = isset($_POST["reprise"]) ? $_POST["reprise"] : "n";

//Integration de chaque fichier trouvé 
foreach ($fichier as $e_fichier) {

    //ouvrir le fichier txt
    $handle = fopen($Directory . "/" . $e_fichier, 'r');
    if ($handle) {
        $ligne = utf8_encode(fgets($handle));
        $titre = addslashes($ligne);
        if (strpos($titre, "Echéancier :") === false) {
            $tab_titre[] = array("num_titre" => 0, "titre" => "Fichier " . $e_fichier . " non conforme !", "nbr_match" => 0);
        } else {
            $lieu_date = trim(str_replace("Echéancier : ", "", $titre));
            $sql = "SELECT `num_titre` FROM `titre` WHERE  lieu_date='" . $lieu_date . "'";
            $result = mysqli_query($connect, $sql);

            if ($data = mysqli_fetch_assoc($result)) {
                $num_titre = $data["num_titre"];
                //si reprise de l'échéancier 
                if ($reprise == "n") {
                    //si pas de reprise on effece toutes les informations
                    $sql = "DELETE 
                            FROM echeancier
                            WHERE num_titre=" . $num_titre;
                } else {
                    //sinon  on ne supprime que les matchs non joués
                    $sql = "DELETE 
                           FROM echeancier
                           WHERE num_titre=$num_titre 
                                 AND
                                 etat=0;";
                }
                mysqli_query($connect, $sql); //On supprime si les info existent déjà dans echeancier      
            } else {
                $sql = "INSERT INTO titre (lieu_date)
                        VALUES ('" . addslashes($lieu_date) . "');";

                $result = mysqli_query($connect, $sql);
                $sql = "SELECT MAX(num_titre) as id from titre;";
                $result = mysqli_query($connect, $sql);
                $data = mysqli_fetch_assoc($result);
                $num_titre = $data["id"];
            }
            //lire la premiere ligne  : c'est le titre 

            $pos = strrpos($ligne, " le ");
            $date_liste = substr($ligne, $pos + 4);
            $lieu = substr($ligne, 0, $pos);
            $lieu = str_replace("Echéancier : ", "", $lieu);
            $prem = true;
            $nbr_match = 0;
            $tab_spe = array();
            //tant que pas fin de fichier
            while (!feof($handle)) {
                //  lire l'enregistrement
                $ligne = utf8_encode(rtrim(fgets($handle)));
                $enreg = explode(";", $ligne);
                $premier = true;
                foreach ($enreg as $e_enreg) {
                    if ($premier) { //Le premier élément contient l'horaire
                        $premier = false;
                        $horaire = $e_enreg;
                    } else { // les suivants sont les informations numéro de match et tableau
                        $pos_blanc = strpos($e_enreg, " "); //Position du premier blanc dans l'élément
                        if (!$pos_blanc === false) {
                            $detail = explode(" ", $e_enreg);

                            // extrait Num de match
                            $num_match = $detail[0];
//                            $spe = $detail[1];
//
//                            if (count($detail) > 2) {
//                                $spe.=" " . $detail[2];
//                            }
//                            $spe = rtrim($spe);
//                            $tableau = "";
//                            for ($i = 3; $i <= count($detail) - 1; $i++) {
//                                $tableau.=$detail[$i] . " ";
//                            }
                            //suppression du N° de match de la chaine
                            //Et analyse de la chaine pour en extraire la specialité et le tableau
							$detail[0]="";
                            $tab = analyse_2(ltrim(implode(" ",$detail)));
                            $spe = $tab[0];
                            $tableau = $tab[1];

                            //flag pour l'ajout dans l'échéancier
                            $ajout = true;
                            if ($reprise == "o") {
                                //test si match encore dans la table échéancier si reprise
                                $sql = "SELECT etat 
                                FROM echeancier
                                WHERE num_titre=$num_titre
                                      AND
                                      num_match=$num_match";

                                $result = mysqli_query($connect, $sql);

                                if ($data = mysqli_fetch_assoc($result)) {
                                    $ajout = false; //pas dans la table alors pas d'ajout 
                                }
                            }
                            //si ajout du match dans la table échéancier ou si pas de reprise
                            if ($ajout) {
                                $sql = "INSERT INTO `echeancier` (num_titre,`Horaire`, `num_match`, `spe`, `tableau`, `etat`, heure_debut, heure_fin,terrain)
                              VALUES (" . $num_titre . ",'" . $horaire . "','" . $num_match . "','" . $spe . "','" . addslashes($tableau) . "',0,'00h00','00h00',0)";
                                if (!mysqli_query($connect, $sql)) {
                                    echo "erreur sql : " . mysqli_error($connect);
                                }
                            }
                            if ($spe != "Pause") {
                                /**                                 * ******************************************************
                                 * Generation du tableau des specialité 
                                 * ******************************************************* */
                                $spe = strtoupper(trim(str_replace($sign, $chg_sign, $spe)));
                                if (in_array($spe, $tab_spe, true) == false) {
                                    $tab_spe[] = $spe;
                                }
                                $nbr_match++; //Incrément du nombre de matchs pour ne pas compter les pauses
                            }
                        }
                    }
                }
            }
        }//ligne suivante
        //on memorise les informations nesessaires pour formuler la réponse
        $tab_titre[] = array("num_titre" => $num_titre, "titre" => str_replace(chr(92), "", $lieu_date), "nbr_match" => $nbr_match);
        //Fermeture du fichier      
        fclose($handle);
        //Suppression du fichier
        unlink($Directory . "/" . $e_fichier);
        /*         * *****************************************************************
         * Génération des couleurs des spécialités
         * et stockage dans la table tbl_couleur
         * Si la spécialité n'existe pas dans la table , on l'ajoute avec 
         *  une couleurs au hasard qui n'existe pas déjà dans la table             
         * ***************************************************************** */
        //les deux spe suivantes ne sont créées qu'un fois        
        $tab_spe[] = "PAUSE";
        $tab_spe[] = "Couleur_texte";
        foreach ($tab_spe as $e_tab_spe) {
            // Test si la spé existe dans la table
            $sql = "SELECT coul_specialite 
                      FROM tbl_couleurs
                      WHERE coul_specialite='" . $e_tab_spe . "'";
            $result = mysqli_query($connect, $sql);
            //Si n'existe pas 
            if (!($data = mysqli_fetch_assoc($result))) {
                //boucle tant que la couleur existe dans la table
                $ok = true;
                while ($ok) {
                    $couleur = couleur_hasard();
                    $sql = "SELECT coul_specialite 
                              FROM tbl_couleurs
                              WHERE coul_couleur='" . $couleur . "'";
                    $result = mysqli_query($connect, $sql);
                    $ok = ($data = mysqli_fetch_assoc($result));
                }
                if ($e_tab_spe == 'Couleur_texte') {
                    $couleur = "#0000";
                }
                //On ajoute la nouvelle spe avec la nouvelle couleur
                $sql = "INSERT INTO tbl_couleurs (coul_specialite,coul_couleur,coul_id_titre)
                           VALUE ('" . $e_tab_spe . "','" . $couleur . "',0)";
                if (!mysqli_query($connect, $sql)) {
                    echo mysqli_error($connect);
                }
            }
        }
        unset($tab_spe);
    }
}//fichier suivant
//Nettoyage en cas d'échéancier fusionné
//On recherche les ligne en doublon sur le même horaire
$sql = "Select num_titre,num_match from echeancier group by num_titre,num_match having count(num_match) > 1";
$result = mysqli_query($connect, $sql);
//Parcourt du jeu d'enregistrement pour supprimer les doublons de type PAUSE
while ($data = mysqli_fetch_assoc($result)) {
    $sql = "DELETE echeancier 
            WHERE num_titre=" . $data['num_titre'] . " AND num_match = " . $data['num_match'] . " AND UCASE(spe) = 'PAUSE' LIMIT 1";
    mysqli_query($connect, $sql);
}
//*****************************************************************
echo json_encode($tab_titre); //Retourne le nombre d'enreg integrés
?>
