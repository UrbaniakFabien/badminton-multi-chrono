<?php
session_start();

/* * *********************************************************************
 * Affichage automatique de l'échéancier en cours
 * FU
 * 2012
 * Version 5.0
 * 12/2013 
 *  Passage à UTF-8  
 *  Controle des droits d'accés par le menu général 
 * Version 5.1  
 * 01/2014
 *  FU
 *  Gestion des couleurs par échéancier
 *  Clic-droit pour menu  
 * Version  5.2 
 * 04/2014 
 * FU  
 *  Correction reprise des couleurs dans le formulaire
 * Passage des couleurs de l'échéancier en cours à l'impression
 * Modification du formulaire menu de configuration
 * Possibilite de changer la couleur du texte dans l'échéancier
 * Gestion d'un type echeancier
 *    M-> affichage horaire de match (defaut)
 *    C-> Horaire de convocation : Heure de match - xxhxx  
 * Affichage de l'échéancier en heure de convocation si date du jour < date de l'échéancier  
 * 
 * Version 6
 * 05/2014   
 * Ajout sous formulaire gestion orientation et format pour impression échéancier
 *  
 * ******************************************************************* */

$num_titre = isset($_GET["num_titre"]) ? $_GET["num_titre"] : 0;
if ($num_titre == 0) {
    $num_titre = isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"] : 0;
}
$appelant = "echeancier.7";
if ($num_titre == 0) {
    $titre = "Affichage échéancier";

    include ("demande_num.7.php");
    exit();
}
/* * *****************************************************************************
 * Valeur par défaut du type d'échéancier
 * Les parametres sont ensuite traités dans genere_echeancier
 * ***************************************************************************** */

$type_echeancier = "M";
$decalage_horaire = "00h00";

include ("genere_echeancier.5.2.php");

$decalage_horaire = ($decalage_horaire == "") ? "00h30" : $decalage_horaire;
/* * *****************************************************************************
 * Génération du formulaire pour la saisie des couleurs par Tableau
 * ***************************************************************************** */
$formulaire_couleurs = "<table id='tableau_couleurs' style='margin:auto'><thead><tr><th>Tableau</th><th>Couleur</th></tr></thead><tbody>";
foreach ($tab_couleur as $key => $e_tab_couleur) {
    if (($key != "PAUSE") && ($key != "horaire")) {
        $formulaire_couleurs.="<tr><td>" . str_replace($chg_sign, $sign, $key) . "</td><td style='text-align:center;'><input size='3px'class='couleur' type='hidden' id='" . $key . "' name='" . $key . "' value='" . rgb2hex($e_tab_couleur["r"], $e_tab_couleur["g"], $e_tab_couleur["b"]) . "'>&nbsp;</td></tr>";
    }
}
$formulaire_couleurs.="</tbody></table>";
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title><?php echo str_replace("<span class='titre'>", "", str_replace("</span>", "", $titre)); ?></title>
        <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link id="css_coul" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="jquery/jquery-miniColors/jquery.miniColors.css" />

        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <script type="text/javascript" src="jquery/js/jquery.scrollTo-1.4.3.1-min.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>
        <script type="text/javascript" src="jquery/jquery-miniColors/jquery.miniColors.js"></script>
        <script type="text/javascript" src="js/mesfonctions.js"></script>
        <script type="text/javascript">

            /***************************************************************************
             * Definition de constantes
             ***************************************************************************/
            var tempo = 10; //Temporisation par défaut pour le rafraichissement
            var tab_class = Array(<?php echo $tab_classe ?>);   //Tableau des classes css des specialités
            var debug = false; //permet de debuger sans les fonction de rafraichissement
            var nombre_essai = 0;
            var max_essai_avant_reload = 5;

            /*************************************************************************
             *Relance la procedure de generation  
             * des nouvelles couleurs pour les classes css de l'échéancier 
             *************************************************************************/
            function regenere_couleurs() {

                for (i = 0; i < tab_class.length; i++) {
                    if (tab_class[i] !== "PAUSE") {
                        //génération couleur au hasard format hexa
                        var hex_couleur = couleur_hasard();
                        //conversion format rgb
                        var couleur = hex2rgb(hex_couleur);
                        //Mise a jour des champs du formulaire de gestion des couleurs
                        $("#" + tab_class[i]).val(hex_couleur)
                                .minicolors("value", hex_couleur)
                                .next().children().css("background-color", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                        change_regle("." + tab_class[i], "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                    }
                }
            }
            ;

            /*****************************************************************************
             * Ouverture du formulaire changement d'echeancier
             *****************************************************************************/
            function change_ech() {
                $("#modal_form").dialog("open");
            }
            /******************************************************************************
             * Mise à jour du tableau si modification de la base
             *   reponse contient le Num et l'état des lignes modifiées
             * Scroll automatique vers la ligne identifiée par le 1er match actuellement en cours
             ******************************************************************************/
            function callComplete(reponse) {
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


                //Pour scroller vers le 1er match en cours dans l'échéancier aprés le changement de N° de terrain
                var cible = ".etat_1:first";
                if ($(cible).length > 0) {
                    //Scroll vers la ligne identifiée si elle existe
                    $("#div_table").scrollTo(cible, 800);
                }
                //Appel Tempo toutes les x secondes
                var t = setTimeout("connect();", tempo * 1000);
            }
            ;

            /*****************************************************************************
             * Fonction en boucle pour mise a jour de l'echeancier d'aprés les modifications
             * faites dans chrono+echeancier
             *****************************************************************************/
            function connect() {
                nombre_essai++; //increment du nombre d'essai
                //$.post('ajax/retourech.1.1.php', {}, callComplete, 'json');
                $.ajax({
                    type: "POST",
                    url: 'ajax/retourech.1.1.php',
                    dataType: 'json',
                    timeout: 10000,
                    error: function () {
                        if (nombre_essai >= max_essai_avant_reload) {
                            $("#msg").html("Probl&eacute;me de connexion, la page doit-&eacirc;tre recharg&eacute;e !!!");
                            $("#msg").show();
                            window.location = "<?php echo $appelant; ?>.php";
                        }
                    },
                    success: function (reponse) {
                        callComplete(reponse);
                        nombre_essai = 0; // Essai réussi => remise à zero du compteur

                    }
                });
                if ($("#recalcul").prop("checked")) {
                    affiche_horaires_reels();
                }
            }
            ;

            /*****************************************************************************
             * Impression de l'echeancier en cours
             *****************************************************************************/
            function imprime(orientation, format) {
                var tab_couleur = "horaire|" + rgb2hex($(".horaire").css("background-color")) + "@";
                var couleur_pause = $(".PAUSE").css("background-color");
                if (couleur_pause != undefined) {
                    tab_couleur = tab_couleur + "PAUSE|" + rgb2hex(couleur_pause) + "@";
                }
                tab_couleur = tab_couleur + "|#ffffff@";//Pour traiter le cas d'un echéancier sans spécialité
                for (i = 0; i < tab_class.length; i++) {
                    if (tab_class[i] !== "PAUSE") {

                        tab_couleur = tab_couleur + tab_class[i] + "|" + $("#" + tab_class[i]).val() + "@";
                    }
                }
                tab_couleur = tab_couleur.replace(/#/g, "");
                window.open("imprime_echeancier_pdf.6.php?format=" + format + "&orientation=" + orientation + "&couleur=" + $("#change_coul").html() + "&tab_couleur=" + tab_couleur.substring(0, tab_couleur.length - 1) + "&type_echeancier=" + $('#type_echeancier').val() + "&decalage_horaire=" + $("#decalage_horaire").val(), '_blank');
            }
            //*********************************************************************
            //* Test si anomalies lors des appels ajax 
            //*********************************************************************
            $(document).ajaxError(function (e, jqXHR, ajaxSettings, exception)
            {
                var anomalie = "";
                {

                    if (jqXHR.status === 0)
                    {
                        anomalie = "Probl&eacute;me de connexion. ";

                    } else if (jqXHR.status == 404)
                    {
                        anomalie = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500)
                    {
                        anomalie = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror')
                    {
                        anomalie = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout')
                    {
                        anomalie = 'Time out error.';
                    } else if (exception === 'abort')
                    {
                        anomalie = 'Ajax request aborted.';
                    } else
                    {
                        anomalie = 'Erreur  : \n' + jqXHR.responseText;
                    }
                }
                if (anomalie != "") {
                    centerThis("#msg");
                    compte_a_rebours();
                    $("#msg_txt").html(anomalie);
                    $("#msg").show()
                            .delay(10000).queue(function () {
                        window.location = "<?php echo $appelant; ?>.php";
                    }
                    );

                }
            });

            function compte_a_rebours() {
                var dec = parseInt($("#decompte").html( )) - 1;
                if (dec >= 0) {
                    $("#decompte").html(dec);
                    var t = setTimeout("compte_a_rebours();", 1000);   //Appel Tempo toutes les 1s
                }
            }
            /*******************************************************************************************
             * Fonction de centrage d'un élément dans l'ecran
             *******************************************************************************************/

            function centerThis(element) {
                var windowHeight = $(window).height();
                var windowWidth = $(window).width();
                var elementHeight = $(element).height();
                var elementWidth = $(element).width();

                var elementTop, elementLeft;

                if (windowHeight <= elementHeight) {
                    elementTop = $(window).scrollTop();
                } else {
                    elementTop = ((windowHeight - elementHeight) / 2) + $(window).scrollTop();
                }

                if (windowWidth <= elementWidth) {
                    elementLeft = $(window).scrollLeft();
                } else {
                    elementLeft = ((windowWidth - elementWidth) / 2) + $(window).scrollLeft();
                }

                $(element).css({
                    'top': elementTop,
                    'left': elementLeft
                });
            }
            // Initialisation du document
            $(document).ready(function () {
                /*************************************************************************
                 *Complement du menu
                 *************************************************************************/
                $("#menu_echeancier").html("<a href='#'>Affichage</a><ul><li><a onclick='change_couleur(this);' id='change_coul'>En noir et blanc</a></li><li><a onclick='gestion_couleurs();' id='gestion_couleurs'>Gestion des couleurs</a></li><li><a>Rafraichissement</a><ul><li><a>1 s<span></span></a></li><li><a>5 s<span></span></a></li><li><a>10 s<span class='sel' ></span></a></li><li><a>15 s<span></span></a></li></ul></li><li><a id='clic_imprime'>Imprime</a></li><li><a onclick='change_ech();'>Change Echéancier</a></li></ul>");


                /***********************************************************************
                 * affichage et gestion du selecteur pour deroulage listes
                 ***********************************************************************/
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
                /*************************************************************************
                 * Definition du formulaire choix d'echeancier
                 *************************************************************************/
                $("#modal_form").dialog({title: 'Choix lieu et date',
                    width: 'auto',
                    height: 300,
                    //position:'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [{
                            text: "OK",
                            'click': function () {
                                window.location = "<?php echo $appelant; ?>.php?num_titre=" + $("#num_titre").val();
                            },
                            icons: {primary: 'ui-icon-check'}},
                        {
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]
                });

                /*************************************************************************
                 * Definition autocomplete choix d'echeancier
                 *************************************************************************/
                $("#lieu_date").autocomplete({
                    source: "ajax/lst_lieux_dates.1.1.php?cible=ech",
                    minLength: 0,
                    select: function (event, ui) {
                        $("#lieu_date").val(ui.item.value);
                        $("#num_titre").val(ui.item.id);
                    }
                });
                /**********************************************************
                 * Boutons du formulaire de configuration echeancier
                 **********************************************************/
                $("#noir_blanc").button({text: "Echéancier en noir et blanc"}).click(function () {
                    change_couleur(this);
                    if ($(this).button('option', 'text') == "Echéancier en noir et blanc") {
                        $(this).button('option', 'text', "Echéancier en couleurs");
                        $("#noir_blanc span").html("Echéancier en couleurs");
                        $("#change_coul").html("En couleur");
                    }
                    else {
                        $(this).button('option', 'text', "Echéancier en noir et blanc");
                        $("#noir_blanc span").html("Echéancier en noir et blanc");
                        $("#change_coul").html("En noir et blanc");
                    }
                    return false;
                });
                $("#chg_couleur").button().click(function () {
                    $("#frm_conf_ech").dialog("close");
                    $("#msg_coul").html("");
                    gestion_couleurs();
                    return false;
                });
                $("#chg_ech").button().click(function () {
                    change_ech();
                    return false;
                });

                /***************************************************************************
                 * Definition du menu 
                 ***************************************************************************/

                $("#menuprinc").menu({
                    autoExpand: true,
                    menuIcon: true,
                    buttons: true,
                    position: {using: positionnerSousMenu},
                    select: function (event, ui) {
                        var text_select = ui.item.text();
                        if ((text_select == "1 s") || (text_select == "5 s") || (text_select == "10 s") || (text_select == "15 s")) {
                            //On efface la selection précédente
                            $("span.ui-icon-check.ui-menu-icon").removeClass();
                            //On montre la selection courante
                            ui.item.children().children().addClass('ui-menu-icon ui-icon-check ui-icon');
                            switch (text_select) {
                                case "1 s":
                                    tempo = 1;
                                    break;
                                case "5 s":
                                    tempo = 5;
                                    break;
                                case "10 s" :
                                    tempo = 10;
                                    break;
                                case "15 s" :
                                    tempo = 15;
                                    break;
                            }
                            //synchronise le select du menu sur clic droit
                            $("#frm_menu_ech select").val(tempo);
                        }
                    }
                });
                //Ajout des classes pour montrer quel est le délai de rafraichissement par défaut
                $(".sel").addClass('ui-menu-icon ui-icon-check ui-icon');

                /*************************************************************************
                 * Formulaire saisie des couleurs de l'échéancier
                 *************************************************************************/
                /*************************************************************************
                 * Contournement de la gestion de la taille du formulaire
                 * pour permettre l'affichage en bas du tableau du selecteur de couleurs
                 * sans avoir a utiliser le scroll
                 * La taille est celle du tableau + 325 px.       
                 *************************************************************************/
                var taille_tableau_couleur = parseInt($("#tableau_couleurs").css("height")) + 325;
                $("#frm_couleurs").dialog({title: 'Gestion des couleurs',
                    width: 'auto',
                    height: 'auto',
                    //position:'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    open: function () { //Fond moins opaque pour voir les changements de couleurs
                        change_regle(".ui-widget-overlay", 'opacity', '0.3');
                    },
                    close: function () { //Fon opaque valeur par defaut
                        change_regle(".ui-widget-overlay", 'opacity', '0.8');
                    },
                    buttons: [{
                            text: "Aleatoire",
                            'click': function () {
                                regenere_couleurs();
                            },
                            icons: {primary: 'ui-icon-refresh'}},
                        {
                            text: "Enregistre",
                            'click': function () {
                                $.post("ajax/enregistre_couleurs.php", $("#frm_couleurs").serialize());
                                $("#frm_couleur_msg").show()
                                        .html("<p style='color:green;'>Enregistrement OK!</p>")
                                        .fadeOut(2000);
                            },
                            icons: {primary: 'ui-icon-disk'}},
                        {
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]
                });

                $("#frm_couleurs").dialog("option", "height", taille_tableau_couleur);
                /*************************************************************************
                 * Initialisation du selecteur de couleurs pour le formulaire 
                 * de changement de couleurs des tableaux
                 *************************************************************************/
                $(".couleur").minicolors({
                    control: "saturation",
                    hide: function () {
                        var id = $(this).attr("id");
                        var hexa = $(this).val();
                        var rgb_ = hex2rgb(hexa);
                        if (id == 'Couleur_texte') {
                            text_noir_blanc(rgb_); //pour modifier la couleur du texte dans l'échéancier
                        }
                        else {
                            change_regle("." + id, "backgroundColor", "rgb(" + rgb_.r + "," + rgb_.g + "," + rgb_.b + ")");
                        }
                    }

                });
                /*************************************************************************
                 * Definition du formulaire menu echeancier
                 *************************************************************************/
                $("#frm_menu_ech").dialog({title: 'Menu échéancier',
                    width: 'auto',
                    height: 'auto',
                    //position:'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [
                        {
                            text: "Ok",
                            'click': function () {
                                change_horaire();
                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]
                });
                /***********************************************************************
                 * formulaire config impression
                 ***********************************************************************/
                $("#frm_imprime").dialog({title: 'Menu configuration impression',
                    width: 'auto',
                    height: 'auto',
                    //position:'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [
                        {
                            text: "Imprime",
                            'click': function () {
                                imprime($('input[name=orientation]:radio:checked').val(), $('input[name=format]:radio:checked').val())
                            },
                            icons: {primary: 'ui-icon-print'}
                        },
                        {
                            text: "Ok",
                            'click': function () {
                                change_horaire();
                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }
                    ]
                });
                /***********************************************************************
                 * Fonction pour appeler le formulaire sur clic dans le menu déroulant
                 ***********************************************************************/
                $("#clic_imprime").click(function () {
                    $("#frm_imprime").dialog("open");
                });
                /************************************************************************ 
                 * Initialisation presentation des boutons du menu echeancier
                 ************************************************************************/
                $("#frm_menu_ech button").button();
                /************************************************************************ 
                 * Action lors du changement de la durée de rafraichissement par
                 * le select du formulaire        
                 ************************************************************************/
                $("#rafraichissement").on("change", function () {
                    var sel = $(this);
                    tempo = parseInt(sel.val()); //Affectation à la temporisation
                    //Mise à jour de l'affichage dans le select dans le menu déroulant
                    $("#menu_echeancier span").removeClass();
                    $("#menu_echeancier a").each(function () {
                        var el = $(this)
                        if (!(el.html().indexOf(tempo + " s") == -1)) {
                            el.children().addClass('ui-menu-icon ui-icon-check ui-icon');
                        }
                    }
                    );
                });
                /************************************************************************ 
                 * Fonction pour tester si click droit
                 ************************************************************************/
                function isRightClick(event) {
                    return event.button == 2;
                }
                /***********************************************************
                 * Clic-droit sur l'echeancier=>Affiche formulaire de configuration
                 ***********************************************************/
                $(document).delegate("#echeancier", "mousedown", function (event) {
                    var self = $(this);
                    event.stopPropagation(); // Stop it bubbling

                    // Make sure it needs to be shown
                    function showIt(event) {
                        return isRightClick(event) && $(event.target).closest('#echeancier')[0] == self[0];
                    }

                    if (!showIt(event)) {
                        return true;
                    }
                    /***********************************************************
                     * Affichage du formulaire
                     ***********************************************************/
                    $("#frm_menu_ech").dialog("open");

                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function () {
                            return false;
                        });

                /*************************************************************************
                 * En fonction du type d'échéancier on recalcul ou pas les horaires
                 *************************************************************************/
                $("#type_echeancier").change(function () {
                    var choix = $(this).val();
                    if (choix == "C") {
                        $(".titre").show();
                        $("#aff_decalage").show();
                    }
                    else {
                        $(".titre").hide();
                        $("#aff_decalage").hide();

                    }
                    return false;
                });

                function change_horaire() {
                    var choix = $("#type_echeancier").val();
                    if (choix == "C") {
                        var decalage = $("#decalage_horaire").val();
                        $.ajax({type: 'POST',
                            url: "ajax/enregistre_decalage.php",
                            data: {num_titre:<?php echo $num_titre; ?>,
                                decalage: decalage}
                        });
                        decalage = heure_en_minute(decalage);
                        $(".horaire").each(function () {
                            $(this).html(minute_en_heure(heure_en_minute($(this).parent().attr("id")) - decalage));
                        });
                    }
                    else {
                        $(".horaire").each(function () {
                            $(this).html($(this).parent().attr("id"));
                        });
                    }
                }
                $("#msg").hide(); //cache le div de message pour pouvoir l'activer avec $("#msg").show();  

                //Sur clic de recalcul
                //Si la case est décochée alors on retabli les tranches horaires initiales
                //qui sont egalement l'id de la ligne
                $("#recalcul").click(function () {
                    if ($(this).prop('checked') == false) {
                        $(".horaire").each(function () {
                            $(this).html($(this).parent().attr("id"));
                        })
                    }
                    else {

                        affiche_horaires_reels();

                    }
                });
                //Si pas en mode debug alors on lance les fonctions de rafraichissement
                if (debug == false) {
                    //Lancement de la boucle de  rafraichissment des données
                    connect();
                    //Lancement du timer pour l'horloge  
                    timer();
                }



            }); // Fin de préparation du document
            /*****************************************************************************
             * Ouverture du formulaire de gestion des couleurs des tableaux
             *****************************************************************************/
            function gestion_couleurs() {

                $("#frm_couleurs").dialog("open");
            }
            ;

            /**********************************************************
             * Boutons du formulaire de configuration echeancier
             **********************************************************/
            $("#noir_blanc").button({text: "Echéancier en noir et blanc"}).click(function () {
                change_couleur(this);
                if ($(this).button('option', 'text') == "Echéancier en noir et blanc") {
                    $(this).button('option', 'text', "Echéancier en couleurs");
                    $("#noir_blanc span").html("Echéancier en couleurs");
                    $("#change_coul").html("En couleur");
                }
                else {
                    $(this).button('option', 'text', "Echéancier en noir et blanc");
                    $("#noir_blanc span").html("Echéancier en noir et blanc");
                    $("#change_coul").html("En noir et blanc");
                }
                return false;
            });


        </script>
        <style type="text/css">
            #msg {
                background-color: white;
                border-style: solid;
                color: red;
                display: block;
                font-size: 57px;
                left: 631.5px;
                position: fixed;
                top: 462.5px;
                width : 600px;
                height: 200px;
            }
            /******************************************************************************
             * Mise en évidence des match en cours par clignotement                       
             ******************************************************************************/  
            @-moz-keyframes blink {0%{opacity:1;} 50%{opacity:0.6;} 100%{opacity:1;}} /* Firefox */
            @-webkit-keyframes blink {0%{opacity:1;} 50%{opacity:0.6;} 100%{opacity:1;}} /* Webkit */
            @-ms-keyframes blink {0%{opacity:1;} 50%{opacity:0.6;} 100%{opacity:1;}} /* IE */
            @keyframes blink {0%{opacity:1;} 50%{opacity:0.6;} 100%{opacity:1;}} /* Opera and prob css3 final iteration */
            .etat_1 {
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
            @font-face {  
                font-family: "digital";  
                src: url( css/fonts/digitaldreamfat.eot ); /* IE */  
                src: local("DIGITALDREAMFAT"), url( css/fonts/DIGITALDREAMFAT.ttf ) format("truetype"); /* non-IE */  
            }  

            /* THEN use like you would any other font */  
            #horloge { 
                font-family:"digital", verdana, helvetica, sans-serif;

            }
        </style>
    </head>
    <body>

        <?php
        include ("menu.5.1.php");
        ?>
        <div id="msg"><span id="msg_txt"></span></ br> Rechargement de la page en cours dans <span id="decompte">11</span>&nbsp;s !!! </div>
        <div style="float:left; text-align:center;" ><h1><?php echo $titre; ?><br />&nbsp;<span id="avance"></span><span class="titre" style="display:none">HORAIRES CONVOCATIONS </span></h1></div><div style="float:right;"><h2><span id="horloge"></span></h2></div>
        <div id="div_table" style="overflow-x: hidden; overflow-y: scroll; height:700px; width:100%; float:left;">
            <?php echo $tableau; ?>
        </div>



        <div style="overflow-x:hidden;" id="modal_form">
            <input type="hidden" id="num_titre" />
            <fieldset>          

                Lieu et date : <input type="text" id="lieu_date" /><button class="deroule" id="lst_lieu_date"></button>

            </fieldset>
            <div style="float:right;font-size:8px;font-style:italic;">version 1.0</div>
        </div>
        <!-- Formulaire de gestion des couleurs de l'échéancier -->
        <form id="frm_couleurs">
            <?php echo $formulaire_couleurs; ?>
            <span id="frm_couleur_msg"></span>
        </form>
        <!-- Formulaire de menu echeancier -->
        <div id="frm_menu_ech">
            <button id="noir_blanc">Echéancier en noir et blanc</button><br /> <br />
            <button id="chg_couleur">Gestion les couleurs</button><br /> <br />
            Rafraichissement&nbsp;:&nbsp;<select id="rafraichissement"><option value="1">1 s</option>
                <option selected value="5">5 s</option>
                <option value="10">10 s</option>
                <option value="15">15 s</option>
            </select><br /> <br />
            Recalcul tranches horaires : <input id='recalcul' type='checkbox' checked='checked' /> <br />                             
            <button onclick='$("#frm_imprime").dialog("open");'>Imprime</button><br /> <br />
            <button id="chg_ech">Change d'échéancier</button ><br /><br />
            Type échéancier&nbsp;&nbsp;<select id='type_echeancier'>
                <option value="M" selected>Matchs</option>
                <option value="C">Convocations</option>
            </select><br /><br />
            <div id="aff_decalage" style="display:none;">
                Décalage xxhmm : <input id="decalage_horaire" type="text" value="<?php echo $decalage_horaire; ?>">
            </div>
        </div>
        <!-- Formulaire pour choix format et orientation impression -->
        <div id="frm_imprime">
            Format : <input id="format" name='format' type="radio" value="A4" checked='checked'>A4  <input id="format" name='format' type="radio" value="A3">A3 <br />
            Orientation :   <input id="orientation" name='orientation' type="radio" value="L" checked='checked'>Paysage  <input id="orientation" name='orientation' type="radio" value="P">Portrait
        </div>

    </body>

</html>
