<?php
session_start();
/* * *********************************************************************
 * Affichage automatique de l'échéancier en cours pour un joueur
 * FU
 * 2013
 * 
 * FU  
 * 01/2014
 * 5.1 Ajout de Tous dans la liste des joueurs
 *    Affichage de l'échéancier global si Tous séléctionné dans liste joueur  
 * 5.2 FU 01/04/2014
 *  Affichage de l'échéancier en horaire de convocations
 *    si date d'échéancier > date du jour   
 * 7.0 FU 04/2015
 * Mise en place affichage avec recalcul des horaires
 * ******************************************************************* */

$num_titre = isset($_GET["num_titre"]) ? $_GET["num_titre"] : 0;
if ($num_titre == 0) {
    $num_titre = isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"] : 0;
}
$appelant = "mon_echeancier.7";
if ($num_titre == 0) {
    $titre = "Echéancier personnel";
    include ("demande_num.7.php");
    exit();
}
$type_echeancier = "C"; // par defaut l'échéancier et en horaire de convocation
include ("genere_echeancier.5.2.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Mon Echéancier</title>
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />
        <link id="css_coul" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 
        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <script type="text/javascript" src="jquery/js/jquery.scrollTo-1.4.3.1-min.js"></script>
        <script type="text/javascript" src="js/mesfonctions.js"></script>
        <script type="text/javascript">
            /******************************************************************************
             * Definition de constantes
             ******************************************************************************/
            var tab_class = Array(<?php echo $tab_classe; ?>);

            function mon_echeancier(reponse) {
                /***************************************
                 *Fonction pour ne monter que les cases du joueur
                 ***************************************/
                //Cache toutes les cases
                $("td").hide();
                //Montre les cases horaire
                //Affiche les cases du joueur
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
                    $("#num_" + reponse[i]).html("<img class='mev' src='images/volant.png' />" + $("#num_" + reponse[i]).html());
                }
            }

            function callComplete(reponse) {
                /*Mise à jour du tableau si modification de la base
                 reponse contient le Num et l'état des lignes modifiées
                 
                 Scroll automatique vers la ligne du plus petit N° de match en cours
                 */

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
                            $this.children("span:eq(2)").html("Fin : " + reponse[i].heure_fin + "</br>"); // affiche l'heure de finn de match
                            break;

                    }


                }
                if ($("#recalcul").prop("checked")) {
                    affiche_horaires_reels();
                }
                //Pour scroller vers le 1er match en cours dans l'échéancier aprés le changement de N° de terrain
                var cible = ".etat_1:first";
                if ($(cible).length > 0) {
                    //Scroll vers la ligne identifiée si elle existe
                    $("#div_table").scrollTo(cible, 800);
                }
                //Appel Tempo toutes les x secondes
                var t = setTimeout("connect();", 15 * 1000);
            }
            ;



            function connect() {
                // boucle infinie : demande de donnée toutes les 15s

                $.post('ajax/retourech.1.1.php', {}, callComplete, 'json');

            }
            ;
            // Initialisation du document
            $(document).ready(function () {
                /*******************************************************************************
                 * Outil gestion deroulage listes
                 *******************************************************************************/
                $(".deroule").button({icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                        .click(function () {
                            var $this = $(this);
                            var id = $this.attr("id");
                            var nom_liste = id.substring(4, id.length);
                            //Avant de delouler la liste, on ferme toutes celles déjà ouvertes
                            $(".deroule").each(function () {
                                var $this = $(this);
                                var id = $this.attr("id");
                                var nom_liste = id.substring(4, id.length);
                                if ($("#" + nom_liste).autocomplete("widget").is(":visible")) {
                                    $("#" + nom_liste).autocomplete("close")
                                }
                            });
                            //On ouvre la liste ciblée  
                            // et on transfert le focus a la zone de saisie.
                            //du coups l'effacement de la liste est geré si on quitte la zone de saisie                              
                            $("#" + nom_liste).autocomplete("search", "")
                                    .focus();

                            return false;
                        });


                /*******************************************************************************                                       
                 * liste déroulante choix du tournoi
                 *******************************************************************************/
                $("#lieu_date").autocomplete({
                    source: "ajax/lst_lieux_dates.1.1.php?cible=ech",
                    minLength: 0,
                    select: function (event, ui) {
                        $("#lieu_date").val(ui.item.value);
                        $("#num_titre").val(ui.item.id);
                        window.location = "<?php echo $appelant; ?>.php?num_titre=" + ui.item.id;
                    }
                });

                /*******************************************************************************
                 * Liste déroulante choix du joueur
                 *******************************************************************************/
                $("#nom_joueur").autocomplete({
                    source: "ajax/lst_joueurs.5.1.php",
                    minLength: 0,
                    select: function (event, ui) {
                        if (ui.item.id != 0) {
                            //Joueur séléctionné
                            $.ajax({
                                Type:'GET',
                                url: "ajax/lst_matchs.5.1.php",
                                data: "licence=" + ui.item.id,
                                dataType: "json",
                                success: function (data) {
                                    mon_echeancier(data);
                                }
                            });
                            $.ajax({
                                Type:'GET',
                                url: "ajax/lst_matchs_joueurs.php",
                                data: "licence=" + ui.item.id,
                                dataType: "json",
                                success: function (data) {
                                    ses_matchs(data);
                                }
                            });
                        } else {
                            //Pas de joueur précisé
                            $("td").show(); //Montre toutes les cases
                            $(".mev").remove(); //Enleve la mise en valeur
                        }
                    }
                });
                /*******************************************************************************
                 * Bouton changement de la couleur
                 *******************************************************************************/
                $("#btn_change_coul").button({icons: {
                        primary: "ui-icon-refresh"
                    },
                    text: false
                })
                        .click(function () {
                            change_couleur();
                        });
                //Sur clic de recalcul
                //Si la case est décochée alors on retabli les tranches horaires initiales
                //qui sont egalement l'id de la ligne
                $("#recalcul").click(function () {
                    if ($(this).prop('checked') == false) {
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

            /*******************************************************************************
             * Relance la procedure de generation des nouvelles couleurs pour les classe 
             * de l'échéancier
             *******************************************************************************/
            function regenere_couleurs() {
                for (i = 0; i < tab_class.length; i++) {
                    if (tab_class[i] != "Pause") {
                        couleur = hex2rgb(couleur_hasard());
                        change_regle("." + tab_class[i], "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                    }
                }
            }
            /*****************************************************
             * Passe l'echéancier de couleur à Noir et blanc ou
             * réciproquement
             *****************************************************/
            function change_couleur() {
                if ($("#change_coul").html() == 'En couleur') {
                    $("#change_coul").html('En noir et blanc');
                    regenere_couleurs();
                    change_regle("#echeancier td", "color", "rgb(255,255,255)");
                } else {
                    $("#change_coul").html('En couleur');
                    for (i = 0; i < tab_class.length; i++) {
                        if (tab_class[i] != "Pause") {
                            change_regle("." + tab_class[i], "backgroundColor", "rgb(255,255,255)");
                        }
                    }
                    change_regle("#echeancier td", "color", "rgb(0,0,0)");
                }
            }

        </script>

        <style type="text/css">
            input {
                width : 300px;
            }
            .mev {
                width:10%;
                float:left;
            }
            @-moz-keyframes blink {0%{opacity:1;} 50%{opacity:0;} 100%{opacity:1;}} /* Firefox */
            @-webkit-keyframes blink {0%{opacity:1;} 50%{opacity:0;} 100%{opacity:1;}} /* Webkit */
            @-ms-keyframes blink {0%{opacity:1;} 50%{opacity:0;} 100%{opacity:1;}} /* IE */
            @keyframes blink {0%{opacity:1;} 50%{opacity:0;} 100%{opacity:1;}} /* Opera and prob css3 final iteration */
            img, .etat_1{
                border:none;
                -moz-transition:all 1s ease-in-out;
                -webkit-transition:all 1s ease-in-out;
                -o-transition:all 1s ease-in-out;
                -ms-transition:all 1s ease-in-out;
                transition:all 1s ease-in-out;
                /* order: name, direction, duration, iteration-count, timing-function */  
                -moz-animation:blink normal 2s infinite ease-in-out; /* Firefox */
                -webkit-animation:blink normal 2s infinite ease-in-out; /* Webkit */
                -ms-animation:blink normal 2s infinite ease-in-out; /* IE */
                animation:blink normal 2s infinite ease-in-out; /* Opera and prob css3 final iteration */
            }
            .ui-autocomplete {
                max-height: 100px;
                overflow-y: auto;
                /* prevent horizontal scrollbar */
                overflow-x: hidden;
            }
            /* IE 6 doesn't support max-height
            * we use height instead, but this forces the menu to always be this tall
            */
            * html .ui-autocomplete {
                height: 100px;
            }
        </style>

    </head>
    <body>

        <div style="float:left;">
            <fieldset class="ui-widget">
                Lieu et date&nbsp;:&nbsp;<input type="text" id="lieu_date" /><button class="deroule" id="lst_lieu_date"></button> <br /> <br />
                Votre nom&nbsp;&nbsp;&nbsp;:&nbsp;<input type="text" id="nom_joueur" /><button class="deroule" id="lst_nom_joueur"></button>
                <button id="btn_change_coul"></button><span id="change_coul" style="display:none;">En couleur</span><br />
                Recalcul tranches horaires : <input id='recalcul' type='checkbox' checked='checked' /> <br /> 
            </fieldset>
        </div>
        <div style="float:left; text-align:center;" >
            <h1><?php echo $titre; ?></h1>
        </div>
        <div style="float:right;">
            <h2><span id="horloge"></span></h2>
        </div>

        <div id="div_table" style="overflow-x: hidden; overflow-y: scroll; height:800px; width:100%; float:left;">
<?php echo $tableau; ?>
        </div>
    </body>
</html>
