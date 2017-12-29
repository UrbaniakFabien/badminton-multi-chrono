<?php

include ("../..connect.7.php");
/*
 * Extraction des données du fichier csv de réglements
 *FUR
 * 12/2017
 * Annulation case 3 :
 * Le case 4 =>enregistre
 */
 //Parcour du répértoire pour créé un tableau de nom de fichier
$fichier = array();
$Directory="server/php/files";
$MyDirectory = opendir($Directory) or die('Erreur');
	while($Entry = @readdir($MyDirectory)) {
		if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..') {
    }  
		else {
    if ($Entry != '.' && $Entry != '..') {
			$fichier[]=$Entry;
    }
    } 
                 
	 }
closedir($MyDirectory);
$tab_titre=array();//Contient les informations pour le retour
//Integration de chaque fichier trouvé 
foreach ($fichier as $e_fichier) {   
$f = fopen($e_fichier, "r");
//On vide les tables à chaque traitement
$result = mysqli_query($connect,"TRUNCATE `tbl_regl_clubs`;");
$result = mysqli_query($connect,"TRUNCATE `tbl_regl_joueurs`;");
while ($ligne = fgets($f)) {
    if ($ligne != "") {
        $tab = explode(";", ($ligne));

        //var_dump($tab);
        //En fonction du nombre d'éléments dans le tab on sait sur quelle type de ligne on est.
        switch (count($tab)) {
            case 13 : //Ligne info joueur
                if ($tab[0] != "Nom") {
                    list($j,$m,$y) = explode("/",$tab[5]);
                    $tab[5] = "$y-$m-$j";
                    $tab_joueurs[] = array("reg_joueurs_nom" => $tab[0], "reg_joueurs_club" => $tab[1], "reg_joueurs_date" => $tab[5], "reg_joueurs_montant" => $tab[11]+0,"reg_joueurs_regle"=>false);
                    $club = $tab[1];
                }
                break;
            case 4 ://ligne total =>enregistrement des infos 
                list($lib, $nbr_joueurs) = explode(":",trim( $tab[0]));
                list($lib, $total) = explode(":", trim($tab[1]));
                list($lib, $deja_regle) = explode(":", trim($tab[2]));

                $tab_club = array("club" => $club, "nbr_joueur" => $nbr_joueurs+0, "total" => $total+0, "deja_regle" => $deja_regle+0);
                $ok_paye =  ($total+0 ==  $deja_regle+0);
             //   break;
            //case 3://si mot 'Ville' alors on change de club =>enregistrement des infos
               // if (strpos($tab[0], "Ville") >= 0) {
                  
                    if (isset($tab_joueurs)) {
                        $sql = "INSERT INTO tbl_regl_clubs (reg_club,reg_nbr_joueurs,reg_total,reg_deja_regle) VALUES ('" . implode("','", $tab_club) . "')";
                       
                        $result = mysqli_query($connect, $sql);
                        //reccupere aprés l'enregistrement la clef générée
                        $reg_joueurs_id_fk_club = mysqli_insert_id($connect);
                        //echo $sql . "<br>";
                        foreach ($tab_joueurs as $e_tab_joueurs) {
                            $e_tab_joueurs["reg_joueurs_regle"] = $ok_paye;
                            $sql = "INSERT INTO tbl_regl_joueurs (reg_joueurs_id_fk_club," . implode(",", array_keys($e_tab_joueurs)) . ") VALUES ($reg_joueurs_id_fk_club,'" . implode("','", $e_tab_joueurs) . "')";
                            //echo $sql . "<br>";
                            $result = mysqli_query($connect, $sql);
                            
                        }
                        unset($tab_joueurs);
                        unset($tab_club);
                    }
               // }
                break;
        }
    }
}
 fclose($handle);
  //Suppression du fichier
  unlink($Directory."/".$e_fichier);
}
