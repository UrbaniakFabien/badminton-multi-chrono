<?php
session_start();
/* * *********************************************************************
 * Affichage automatique de l'échéancier en cours pour un joueur
 * FU
 * 2013
 * 
 * FU
 * 10/01/2014
 * Gestion des nom de classe des specialités en majuscule    
 * ******************************************************************* */
include("../connect.7.php");
$num_titre = isset($_GET["num_titre"]) ? $_GET["num_titre"] : 0;
if ($num_titre == 0) {
    $num_titre = isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"] : 0;
}
$path = "../";
if ($num_titre == 0) {
    $appelant = "index";
    $titre = "Echéancier personnel";
    include ($path . "demande_num.7.php");
    exit();
}
$type_echeancier = "C"; // par defaut l'échéancier et en horaire de convocation
include ($path . "genere_echeancier.5.2.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <title>Mon échéancier
        </title>
        <link rel="stylesheet" href="css/jquery.mobile-1.3.2.min.css" />
        <link rel="stylesheet" href="my.css" />
        <script src="../jquery/jquery-2.1.3.min.js">
        </script>
        <script src="js/jquery.mobile-1.3.0.min.js">
        </script>
        <script src="js/autoComplete/jqm.autoComplete-1.5.2-min.js">
        </script> 
        <script src="../js/mesfonctions.js">
        </script>     
        <link id="css_coul" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 

        <script type="text/javascript">

            var tab_class = Array(<?php echo $tab_classe; ?>);

            try {

                function mon_echeancier(reponse) {
                    /***************************************
                     *Fonction pour ne montrer que les cases du joueur
                     ***************************************/
        //Cache toutes les cases
                    $("td").hide();
        //Montre les cases horaire
        //Affiche les case du joueur
                    for (i = 0; i < reponse.length; i++) {
                        $("#num_" + reponse[i]).parent("tr").children(".horaire").show()
                        $("#num_" + reponse[i]).show();

                    }

                }

                function ses_matchs(reponse) {
                    /***********************************
                     *Pour mettre en evidence les matchs connu dans la liste de pointage
                     ************************************/
                    //On efface les mise en évidences précédentes
                    $(".mev").remove();
                    //On ajoute la mise en évidence 
                    for (i = 0; i < reponse.length; i++) {
                        $("#num_" + reponse[i]).html("<img class='mev' src='../css/images/volant.png' />" + $("#num_" + reponse[i]).html());
                    }
                }

                function callComplete(reponse) {
                    /*Mise à jour du tableau si modification de la base
                     reponse contient le Num et l'état des lignes modifiées
                     */
                    //Heure systeme
                    var Sys_date_heure = new Date();
                    var heure = Sys_date_heure.getHours() - 1;
                    var min = Sys_date_heure.getMinutes();
                    //Mise a jour de l'affichage des cellules en fonction des données retournées
                    for (i = 0; i < reponse.length; i++) {
                        var $this = $("#num_" + reponse[i].num);
                        $this.removeClass()
                                .toggleClass("etat_" + reponse[i].etat, reponse[i].etat != 0)
                                .toggleClass($("#classe_" + reponse[i].num).val(), (reponse[i].etat == "0" || reponse[i].etat == "3"));
                        $("#input_num_" + reponse[i].num).html(reponse[i].terrain);

                        switch (reponse[i].etat) {
                            case "3" :
                            case "0" :
                                $this.children("span:eq(1)").html("");// efface le heure de debut
                                $this.children("span:eq(2)").html(""); // et de fin de match
                                $this.children("span:eq(0)").hide();//cache le N° de terrain
                                break;
                            case "1" :
                                $this.children("span:eq(1)").html("D&eacute;but : " + reponse[i].heure_debut + "</br>");//affiche l'heure de début de match
                                $this.children("span:eq(2)").html(""); // efface heure de fin de match
                                $this.children("span:eq(0)").show();//affiche le N° de terrain
                                break;
                            case "2" :
                                $this.children("span:eq(2)").html("Fin : " + reponse[i].heure_fin + "</br>"); // affiche l'heure de fin de match
                                break;

                        }

                    }
                    if ($("#recalcul").val() == "true") {
                        affiche_horaires_reels();
                    }
                    //Appel Tempo toutes les 15 secondes
                    var t = setTimeout("connect();", 15 * 1000);
                }
                ;



                function connect() {
                    // boucle infinie : demande de donnée toutes les 15s

                    $.post('../ajax/retourech.1.1.php', {}, callComplete, 'json');

                }
                ;
                // Initialisation du document
                $(document).ready(function () {

                    $("#toggleswitch1").change(function () {
                        change_couleur();
                    });

                    $("#joueur").autocomplete({
                        target: $('#choix_joueur'),
                        source: "../ajax/lst_joueurs.5.1.php?format=2",
                        minLength: 4,
                        link: "",
                        loadingHtml: '<li data-icon="none"><a href="#">Recherche en cours...</a></li>',
                        callback: function (e) {
                            var $a = $(e.currentTarget); // access the selected item
                            $('#joueur').val($a.text()); // place the value of the selection into the search box
                            var num_lic = $a.data('autocomplete').value;
                            $("#joueur").autocomplete('clear'); // clear the listview

                            if (num_lic != 0) {  //Joueur précis=>échéancier du joueur
                                //Affichage des matchs 
                                $.ajax({
                                    url: "../ajax/lst_matchs.5.1.php",
                                    data: "licence=" + num_lic,
                                    dataType: "json",
                                    success: function (data) {
                                        mon_echeancier(data);
                                    }
                                });
                                $.ajax({
                                    url: "../ajax/lst_matchs_joueurs.php",
                                    data: "licence=" + num_lic,
                                    dataType: "json",
                                    success: function (data) {
                                        ses_matchs(data);
                                    }
                                });
                            } else { // Pas de joueur précis=>échéancier global
                                $("td").show();
                                $(".mev").remove();
                            }
                        },
                        icon: ""

                    });

                    /************************************************************************
                     * Autocomplete des sites
                     ************************************************************************/
                    $("#site").autocomplete({
                        target: $('#choix_site'),
                        source: "../ajax/lst_lieux_dates.1.1.php?format=2&cible=ech",
                        minLength: 0,
                        link: "",
                        callback: function (e) {
                            var $a = $(e.currentTarget); // access the selected item
                            $('#site').val($a.text()); // place the value of the selection into the search box
                            var num_titre = $a.data('autocomplete').value;
                            window.location = "?num_titre=" + num_titre;
                        },
                        loadingHtml: '<li data-icon="none"><a href="#">Recherche en cours...</a></li>',
                        icon: "none"
                    });

                    //Action si changement de valeur pour recalcul                      
                    $("#recalcul").change(function () {
                        if ($(this).val() == "false") {
                            $(".horaire").each(function () {
                                $(this).html($(this).parent().attr("id"));
                            })
                        } else {
                            affiche_horaires_reels();
                        }
                    });
                    //Lancement de la boucle de  rafraichissement des données
                    connect();
                    timer();
                });

                function regenere_couleurs() {
                    /****************************************
                     *Relance la procedure de generation de 
                     * des nouvelles couleurs pour les classe de l'échéancier
                     ****************************************/

                    for (i = 0; i < tab_class.length; i++) {
                        if (tab_class[i] != "PAUSE") {
                            couleur = hex2rgb(couleur_hasard());
                            change_regle("." + tab_class[i], "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                        }
                    }
                }
                function change_couleur() {
                    /*****************************************************
                     * Passe l'echéancier de couleur à Noir et blanc ou
                     * réciproquement
                     *****************************************************/

                    if ($("#change_coul").html() == 'En couleur') {
                        $("#change_coul").html('En noir et blanc');
                        regenere_couleurs();
                        change_regle("#echeancier td", "color", "rgb(255,255,255)");
                    } else {
                        $("#change_coul").html('En couleur');
                        for (i = 0; i < tab_class.length; i++) {
                            if (tab_class[i] != "PAUSE") {
                                change_regle("." + tab_class[i], "backgroundColor", "rgb(255,255,255)");
                            }
                        }
                        change_regle("#echeancier td", "color", "rgb(0,0,0)");
                    }
                }


            } catch (error) {
                console.error("Il y a une erreur dans le script :-(: " + error);
            }
        </script>
    </head>
    <body>
        <span id="change_coul" style="display:none;"></span>

        <!-- Home -->
        <div data-role="page" id="page1"> 
            <div data-role="content">
                <div style="float:right;">
                    <div style="color:black;font-size:25px;" id="horloge"></div>
                </div>
                <br> 
                <div  style="width:80%">
                    <input type="text" id="site" placeholder="Nom de la salle ou date du tournoi" data-filter="true"  data-type="search" data-filter-theme="d" value="<?php echo $site; ?>">
                    <ul id="choix_site" data-role="listview" data-inset="true" ></ul>
                </div>
                <div style="width:80%;"> 
                    <input type="text" id="joueur" placeholder="Choix d'un joueur (4 premiers caracteres minimum)" data-type="search">
                    <ul id="choix_joueur" data-role="listview" data-inset="true" ></ul>
                </div>

                <div data-role="fieldcontain">
                    <fieldset data-role="controlgroup">
                        <label for="toggleswitch1">
                            Couleur
                        </label>
                        <select name="toggleswitch1" id="toggleswitch1" data-theme="" data-role="slider" data-mini="true">
                            <option value="on">
                                On
                            </option>
                            <option value="off">
                                Off
                            </option>
                        </select>
                    </fieldset>
                </div>
                <div data-role="fieldcontain">
                    <fieldset data-role="controlgroup">
                        <label for="toggleswitch2">
                            Recalcul dynamique des tranches horaire
                        </label>
                        <select name="toggleswitch2" id="recalcul" data-theme="" data-role="slider" data-mini="true">
                            <option value="true">
                                On
                            </option>
                            <option value="false">
                                Off
                            </option>
                        </select>
                    </fieldset>
                </div>
                <div data-role="collapsible-set">
                    <div data-role="collapsible" data-collapsed="false">
                        <h3>
                            <?php echo $titre; ?>&nbsp; <span id="avance"></span>
                        </h3>
                        <?php echo $tableau; ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
