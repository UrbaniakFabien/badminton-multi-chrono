<?php
session_start();

/* * *********************************************************************************************
 * Version 2.0 
 *  Gestion de plusieurs chronos sur différents terrains pour le decompte du temps entre l'appel du match
 * et le début du match
 * Gere également l'échéancier 
 *   le match est en cours ou terminé
 *   le numéro du terrain
 *   l'heure de début et l'heure de fin de match
 *   
 * Affichage de diverses statistiques
 * 
 * FU
 * 2012   
 * 
 *  Version 3.0  
 * Affichage possible de plus de terrains que de terrain sur l'échéancier
 * Possibilité de relancer un match arreté par erreur (en gardant l'heure de début)
 * Possibilité d'arreter un match relancé par erreur (en gardant l'heure de fin)  
 * FU
 * 12/2012         
 * 
 * Version 4.0
 * Gestion de plusieurs échéanciers en //  
 * Le N° d'échéancier est passé en parametre dans les données  de la session
 *   S'il n'existe pas on le demande 
 * Menu configuration Chrono sur clic droit     
 * Verrouillage des terrains pour empecher leur déplacements ou leur changement d'orientation
 * Changement d'apparence du menu  
 * 
 * Version 4.1
 * Changement menu
 * Reprise des informations contenue dans l'échéancier en cas de reprise  
 * 
 * Version 4.2
 * Gestion des joueurs présent pour un N° match   
 *   Affichage dynamique dans infobulle avec couleur suivant état joueurs (non pointé,présent,WO)
 *      
 * Version 5
 * Passage à UTF-8 (code et mysql)
 * Enregistrement à la demande de la configuration
 * Adaptation de l'affichage de l'heure de fin de match en fonction du zoom demandé
 * Contrôle des droits d'accés par le menu general
 * Modification de l'affichage de l'échéancier (cellules arrondies)
 * Menu de l'échéancier sur clic droit dans l'échéancier  
 * Generation commune de l'échéancier. 
 * 
 * Version 5.1
 *  Optimisation de l'enregistrement des parametres des terrains
 *  Déport du code php    
 *  Gestion des classe liées au spécialités en majuscule 
 *  Gestion des noms prenoms composés dans la liste des jouers par match
 *  Parametrage de l'affichage ou pas des info-bulles liste des joueurs par match  
 *  Gestion des couleurs de l'echeancier 
 *  Changement d'apparence et de getsion des boutons stop/start 
 *  Changement de la couleur d'affichage du temps du chrono : Noir->Stop Blanc->en cours
 *  Initialisation du formulaire de configuration par la partie serveur pour les objets modifiés par Jquery.
 *     
 * Version 5.2
 *  Peux Fonctionner sans échéancier : Multi-chrono seulement.
 *  Gestion d'une table de couleur unique pour tout les échéanciers 
 *  Gestion de configurations préférées 
 *  
 * Version 6
 *  Clic droit sur terrain pour neutralisation   
 *  test si tous joueurs présents pour un match et montre pouce levé ou baissé 
 *   
 *  Version 7
 *  Passage a Jquery 2.x
 *  Nouveau menu 100%jquery
 *  recalcul de l'horaire des tranche dans l'échéancier en fonction de l'vancée du tournoi    
 * ********************************************************************************************** */

$num_titre = isset($_GET["num_titre"]) ? $_GET["num_titre"] : 0;
if ($num_titre == 0) {
    $num_titre = isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"] : 0;
}
$appelant = "chrono_ech.7";
if ($num_titre == 0) {
    $titre = "chrono et échéancier";

    include ("demande_num.7.php");
    exit();
}
if ($num_titre > 0) {
    $type_echeancier = "M";
// Génération de l'échéancier  ->$tableau
    include ("genere_echeancier.5.2.php");
} else {
    $tableau = "";
    $titre = "";
}
//Définitions des terrains 
$max_terrains = 30;  //Nombre max de terrains possible
$lim_temps1 = ((2 * 60) + 30); //2.30 min 
$lim_temps2 = 3 * 60; // 3 min 
$str_lim_temps2 = "03:00";
$terrains = "";
$nbr_terrain = 1; //Nombre min terrain par defaut
for ($i = 1; $i <= $max_terrains; $i++) {
    $terrains .= '
    <div id="draggable_' . $i . '" class="draggableh draggable ui-widget-content non_u" style="display:none;" title="Pas de liste de joueurs">
    <input type="hidden" id="memo_temp' . $i . '" value=0 />
    <div class="match_h" >N&deg;&nbsp;<input id="match_' . $i . '" value=0 class="match" onclick="max_match(' . $i . ');" onchange="controle(this);"/></div> <div class="fin_h" id="fin_' . $i . '">00h00</div>
    <div class="centre_texte_h">' . $i . '</div> 
    <div class="bouton_h"><button class="go_stop" id="btn_' . $i . '">Start</button></div> <div class="temps_h" id="affiche_temp_' . $i . '">00.00</div>
    <input type="hidden" id="memo_son' . $i . '" value=1 />
    <div class="pouce_baisse pouce" id="pouce_' . $i . '"></div>
    </div>';
}
if ($num_titre > 0) {
    //on cherche a initialiser les N° de matchs et l'affichage des terrains
    // en fonction de l'échéancier en cours
    //Lecture de l'horaire le plus tôt
    $sql = "select min(horaire) as min_horaire from echeancier where num_titre=" . $num_titre;
    $result = exec_commande($sql);
    $nbr_terrain = 1; //Nombre de terrain min par défaut. Sera passé en parametre à la fonction de choix de nombre de terrain 
    $num_terrain = "";
    if ($data = mysqli_fetch_row($result)) {
        //Lecture de tous les matchs de cet horaire triés par ordre de n° match
        $sql = "select num_match from echeancier where horaire='" . $data[0] . "'and num_titre=" . $num_titre . " order by num_match;";

        $result = exec_commande($sql);
        $nbr_terrain = mysqli_num_rows($result); //Nombre d'enregistrements = Nombre de terrains
        //On cherche tous les N° de match qui ne sont pas des pauses et pas deja joués et pas WO dans la limite du nombre de terrains
        $sql = "select num_match from echeancier 
                        where horaire>='" . $data[0] . "' and spe<>'Pause' and etat in (0,-1) and num_titre=" . $num_titre . " 
                        order by num_match limit " . $nbr_terrain . ";";

        $result = exec_commande($sql);
        while ($data = mysqli_fetch_row($result)) {
            $num_terrain .= $data[0] . ",";
        }
    }
} else {
    $num_terrain = "";
}
if ($num_terrain != '') {
    $fonction_init_num_match = '
                        var tab_num=Array(' . substr($num_terrain, 0, -1) . ');
                        for (i=1;i<=tab_num.length;i++) {
                             $("#draggable_"+i).show();
                             $("#match_"+i).val(tab_num[i-1]);
                        }
                
                  ';
} else {
    $fonction_init_num_match = '$("#draggable_1").show();';
}


$formulaire_couleurs = "";
$existe_liste = 'false';
if ($num_titre < 0) {
    $tab_classe = "";
}
if ($num_titre > 0) {
    /*     * *****************************************************************************
     * Génération du formulaire pour la saisie des couleurs par Tableau
     * ***************************************************************************** */
    $formulaire_couleurs = "<table id='tableau_couleurs' style='margin:auto'><thead><tr><th>Tableau</th><th>Couleur</th></tr></thead><tbody>";
    foreach ($tab_couleur as $key => $e_tab_couleur) {
        if (($key != "PAUSE") && ($key != "horaire")) {
            $formulaire_couleurs .= "<tr><td>" . str_replace($chg_sign, $sign, $key) . "</td><td style='text-align:center;'><input size='3px'class='couleur' type='hidden' id='" . $key . "' name='" . $key . "' value='" . rgb2hex($e_tab_couleur["r"], $e_tab_couleur["g"], $e_tab_couleur["b"]) . "'>&nbsp;</td></tr>";
        }
    }
    $formulaire_couleurs .= "</tbody></table>";

    /*     * *****************************************************************************
     * Test si existe une liste de joueurs pour cet échéancier
     * ***************************************************************************** */
    $sql = "SELECT * 
            FROM joueurs
            WHERE num_titre=" . $num_titre . "
            LIMIT 1";
    $result = exec_commande($sql);
    /*     * *****************************************************************************
     * Determine si une liste de joueurs existe
     * si oui on permet l'affichage de la liste des joueurs par terrain/match dans le client
     * si non l'option est à faux dans le client
     * ***************************************************************************** */
    $existe_liste = 'false'; //Drapeau qui indique si une liste de joueurs existe pour cet echeancier
    if ($data = mysqli_fetch_array($result)) {
        $existe_liste = 'true';
    }
}
/* * *****************************************************************************
 * Initialisation des couleurs et zoom par le serveur
 * ***************************************************************************** */

$couleur_terrain_libre = "#33CC00";
$couleur_terrain_occupe = "#FF0000";
$couleur_terrain_neutre = "#A7BDE9";
$couleur_salle = "";
$zoom = 1;
if ($num_titre > 0) {
    $sql = "select * from tbl_config_chrono where num_titre=" . $num_titre;
    $result = exec_commande($sql);
    if ($data = mysqli_fetch_array($result)) {
        $couleur_terrain_libre = $data["Conf_coul_libre"];
        $couleur_terrain_occupe = $data["Conf_coul_occup"];
        $couleur_salle = $data["Conf_coul_salle"];
        $zoom = $data["Conf_zoom"];
        $couleur_terrain_neutre = $data["Conf_coul_neutre"];
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title><?php echo $titre; ?></title>
        <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link id="css_coul_" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" >
        <link rel="stylesheet" type="text/css" href="css/multi-chrono.css" />
        <link rel="stylesheet" type="text/css" href="jquery/jquery-miniColors/jquery.miniColors.css" />


        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>

        <script type="text/javascript" src="jquery/js/jquery.scrollTo-1.4.3.1-min.js"></script>
        <script type="text/javascript" src="jquery/jquery-miniColors/jquery.miniColors.js"></script>
        <script type="text/javascript" src="js/echeancier.js"></script>
        <script type="text/javascript" src="js/mesfonctions.js"></script>
        <script type="text/javascript">
            /***************************************************************
             * Fonction pour connaitre le browser utilisé
             **************************************************************/
            if (!$.browser) {
                $.browser = {chrome: false, mozilla: false, opera: false, msie: false, safari: false};
                var ua = navigator.userAgent;
                $.each($.browser, function (c, a) {
                    $.browser[c] = ((new RegExp(c, 'i').test(ua))) ? true : false;
                    if ($.browser.mozilla && c == 'mozilla') {
                        $.browser.mozilla = ((new RegExp('firefox', 'i').test(ua))) ? true : false;
                    }
                    ;
                    if ($.browser.chrome && c == 'safari') {
                        $.browser.safari = false;
                    }
                    ;
                });
            }
            /***********************************************
             * Module de gestion des terrains dans le multi chrono
             ***********************************************/
            /*Variables globales par defaut pour gestion des chronos  
             var temps_1             Limite de temps 1
             var temps_2             Limite de temps 2
             var str_temps_2         Affichage en mode string du temps2
             var temps_sens = 0/1    Sens du comptage temps
             var audio               Audio actif ou pas 
             var tab_class           Tableau des classe
             var debug = true/false  Permet de debuguer sans les timers 
             var existe_liste = true/false  Si false pas d'affichage de joueurs
             var existe_echeancier = true/false  Si false pas d'affichage echeancier
             */

            var temps_1 =<?php echo $lim_temps1; ?>,
                    temps_2 =<?php echo $lim_temps2; ?>,
                    str_temps_2 = "<?php echo $str_lim_temps2; ?>",
                    temps_sens = 0,
                    audio,
                    tab_class = Array(<?php echo $tab_classe ?>),
                    existe_liste =<?php echo $existe_liste; ?>,
                    existe_echeancier =<?php echo $num_titre > 0 ? "true" : "false"; ?>,
                    ecart = <?php echo $ecart; ?>,
                    debug = false;



            function change_ech() {
                $("#modal_form").dialog("open");
            }


            /*******************************************************
             * Mise a jour directe de l'échéancier affiché
             *******************************************************/
            function  Mise_a_jour_echeancier(p_id, p_etat, p_terrain, p_heure) {
                var $this = $("#num_" + p_id);
                if (existe_echeancier) {
                    $this.removeClass()
                            .toggleClass("etat_" + p_etat, p_etat !== 0)
                            .toggleClass($("#classe_" + p_id).val(), p_etat == "0" || p_etat == "3");
                    $("#input_num_" + p_id).html(p_terrain);

                    switch (p_etat) {
                        case 0 :
                            $this.children("span:eq(1)").html("");  // efface l'heure de début
                            $this.children("span:eq(2)").html("");  // efface l'heure de fin
                            $this.children("span:eq(0)").hide();    // cache le N° de terrain
                            break;
                        case 1 :
                            if (p_heure !== "99h99") {
                                //si l'heure <>99h99 on met a jour l'heure de dbut de match
                                $this.children("span:eq(1)").html("D&eacute;but : " + p_heure + "<br />");
                            }
                            //match relancé : on se contente d'effacer l'heure de fin de match
                            $this.children("span:eq(2)").html("");  // efface l'heure de fin 

                            $this.children("span:eq(0)").show();

                            //Si on a demandé le recalcul des tranche horaires
                            if ($('#recalcul').prop('checked')) {
                                affiche_horaires_reels();

                            }
                            break;
                        case 2 :
                            $this.children("span:eq(2)").html("Fin : " + p_heure + "<br />");
                            break;


                    }
                    //Pour scroller vers le 1er match en cours dans l'échéancier aprés le changement de N° de terrain
                    var cible = ".etat_1:first";
                    if ($(cible).length > 0) {
                        //Scroll vers la ligne identifiée si elle existe
                        $("#table").scrollTo(cible, 800);
                    }
                }
            }
            /********************************************
             * Repositionnement des terrains
             * en fonction des données sauvegardées dans la table
             * des parametres      
             ********************************************/
            function callComplete_multi(reponse) {
                if (reponse.length > 0) {
                    $(".draggable").hide(); //On cache tous les terrains
                    //Affichage des seuls terrains demandés
                    for (i = 0; i < reponse.length - 2; i++) {
                        $("#draggable_" + i).show();
                    }
                    //Initialisation du nombre de terrain demandé par l'utilisateur
                    $("#nbr_terrain").val(reponse.length - 2);
                    //Remise en position des terrains
                    for (i = 0; i < reponse.length; i++) {
                        top_ = parseInt(reponse[i].top);
                        left_ = parseInt(reponse[i].left);
                        if (parseInt(reponse[i].num) < 100) {
                            //repositionnment des terrains  
                            $this = $("#draggable_" + reponse[i].num);
                            $this.show();
                            $this.removeClass()
                                    .addClass("draggable" + reponse[i].orientation + " draggable ui-widget-content stop")
                                    .offset({left: left_, top: top_});
                            $this.children("div:eq(2)").toggleClass("centre_texte_v", reponse[i].orientation == 'v')
                                    .toggleClass("centre_texte_h", reponse[i].orientation == 'h');
                            $this.children("div:eq(3)").toggleClass("bouton_v", reponse[i].orientation == 'v')
                                    .toggleClass("bouton_h", reponse[i].orientation == 'h');
                            $this.children("div:eq(4)").toggleClass("temps_v", reponse[i].orientation == 'v')
                                    .toggleClass("temps_h", reponse[i].orientation == 'h');
                        } else {
                            //Repositionnment de l'échéancier et de la salle
                            switch (reponse[i].num) {
                                case "101" :
                                    $("#salle").height(left_);
                                    break;
                                case "100"  :
                                    $("#contenaire_echeancier").height(left_);
                                    break;
                            }

                        }

                    }
                }

                // Reprise des informations depuis echeancier
                //pour réafficher correctement les données dans les terrains en cas d'interruption

                //Phase 1
                // Remet en etat les terrains : etat, numero de match
                $.ajax({
                    type: "POST",
                    url: "ajax/match_en_cours.php",
                    dataType: "json",
                    success: function (data) {
                        for (i = 0; i <= data.length - 1; i++) {
                            var terrain = $("#draggable_" + data[i].terrain),
                                    match = $("#match_" + data[i].terrain),
                                    pouce = $("#pouce_" + data[i].terrain);
                            //Remise en etat en cours pour le terrain
                            terrain.removeClass("stop")
                                    .addClass("go");
                            //Reprise du N° de match
                            match.val(data[i].num_match)
                                    .attr("disabled", "disabled");
                            //Remise en place des pouces levés
                            pouce.addClass("pouce_leve")
                                    .removeClass("pouce_baisse");
                        }
                    }
                });
                //Phase 2
                // Reprise des heure de fin de matchs précédents

                $.ajax({
                    type: "POST",
                    url: "ajax/derniers_matchs_termines.php",
                    dataType: "json",
                    success: function (data) {
                        for (i = 0; i <= data.length - 1; i++) {
                            var heure_fin = $("#fin_" + data[i].terrain);
                            //Reprise heure fin de match précédent
                            heure_fin.html(data[i].h_fin);
                        }
                    }
                });
                //Phase 3
                // attribution des numéro de matchs aux terrains libres  et visibles
                $.ajax({
                    type: "POST",
                    url: "ajax/matchs_a_jouer.php",
                    dataType: "json",
                    success: function (data) {
                        var i = 0,
                                tab_tmp,
                                id = 0;

                        $(".stop").children("div").children(".match").each(function () {
                            var terrain = $(this);
                            if (terrain.parent().parent().css("display") == "block") {
                                id = terrain.parent().parent().attr("id");
                                if (i <= data.length - 1) {
                                    //on attribut le N° de match au terrain
                                    terrain.val(data[i].num_match);
                                    tab_tmp = id.split("_");
                                    //On test pour savoir si tous les joueurs du match sont présents
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax/pauseech.6.php",
                                        data: {num: data[i].num_match},
                                        async: false,
                                        dataType: 'json',
                                        success: function (reponse) {

                                            // test pour savoir si tous les joueurs de ce match sont présents
                                            //cas particulier : aucun joueur connu => par defaut tous présents
                                            if (reponse.absent == 0) {
                                                $("#pouce_" + tab_tmp[1]).removeClass("pouce_baisse")
                                                        .addClass("pouce_leve");

                                            } else {
                                                $("#pouce_" + tab_tmp[1]).removeClass("pouce_leve")
                                                        .addClass("pouce_baisse");
                                            }

                                        }
                                    });
                                }
                                i++;
                            }
                        });
                    }
                });
            }


            /***************************************************************
             * Fonction pour convertir un temps mm:ss -> s
             ****************************************************************/

            function conv_chaine_temps(p_str) {
                p_str = p_str.replace("h", ":");
                var tmp = p_str.split(":");
                if (tmp.length > 0) {
                    return (parseInt(tmp[0]) * 60) + parseInt(tmp[1]);
                } else {
                    return 0;
                }
            }


            /***********************************************
             * Pour remettre à 0 les position des terrains
             * *********************************************/
            function raz() {

                $(".draggable").each(function () {
                    var $this = $(this);
                    $this.removeClass()
                            .addClass("draggableh draggable ui-widget-content stop")
                            .css("left", 0)
                            .css("top", 0);

                    $this.children("div:eq(2)").removeClass()
                            .addClass("centre_texte_h");

                    $this.children("div:eq(3)").removeClass()
                            .addClass("bouton_h");

                    $this.children("div:eq(4)").removeClass()
                            .addClass("temps_h");

                    i++;
                });

            }
            /***************************************************************
             * Initialisation de la salle
             ***************************************************************/
            function init_multi() {
                //La salle peut être déplacée dans l'écran
                $("#salle").draggable();

                //initialisation des terrains
                $(".draggable").draggable({snap: true,
                    containment: "#salle",
                    scroll: true,
                    snapMode: "inner",
                    stop: function (event, ui) {
                        //Suppression des caractéristiques height et width ajoutées lors du déplacement
                        //Ces informations sont dans la class  et inutiles dans le style de l'élément déplacé
                        //FU
                        //04/2015
                        $(this).css("height", "")
                                .css("width", "");
                    }
                }
                );
                /***********************************************************
                 * Gestion changement orientation des terrains
                 ***********************************************************/
                $(".draggable").on('dblclick', function () {
                    var $thisParagraph = $(this),
                            count = 0;
                    //Le double clic sur le terrain n'est actif que si le 'verrou' est ouvert
                    if ($("#verrou").button("option", "icons").primary == "ui-icon-unlocked") {
                        if ($thisParagraph.hasClass("draggablev")) {
                            count = 1;
                        } else {
                            count = 0;
                        }
                        $thisParagraph.toggleClass("draggablev", count == 0)
                                .toggleClass("draggableh", count == 1);
                        $thisParagraph.children("div:eq(2)").toggleClass("centre_texte_v", count == 0)
                                .toggleClass("centre_texte_h", count == 1);
                        $thisParagraph.children("div:eq(3)").toggleClass("bouton_v", count == 0)
                                .toggleClass("bouton_h", count == 1);
                        $thisParagraph.children("div:eq(4)").toggleClass("temps_v", count == 0)
                                .toggleClass("temps_h", count == 1);



                    }
                });
                /***********************************************************
                 * Gestion stop go des chronos
                 ***********************************************************/
                $(".go_stop").on('click', function () {
                    var $btn = $(this);
                    var Sys_date_heure = new Date();
                    var heure = Sys_date_heure.getHours();
                    var min = Sys_date_heure.getMinutes();
                    min = ("00" + min).slice(-2);
                    heure = ("00" + heure).slice(-2);
                    if (!($btn.html().indexOf("Start") == -1)) {
                        /***************************************************************************************
                         * si Start alors  Contrôle que ce N° de match n'est pas deja joué
                         *                changement du nom du bouton                        
                         *                envoi a la base des informations pour mise a jour table echeancier
                         *                Mise à jour de la zone  de début de match                         
                         **************************************************************************************/
                        /* on reccupere le N° du match */
                        var id = $(this).parent().parent().children("div").children("input:first").val();
                        var tmp = $(this).attr("id").split("_");
                        var id_obj = tmp[1];
                        $("#reponse").val("");
                        $("#source").val("btn");
                        $.ajax({
                            type: "POST",
                            url: "ajax/controle_match.php",
                            data: {num: id},
                            async: false,
                            timeout: 2000,
                            error: function (o_jqXrt, status) {
                                alert('erreur lors du controle match : ' + status);
                            },
                            success: function (data) {

                                if (data == "2") {

                                    $("#etat").val("2");
                                    $("#prompt").html("Le match " + id + " a deja &eacute;t&eacute; jou&eacute; !<br />Souhaitez vous r&eacute;initialiser l'heure de d&eacute;but de match ?");
                                    $("#objet").val(id_obj);
                                    $("#prompt-form").dialog("open");

                                }
                                if (data == "3") {

                                    $("#etat").val("3");
                                    $("#prompt").html("Le match " + id + " est &agrave; l'etat  WO !<br />Souhaitez vous lancer ce match (annule WO) ?");
                                    $("#objet").val(id_obj);
                                    $("#prompt-form").dialog("open");

                                }
                                if ((data == "2") || (data == "3")) {
                                    $("#reponse").val("");
                                } else {
                                    $("#reponse").val("O");
                                }
                            }
                        });


                        /* on change l'intitulé du bouton 
                         et la couleur du terrain
                         et la couleur du texte temps
                         */
                        if ($("#reponse").val() == "O") {
                            $btn.html($btn.html().replace('Start', 'Stop')); //Changement du texte du bouton
                            $("#affiche_temp_" + id_obj).css("color", "white");  //Changement de la couleur noir->blanc    
                            $(this).parent().parent().addClass("go")        //Changement de la couleur du terrain
                                    .removeClass("stop");
                            /*et on interdit la saisie */
                            $(this).parent().parent().children("div").children("input:first").attr("disabled", "disabled");
                            var terrain = $(this).parent().parent().children('div:eq(2)').html();
                            $.ajax({
                                type: "POST",
                                url: "ajax/majech.1.1.php",
                                data: {num: "num_" + id,
                                    etat: 1,
                                    terrain: terrain,
                                    heure: heure + "h" + min},
                                dataType: "json",
                                success: function (data) {
                                    for (i = 0; i <= data.length - 1; i++) {
                                        Mise_a_jour_echeancier(data[i].num_match, data[i].etat, 0, "00h00");
                                    }
                                }


                            });
                            Mise_a_jour_echeancier(id, 1, terrain, heure + "h" + min);//mise a jour directe de l'échéancier affiché
                        }
                    } else
                    {
                        $btn.html($btn.html().replace('Stop', 'Start'));

                        tmp = $btn.attr("id").split("_");
                        var zone_temp = $("#affiche_temp_" + tmp[1]);

                        if (temps_sens == 0) {
                            zone_temp.html("00:00");
                            $("#memo_temp" + tmp[1]).val(0);
                        } else {
                            zone_temp.html(str_temps_2);
                            $("#memo_temp" + tmp[1]).val(temps_2);
                        }
                        zone_temp.show()
                                .css("color", "black");  //Changement de la couleur noir->blanc
                        $("#draggable_" + tmp[1]).removeClass("orange");

                    }
                });
                /***********************************************************
                 * Gestion Double clic sur la zone heure de fin du terrain
                 ***********************************************************/
                $(".fin_h").on('dblclick', function () {
                    var $btn = $(this);
                    // Test si match pas deja terminé
                    var id = $(this).parent().children("div").children("input:first").val(); // N° de match
                    var tmp = $(this).attr("id").split("_");
                    var id_obj = tmp[1];
                    $("#reponse").val("");
                    $("#source").val("zone_heure");
                    $.ajax({
                        type: "POST",
                        url: "ajax/controle_match.php",
                        data: "num=" + id,
                        async: false,
                        timeout: 2000,
                        error: function () {
                            alert("Probleme de liaison avec le serveur !")
                        },
                        success: function (data) {
                            if (data == "2") {
                                $("#prompt").html("Le match " + id + " deja termin&eacute; !<br />Souhaitez vous mettre &agrave; jour l'heure de fin de match ?");
                                $("#prompt-form").dialog("open");

                            } else {
                                $("#reponse").val("O");
                            }
                        }
                    });


                    /* on change l'intitulé du bouton 
                     et la couleur du terrain
                     */
                    if ($("#reponse").val() == "O") {
                        //Heure systeme
                        var Sys_date_heure = new Date();
                        var heure = Sys_date_heure.getHours();
                        var min = Sys_date_heure.getMinutes();
                        if (min < 10) {
                            min = "0" + min;
                        }
                        $btn.html(heure + "h" + min);
                        $(this).parent().addClass("stop")
                                .removeClass("go")
                                .removeClass("orange");



                        var terrain = $(this).parent().children('div:eq(2)').html();
                        if (temps_sens == 0) {
                            $(this).parent().children("div:eq(4)").html("00:00")
                                    .css("color", "black");
                            $(this).parent().children("input:first").val("0");  //Remise à zero du chrono du terrain

                        } else {
                            $(this).parent().children("div:eq(4)").html(str_temps_2)
                                    .css("color", "black");
                            $(this).parent().children("input:first").val(temps_2);  //Remise à temps_2 du chrono du terrain

                        }
                        $(this).parent().children("div:eq(0)").children().removeAttr("disabled"); // On autorise le changement de N° de terrain
                        $(this).parent().children("div:eq(4)").show();
                        var texte = $(this).parent().children("div:eq(3)").children("button")
                        texte.html(texte.html().replace('Stop', 'Start'));
                        //on reactive le son pour ce terrain
                        tmp = $btn.attr("id").split("_");
                        $("#memo_son" + tmp[1]).val(1);
                        //mise a jour de la table échéancier 
                        $.ajax({
                            type: "POST",
                            url: "ajax/majech.1.1.php",
                            data: {num: "num_" + id,
                                etat: 2,
                                terrain: terrain,
                                heure: heure + "h" + min},
                            dataType: "json",
                            success: function (data) {
                                for (i = 0; i <= data.length - 1; i++) {
                                    Mise_a_jour_echeancier(data[i].num_match, data[i].etat, 0, "00h00");
                                }
                            }
                        });
                        Mise_a_jour_echeancier(id, 2, terrain, heure + "h" + min);//mise a jour directe de l'échéancier affiché

                    }
                    return false;  //pour que le double clic n'agisse pas sur l'orientation du terrain
                });

                /*******************************************************
                 * Double Click sur zone temps=arret son
                 *******************************************************/
                $(".temps_h").on('dblclick', function () {
                    var zone = $(this);
                    $(this).attr("id");
                    var tab = $(this).attr("id").split("_");
                    var id = tab[2];
                    //on annule pour l'instant le son sur ce terrain
                    $("#memo_son" + id).val(0);
                    return false;  //pour que le double clic n'agisse pas sur l'orientation du terrain
                });
                /******************************************************************************
                 * initialisation des info-bulles : liste des joueurs par matchs sur terrains
                 ******************************************************************************/
                $(".draggable").tooltip({
                    show: {effect: "blind", delay: 2000},
                    content: function () {  // On cherche à retourner dans l'info bulle, la liste des joueurs pour le match
                        var terrain = $(this);
                        //si les infos-bulle sont activées et qu'il existe une liste
                        if (($("#info_bulle_lst_joueurs").prop("checked") == true) && (existe_liste)) {
                            var tmp = terrain.attr("id").split("_");
                            var num_match = $("#match_" + tmp[1]).val();
                            var liste = "";
                            $.ajax({
                                type: "POST",
                                url: "ajax/lst_joueurs_du_match.5.1.php",
                                data: {num_match: num_match},
                                dataType: "json",
                                async: false,
                                success: function (data) { //On créé la liste avec les élément retourné par l'appel
                                    var nbr_etat = 0;
                                    for (i = 0; i <= data.length - 1; i++) {
                                        liste = liste + "<p class='etat_j_" + data[i].etat + "'>" + data[i].nom + " " + data[i].club + "</p>";
                                        if (data[i].etat == "1") {
                                            nbr_etat++;
                                        }   //on compte le nombre de joueurs pointés présents
                                    }
                                    terrain.attr("title", liste);
                                    //Si tous les joueurs présents alors pouce levé
                                    if (nbr_etat == data.length) {
                                        if ($("#pouce_" + tmp[1]).hasClass("pouce_baisse")) {
                                            $("#pouce_" + tmp[1]).removeClass("pouce_baisse")
                                                    .addClass("pouce_leve");
                                        }
                                    } else {
                                        $("#pouce_" + tmp[1]).removeClass("pouce_leve")
                                                .addClass("pouce_baisse");
                                    }
                                }
                            })

                            return liste;
                        } else {
                            terrain.attr("title", "");

                            return "";
                        }
                    }
                });

                /*******************************************************
                 * Forcage de l'affichage des info-bulle même si les 
                 * terrains sont verrouillés
                 *******************************************************/
                $(".draggable").hover(function () {
                    var element = $(this);
                    if (element.hasClass("ui-draggable-disabled")) {
                        element.tooltip("open");
                        return false;
                    }
                });



                /***********************************************************
                 * Initialisation si possible des terrains
                 * Affichage et valeur par defaut des N° de match
                 ***********************************************************/
                function init_num_match() {

<?php
echo $fonction_init_num_match;
?>
                }
                /***********************************************************
                 * Gestion nbr terrains
                 ***********************************************************/
                $("#nbr_terrain").spinner({incremental: true,
                    max: <?php echo $max_terrains; ?>,
                    min: 1,
                    step: 1,
                    value: <?php echo $nbr_terrain; ?>,
                    spin: function (event, ui) {
                        for (i = 1; i <= ui.value; i++) {
                            if ($("#draggable_" + i).css("display") !== "block") {
                                $("#draggable_" + i).show()
                                        .offset($("#salle").offset());
                            }

                        }
                        for (i = ui.value + 1; i <=<?php echo $max_terrains; ?>; i++) {
                            $("#draggable_" + i).hide();
                        }
                        $("#draggable_1").show();
                    }
                }
                );

                /***********************************************************
                 * Initialisation affichage nombre de terrain
                 ***********************************************************/
                $("#nbr_terrain").val("<?php echo $nbr_terrain; ?>");
                /***********************************************************
                 * Initialise les premiers N° de match sur les terrains
                 ***********************************************************/
                init_num_match();
                /***********************************************************
                 * Pour ajouter les nouvelles options au menu par défaut
                 ***********************************************************/
                $("#chrono_ech").html('<a href="#">Chrono & échéancier</a>\n\
                                            <ul>\n\
                                                <li><a href="#"><span onclick="affiche_stat();">Statistiques</span></li>\n\
                                                <li><a href="#">Configuration</a>\n\
                                                    <ul>\n\
                                                        <li><a href="#">Echeancier</a>\n\
                                                            <ul>\n\
                                                                <li><a href="#"><span onclick="change_couleur(this);" id="change_coul">En noir et blanc</span></a></li>\n\
                                                                <li><a href="#"><span onclick="gestion_couleurs();" id="regenere_coul">Gestions Couleurs</span></a></li>\n\
                                                                <li><a href="#"><span onclick="change_ech();" id="change_ech">Change Echéancier</span></a></li>\n\
                                                            </ul>\n\
                                                        </li>\n\
                                                        <li><a href="#"><span onclick="go_couleur();">Chrono</span></a></li>\n\
                                                    </ul>\n\
                                                </li>\n\
                                            </ul>');

                /***************************************************************
                 * Menu général
                 * *************************************************************/
                $("#menuprinc").menu({position: {using: positionnerSousMenu}});


                /***********************************************************
                 * Repositionnement des terrains
                 ***********************************************************/
                $.post('ajax/majparam.php', {}, callComplete_multi, 'json');
            }
            ;/************Fin initialisation Salle ********************/

            /****************************************************************
             * Relance la procedure de generation de 
             * des nouvelles couleurs pour les classe de l'échéancier
             ****************************************************************/
            function regenere_couleurs() {
                for (i = 0; i < tab_class.length; i++) {
                    if (tab_class[i] !== "PAUSE") {
                        //génération couleur au hasard format hexa
                        var hex_couleur = couleur_hasard();
                        //conversion format rgb
                        var couleur = hex2rgb(hex_couleur);
                        //Mise a jour des champs du formulaire de gestion des couleurs
                        $("#" + tab_class[i]).val(hex_couleur)
                                .next().children().css("background-color", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                        change_regle("." + tab_class[i], "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                    }
                }
            }
            ;




            /***************************************************************
             * Recherche plus grand numéro de match en cours
             * et retourne le N° de match suivant                 
             ***************************************************************/
            function max_match(num)
            {
                var max = 0;

                /***********************************************************
                 * Recherche dans les terrains 
                 * le N° de match en cours , le plus grand 
                 ***********************************************************/
                for (i = 1; i <=<?php echo $max_terrains; ?>; i++) {
                    if ($("#draggable_" + i).css("display") == "block") {
                        if (parseInt($("#match_" + i).val()) > max) {
                            max = parseInt($("#match_" + i).val());
                        }
                    }
                }

                max = max + 1;
                //Controle si ce N° n'est pas une pause  ou WO
                $.ajax({
                    type: "POST",
                    url: "ajax/pauseech.6.php",
                    data: {num: max},
                    async: false,
                    dataType: 'json',
                    success: function (data) {
                        max = data.num_match;
                        // test pour savoir si tous les joueurs de ce match sont présents
                        //cas particulier : aucun joueur connu => par defaut tous présents
                        if (data.absent == 0) {
                            $("#pouce_" + num).removeClass("pouce_baisse")
                                    .addClass("pouce_leve");

                        } else {
                            $("#pouce_" + num).removeClass("pouce_leve")
                                    .addClass("pouce_baisse");
                        }

                    }
                });
                $("#match_" + num).val(max);
                /* var terrain=$("#draggable_"+num);
                 if (terrain.hasClass("neutralise")) {
                 terrain.removeClass("neutralise")
                 .addClass("stop");
                 } */

            }
            ;
            /***************************************************************
             * Chrono par terrains
             ***************************************************************/
            function timedCount()
            {
                $("#salle button").each(function () {
                    var $btn = $(this);
                    if (!($btn.html().indexOf("Stop") == -1)) {
                        tmp = $btn.attr("id").split("_");
                        var temp = parseInt($("#memo_temp" + tmp[1]).val());
                        var joue = false;//Pour savoir si on joue ou pas le beep
                        if (temps_sens == 0) { //increment du temps
                            temp = temp + 1;
                            $("#memo_temp" + tmp[1]).val(temp);
                            $("#affiche_temp_" + tmp[1]).html(formatSecondsAsTime(temp, "mm:ss"));
                            if (temp >= temps_1) {
                                joue = true;
                                $("#affiche_temp_" + tmp[1]).toggle(); // limite 1 dépassée=>temps clignote

                            }
                            if (temp >= temps_2) {
                                joue = true;
                                $("#draggable_" + tmp[1]).toggleClass("orange"); // limite 2 dépassée=>terrain clignote

                            }
                        } else { //decompte du temps
                            temp = temp - 1;
                            $("#memo_temp" + tmp[1]).val(temp);
                            $("#affiche_temp_" + tmp[1]).html(formatSecondsAsTime(temp, "mm:ss"));
                            if (temp <= temps_2 - temps_1) {
                                joue = true;
                                $("#affiche_temp_" + tmp[1]).toggle(); // limite 1 dépassée=>temps clignote

                            }
                            if (temp <= 0) {
                                joue = true;
                                $("#draggable_" + tmp[1]).toggleClass("orange"); // limite 2 dépassée=>terrain clignote

                            }
                        }
                        if ((joue) && ($("#son").prop('checked') == true) && ($("#memo_son" + tmp[1]).val() == 1)) {
                            play_sound();
                        }
                    }
                });
                var t = setTimeout("timedCount()", 1000);
            }
            ;



            /********************************************************
             * Affichage du formulaire de configuration des chronos
             * *****************************************************/
            function go_couleur() {

                $("#dialog-form").dialog("open");
            }
            /********************************************************
             * Affichage du formulaire des statistiques
             * *****************************************************/
            function affiche_stat() {
                $.ajax({
                    type: "POST",
                    url: "ajax/statistiques.5.1.php",
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        $("#joues").html(data.joues);
                        $("#en_cours").html(data.en_cours);
                        $("#restant").html(data.restant);
                        $("#tp_moyen").html(data.temps_moyen);
                        $("#tp_min").html("N° " + data.num_court + " : " + data.temps_min);
                        $("#tp_max").html("N° " + data.num_long + " : " + data.temps_max);
                        $("#wo").html(data.wo)
                    }
                });
                $("#stat-form").dialog("open");
            }
            /*******************************************************************
             * Pour vérifer que ce N° de match n'est pas déjà attribué
             * si la donnée n'est pas numerique ou =0 alors terrain mis en pause             
             *******************************************************************/
            function controle(p_obj) {
                if ($(p_obj).val() > 0) {
                    $(p_obj).parent().parent().removeClass("neutralise")
                            .addClass("stop");
                    $(".draggable").each(function () {
                        var obj_courant = $(this).children("div:eq(0)").children("input:first");
                        //On test si on est pas sur l'objet passé en paramétre et
                        //Que sa valeur est <> de la valeur de l'objet en cours
                        if (($(p_obj).val() == obj_courant.val()) && ($(p_obj).attr("id") !== obj_courant.attr("id"))) {
                            // Message
                            $("#message").html("Ce N° de match est deja attribué au terrain " + obj_courant.parent().parent().children("div:eq(2)").html());
                            $("#obj").val($(p_obj).attr("id"));
                            //Affichage du message
                            $("#msg-form").dialog("open");
                            return false;
                        }
                    });
                } else {
                    // $(p_obj).parent().parent().removeClass("stop","start")
                    //                            .addClass("neutralise");

                }
            }
            /***************************************************************
             * Calcul de la nouvelle taille des terrains 
             ***************************************************************/
            function maj_taille_terrain(taille) {
                /*************************************************************
                 * Terrains Horizontaux
                 *************************************************************/
                //Taille de base en fonction de l'explorateur
                if ($.browser.msie) {
                    large_init = 110;
                    hauteur_init = 110;
                } else {
                    large_init = 90;
                    hauteur_init = 90;
                }
                var draggable_h_hauteur = hauteur_init * (1 + ((25 * taille) / 100)),
                        draggable_h_largeur = large_init * (1 + ((25 * taille) / 100));
                //On memorise les positions avant modifications de tailles
                var tab_pos = new Array();
                $(".draggable").each(function () {
                    tab_pos.push($(this).offset());
                });
                //Mise a jour des regles css
                change_regle(".draggableh", "height", draggable_h_hauteur + "px");
                change_regle(".draggableh", "width", draggable_h_largeur + "px");
                change_regle(".centre_texte_h", "top", (draggable_h_hauteur / 2) + "px");
                change_regle(".centre_texte_h", "left", (draggable_h_largeur / 2) + "px");
                change_regle(".pouce", "bottom", (12 + (taille * 4)) + "px");
                change_regle(".pouce", "height", 20 + (5 * taille) + "px");
                change_regle(".pouce", "width", 20 + (5 * taille) + "px");
                /*************************************************************
                 * Terrains Verticaux
                 *************************************************************/
                //Taille de base en fonction de l'explorateur

                if ($.browser.msie) {
                    large_init = 110;
                    hauteur_init = 140;
                } else {
                    large_init = 90;
                    hauteur_init = 125;
                }
                var draggable_v_hauteur = hauteur_init * (1 + ((25 * taille) / 100)),
                        draggable_v_largeur = large_init * (1 + ((25 * taille) / 100));
                change_regle(".draggablev", "height", draggable_v_hauteur + "px");
                change_regle(".draggablev", "width", draggable_v_largeur + "px");
                change_regle(".centre_texte_v", "top", (draggable_v_hauteur / 2) + "px");
                change_regle(".centre_texte_v", "left", (draggable_v_largeur / 2) + "px");
                change_regle(".temps_v", "fontSize", (10 + (taille * 2)) + "px");

                //Modification de la taille de police 
                var taille_police = (16 + taille);
                change_regle(".fin_h", "fontSize", taille_police + "px");
                change_regle(".centre_texte_v", "fontSize", taille_police + "px");
                change_regle(".centre_texte_h", "fontSize", taille_police + "px");
                taille_police = (13 + taille);
                change_regle(".match", "fontSize", taille_police + "px");
                change_regle(".match_h", "fontSize", taille_police + "px");
                change_regle(".temps_h", "fontSize", (10 + (taille * 2)) + "px");
                //Affichage de la valeur du zoom dans le formulaire de configuration
                $("#taille_zoom").html(taille * 25);
                //on remet les objets a leur place initiale
                var i = 0;
                $(".draggable").each(function () {
                    $(this).offset(tab_pos[i]);
                    i++;
                });
            }

            /****************************************************************
             *Gestion d'un signal sonore
             ****************************************************************/
            function play_sound() {
                //Pour faire beep
                audio = new Audio('media/ok.wav');
                audio.play();
            }
            function stop_sound() {
                if (audio !== undefined)
                    audio.stop(); // audio.pause();  
                audio = null;
            }

            /***************************************************************
             *  En fonction de p_reponse on réinitialise 
             *  ou pas l'heure de début ou de fin de match
             ***************************************************************/
            function change_etat(p_reponse) {
                var Sys_date_heure = new Date();
                var heure = Sys_date_heure.getHours();
                var min = Sys_date_heure.getMinutes();
                var id_obj = $("#objet").val();//N° de l'objet
                var id = $("#match_" + id_obj).val();//N° du match
                if (min < 10) {
                    min = "0" + min;
                }
                $("#reponse").val("O");
                //Si click sur bouton qui appel le formulaire de confirmation
                if ($("#source").val() == "btn") {
                    $("#btn_" + id_obj).html($("#btn_" + id_obj).html().replace('Stop', 'Start'));
                    $("#draggable_" + id_obj).addClass("go")
                            .removeClass("stop")
                            /*et on interdit la saisie */
                            .children("div").children("input:first").attr("disabled", "disabled");
                    var num_terrain = $("#draggable_" + id_obj).children('div:eq(2)').html();
                    if (p_reponse == "non") {
                        //On ne remet pas a jour l'heure de début de match
                        heure = 99;
                        min = 99;
                        //c'est dans le module php que s'effectura la bonne opération en fonction de la valeur de l'heure passée
                    }
                    $.ajax({
                        type: "POST",
                        url: "ajax/majech.1.1.php",
                        data: {num: "num_" + id,
                            etat: 1,
                            terrain: num_terrain,
                            heure: heure + "h" + min},
                        dataType: "json",
                        success: function (data) {
                            for (i = 0; i <= data.length - 1; i++) {
                                Mise_a_jour_echeancier(data[i].num_match, data[i].etat, 0, "00h00");
                            }
                        }
                    });
                    //si heure et minute = 99h99 alors on ne met que l'etat a jour dans echeancier
                    Mise_a_jour_echeancier(id, 1, num_terrain, heure + "h" + min);//mise a jour directe de l'échéancier affiché
                    //on reactive le son pour ce terrain
                    $("#memo_son" + id_obj).val(1);
                } else {
                    if (p_reponse == "oui") {
                        //Si dblclick sur heure de fin qui appel le formulaire
                        var o_terrain = $("#draggable_" + id_obj);
                        $("#fin_" + id_obj).html(heure + "h" + min);
                        o_terrain.addClass("stop")
                                .removeClass("go")
                                .removeClass("orange");

                        var terrain = o_terrain.children('div:eq(2)').html();
                        if (temps_sens == 0) {
                            o_terrain.children("div:eq(4)").html("00:00");
                            o_terrain.children("input:first").val("0"); //Remise à zero du chrono du terrain
                        } else {
                            o_terrain.children("div:eq(4)").html(str_temps_2);
                            o_terrain.children("input:first").val(temps_2); //Remise à temps_2 du chrono du terrain
                        }
                        o_terrain.children("div:eq(0)").children().removeAttr("disabled"); // On autorise le changement de N° de terrain
                        o_terrain.children("div:eq(4)").show();
                        var texte = o_terrain.children("div:eq(3)").children("button");
                        texte.html(texte.html().replace('Stop', 'Start'));
                        //on reactive le son pour ce terrain
                        $("#memo_son" + id_obj).val(1);
                        //mise a jour de la table échéancier 
                        $.ajax({
                            type: "POST",
                            url: "ajax/majech.1.1.php",
                            data: {num: "num_" + id,
                                etat: 2,
                                terrain: terrain,
                                heure: heure + "h" + min},
                            dataType: "json",
                            success: function (data) {
                                for (i = 0; i <= data.length - 1; i++) {
                                    Mise_a_jour_echeancier(data[i].num_match, data[i].etat, 0, "00h00");
                                }
                            }
                        });
                        Mise_a_jour_echeancier(id, 2, terrain, heure + "h" + min);//mise a jour directe de l'échéancier affiché
                    }
                }

            }
            ;

            /********************************************************************
             * Fonction pour sauvegarder la configuration complete des chronos
             * Appelée soit en sortie de la page
             * Soit sur demande dans le formulaire de configuration
             * Modifié le 27/12/2013
             * par FU
             *  Appel une seul fois le module param.5.1.php en passant un tableau
             *  des éléments à sauvegarder 
             *  
             * 04/2014
             * Gestion de la sauvegarde en fonction du type 
             *  "" -> classique
             *  "pref_"-> preference                                                                                                                                     
             *******************************************************************/

            function sauve_config(type_config, texte_particulier) {

                var data = new Array(),
                        num_pref = $("#num_preference").val();

                // Sauvegarde la position des terrains afin de les replacer lorsque la page est chargée à nouveau
                $('.draggableh').each(function () {
                    var $this = $(this)
                    if (($this.offset().top > 0) && ($this.css("display") == "block")) {
                        data.push({'id': $(this).attr("id"), 'top': parseInt($this.offset().top), 'left': parseInt($this.offset().left), 'orient': 'h'});
                    }
                });
                $('.draggablev').each(function () {
                    var $this = $(this);
                    if (($this.offset().top > 0) && ($this.css("display") == "block")) {
                        data.push({'id': $(this).attr("id"), 'top': parseInt($this.offset().top), 'left': parseInt($this.offset().left), 'orient': 'v'});
                    }
                });

                // Sauvegarde de la position et taille des div Contenaire-echeancier et salle 
                var obj = $("#contenaire_echeancier");
                data.push({"id": "div_100", 'top': parseInt(obj.offset().top), 'left': parseInt(obj.height()), 'orient': "h"});
                obj = $("#salle");
                data.push({"id": "div_101", 'top': parseInt(obj.offset().top), 'left': parseInt(obj.height()), 'orient': "h"});
                //Appel unique de l'enregistrement des caractéristiques
                $.ajax({
                    type: "POST",
                    url: "ajax/param.5.2.php",
                    async: false,
                    data: {donnees: data,
                        type_sauvegarde: type_config,
                        num_pref: num_pref
                    }

                });
                /* Parametrages des chronos
                 var c_go // Couleur si terrain lancé
                 var c_stop  // Couleur si terrain libre
                 var c_salle    // Couleur de la salle
                 var tp1  //Temps limite 1
                 var tp2 //Temps limite 2
                 var sens  //Sens du compteur temps
                 var zoom  //Taille du zoom 
                 var son = 0; //Avec ou sans son
                 Ajout 01/2014   par FU
                 var info_bulle=0; // affichage ou pas des info-bulles listes des joueurs
                 
                 
                 */
                var c_go = get_couleur(".go"),
                        c_stop = get_couleur(".stop"),
                        c_salle = rgb2hex($("#salle").css("background-color")),
                        tp1 = $("#Tp_1").val(),
                        tp2 = $("#Tp_2").val(),
                        sens = $("input[name='Tp_sens']:checked").val(),
                        zoom = $("#taille_zoom").html(),
                        son = 0,
                        info_bulle = 0,
                        t_neutre = get_couleur(".neutralise");


                if ($("#son").prop('checked') == true) {
                    son = 1;
                }

                if ($("#info_bulle_lst_joueurs").prop('checked') == true) {
                    info_bulle = 1;
                }

                $.ajax({
                    async: false,
                    type: "POST",
                    url: "ajax/config_chrono.5.2.php",
                    data: {t_l: c_stop,
                        t_o: c_go,
                        c_s: c_salle,
                        tp1: tp1,
                        tp2: tp2,
                        sens: sens,
                        zoom: zoom,
                        son: son,
                        info_bulle: info_bulle,
                        t_neutre: t_neutre,
                        type_sauvegarde: type_config,
                        num_pref: num_pref
                    }
                });
                $("#msg_conf").show()
                        .html("Enregistrement OK!")
                        .fadeOut(2000);
            }
            //Sauvegardes des parametres lors de la cloture de la fenetre
            $(window).on('beforeunload', function () {
                sauve_config("", "");

            });

            //fonction pour tester si click droit
            function isRightClick(event) {
                return event.button == 2;
            }
            /********************************************************************************************************************************************
             *Document ready
             ********************************************************************************************************************************************/
            $(document).ready(function () {
                //Initialisation du slider Zoom taille des terrains            
                $("#zoom").slider({
                    value: <?php echo ($zoom / 25); ?>,
                    min: 0,
                    max: 4,
                    step: 1,
                    slide: function (event, ui) {
                        maj_taille_terrain(ui.value);
                    }
                });
                /* Initialisation des deux div */
                if (existe_echeancier) {
                    init_ech();
                }
                init_multi();

                /***********************************************************
                 *Gestion du click droit sur la salle=>affiche formulaire de configuration
                 ************************************************************/
                $(document).delegate("#salle", "mousedown", function (event) {
                    var self = $(this);
                    event.stopPropagation(); // Stop it bubbling

                    // Make sure it needs to be shown
                    function showIt(event) {
                        return isRightClick(event) && $(event.target).closest('#salle')[0] == self[0];
                    }

                    if (!showIt(event)) {
                        return true;
                    }
                    //Affichage du formulaire
                    $("#msg_conf").html("");
                    $("#dialog-form").dialog("open");

                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function () {
                            return false;
                        });
                /***********************************************************
                 *Gestion du click droit sur terrain=>neutralisation/activation
                 ************************************************************/
                $(document).delegate(".draggable", "mousedown", function (event) {
                    var self = $(this);
                    event.stopPropagation(); // Stop it bubbling

                    // Make sure it needs to be shown
                    function showIt(event) {
                        return isRightClick(event) && $(event.target).closest('.draggable')[0] == self[0];
                    }

                    if (!showIt(event)) {
                        return true;
                    }
                    var tmp = $(this).attr("id").split("_");
                    var id_obj = tmp[1];
                    if ((self.hasClass("stop")) || (self.hasClass("neutralise"))) {  //active le formulaire que si pas de match en cours sur le terrain ou terrain neutralisé
                        //initialisation des données dans le formulaire frm_neutralise
                        $("#terrain_neutralise").val(id_obj);
                        $("#num_terrain_neutre").html(id_obj);
                        if (self.hasClass("neutralise")) {
                            $("#ok_neutralise").attr("checked", "checked");
                        } else {
                            $("#ok_neutralise").removeAttr("checked");
                        }
                        //Ouverture du formulaire
                        $("#frm_neutralise").dialog("open");
                    }


                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function () {
                            return false;
                        });
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
                    //Affichage du formulaire
                    $("#frm_conf_ech").dialog("open");

                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function () {
                            return false;
                        });

                /***********************************************************
                 * Initialisation de la fonction de redefinition des 
                 *   tailles des deux div 
                 ************************************************************/
                $("#salle").resizable();
                if (existe_echeancier) {
                    $("#contenaire_echeancier").resizable();
                } else {
                    $("#contenaire_echeancier").hide();
                    $("#salle").css("height", "600px")
                            .css("width", "800px");
                }
                /***********************************************************
                 * Definition du formulaire de configuration des terrains 
                 *                     et temps
                 ************************************************************/
                $("#dialog-form").dialog({
                    autoOpen: false,
                    height: "auto",
                    width: 'auto',
                    modal: true,
                    resizable: false,
                    buttons: [{
                            text: "Préférences",
                            'click': function () {
                                $("#frm_designation").dialog("open");
                            },
                            icons: {primary: 'ui-icon-gear'}
                        },
                        {
                            text: 'Enregistre',
                            'click': function () {
                                sauve_config("", "");
                            },
                            icons: {primary: 'ui-icon-disk'}
                        },
                        {
                            text: 'Ok',
                            'click': function () {
                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-check'}
                        }],
                    close: function () {
                        //initialisation des zones de temps dans les terrains
                        temps_1 = conv_chaine_temps($("#Tp_1").val());
                        temps_2 = conv_chaine_temps($("#Tp_2").val());
                        str_temps_2 = $("#Tp_2").val();
                        temps_sens = $("input[name='Tp_sens']:checked").val();
                        $(".draggable").each(function () {
                            var $this = $(this);
                            if (temps_sens == 0) {
                                $this.children("input:first").val("0");//memo_temps
                                $this.children("div:eq(4)").html("00:00");//affiche temps
                            } else {
                                $this.children("input:first").val(temps_2);//memo_temps
                                $this.children("div:eq(4)").html(str_temps_2);//affiche temps
                            }
                        });
                    }
                });
                /***********************************************************
                 *Fenetre d'affichage des statistiques
                 ***********************************************************/
                $("#stat-form").dialog({
                    autoOpen: false,
                    height: 'auto',
                    width: 'auto',
                    modal: true,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function () {
                    }
                });
                /***********************************************************
                 *Fenetre d'affichage des messages
                 ***********************************************************/
                $("#msg-form").dialog({
                    autoOpen: false,
                    height: 'auto',
                    width: 'auto',
                    modal: true,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function () {
                        //On reprend l'identifiant de l'objet caché dans le formulaire
                        var obj = $("#obj").val();
                        //On réinitialise
                        $("#" + obj).val("");
                        //On met le focus 
                        $("#" + obj).focus();
                    }
                });
                /***********************************************************
                 * Gestion du déroulage des liste dans autocomplete
                 ***********************************************************/
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
                                    $("#" + nom_liste).autocomplete("close");
                                }
                            });
                            //On ouvre la liste ciblée  
                            // et on transfert le focus a la zone de saisie.
                            //du coups l'effacement de la liste est geré si on quitte la zone de saisie                              
                            $("#" + nom_liste).autocomplete("search", "")
                                    .focus();

                            return false;
                        });
                /***********************************************************
                 * Definition formulaire choix lieu et date
                 ***********************************************************/
                $("#modal_form").dialog({title: 'Choix lieu et date : chrono-échéancier',
                    width: 450,
                    height: 300,
                    //position: 'center',
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
                /***********************************************************
                 * Autocomplete choix d'échéancier
                 ***********************************************************/

                $("#lieu_date").autocomplete({
                    source: "ajax/lst_lieux_dates.1.1.php?cible=ech&action=lst",
                    minLength: 0,
                    select: function (event, ui) {
                        $("#lieu_date").val(ui.item.value);
                        $("#num_titre").val(ui.item.id);
                    }
                });
                /***********************************************************
                 * Fenetre message si match deja joué ou WO !
                 ***********************************************************/
                $("#prompt-form").dialog({resizable: false,
                    height: 'auto',
                    width: 'auto',
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        Oui: function () {
                            change_etat("oui");
                            $(this).dialog("close");

                        },
                        Non: function () {
                            //Sortie sur Non sur match joué
                            if ($("#etat").val() == "2") {
                                change_etat("non");
                            }
                            //Sortie sur Non sur Match WO
                            if ($("#etat").val() == "3") {
                                $("#reponse").val('N');
                            }
                            $("#etat").val("0");
                            $(this).dialog("close");
                        },
                        Abandon: function () {
                            $("#reponse").val('N');
                            $(this).dialog("close");
                        }
                    }
                });

                //Initialisation formulaire de configuration echeancier
                $("#frm_conf_ech").dialog({
                    title: "Configuration Echéancier",
                    resizable: false,
                    height: 'auto',
                    width: 400,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        OK: function () {
                            $(this).dialog("close");
                        }
                    }
                }
                );

                //Verrouille les terrain        
                $("#verrou").button({text: false,
                    icons: {primary: 'ui-icon-unlocked'}
                })
                        .click(function () {
                            var options;
                            if ($(this).button('option', 'icons').primary == "ui-icon-locked") {
                                options = {
                                    icons: {
                                        primary: "ui-icon-unlocked"
                                    }
                                };
                                $(".draggable").draggable("enable");
                            } else {
                                options = {
                                    icons: {
                                        primary: "ui-icon-locked"
                                    }
                                };
                                $(".draggable").draggable("disable");
                            }
                            $(this).button("option", options);
                            return false;
                        })
                        .tooltip();
                /**********************************************************
                 * Boutons du formulaire de configuration echeancier
                 **********************************************************/
                $("#noir_blanc").button({text: "Echéancier en noir et blanc"}).click(function () {
                    change_couleur(this);
                    if ($(this).button('option', 'text') == "Echéancier en noir et blanc") {
                        $(this).button('option', 'text', "Echéancier en couleurs");
                        $("#noir_blanc span").html("Echéancier en couleurs");
                        $("#change_coul").html("En couleur");
                    } else {
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
                    width: 500,
                    height: 'auto',
                    //position: 'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [{
                            text: "Aleatoire",
                            'click': function () {
                                regenere_couleurs();
                            },
                            icons: {primary: 'ui-icon-refresh'}},
                        {
                            text: "Enregistre",
                            'click': function () {
                                $.post("ajax/enregistre_couleurs.php",
                                        $("#frm_couleurs").serialize(),
                                        function () {
                                            $("#msg_coul").html("Enregistrement ok !")

                                        });
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
                        var rgb = hex2rgb(hexa);
                        switch (id) {
                            case "T_occupe" :
                                change_regle(".go", "backgroundColor", "rgb(" + rgb.r + "," + rgb.g + "," + rgb.b + ")");
                                break;
                            case "T_libre"  :
                                change_regle(".stop", "backgroundColor", "rgb(" + rgb.r + "," + rgb.g + "," + rgb.b + ")");
                                break;
                            case "T_neutre"  :
                                change_regle(".neutralise", "backgroundColor", "rgb(" + rgb.r + "," + rgb.g + "," + rgb.b + ")");
                                break;
                            case "C_salle"  :
                                $("#salle").css("background-color", hexa);
                                break;
                            case "Couleur_texte" :
                                text_noir_blanc(rgb); //pour modifier la couleur du texte dans l'échéancier
                                break;
                            default :
                                change_regle("." + id, "backgroundColor", "rgb(" + rgb.r + "," + rgb.g + "," + rgb.b + ")");

                        }

                    }

                });
                /************************************************************
                 * Gestion du clic sur la case a cocher affiche bulle
                 ************************************************************/
                $("#info_bulle_lst_joueurs").on("click", function () {
                    var $this = $(this);
                    if (($this.prop("checked") == true) && (existe_liste == false)) {
                        $this.prop("checked", false);

                        $("#msg_conf").show()
                                .html('<p style="color:red;">Pas de liste de joueurs pour cet &eacute;ch&eacute;ancier ! </p>')
                                .fadeOut(2000);
                    }
                    //En fonction de la coche on cache ou montre les pouces
                    if ($this.prop("checked") == true) {
                        $(".pouce").show();
                    } else {
                        $(".pouce").hide();
                    }
                });
                /************************************************************
                 *Mise en forme des boutons start/stop
                 ************************************************************/
                $(".go_stop").button()
                        .css("height", "auto")
                        .css("font-size", "12px");

                /****************************************************************
                 * mise a jour de l'affichage des chronos
                 ****************************************************************/
                function mise_a_jour_parametre(reponse) {
                    if (reponse !== false) {
                        // initialisation de la regle css pour terrain en cour ou a l'arret
                        var couleur = hex2rgb(reponse.Conf_coul_libre);
                        change_regle(".stop", "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
                        couleur = hex2rgb(reponse.Conf_coul_occup);
                        change_regle(".go", "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");

                        // initialisation de la couleur de la salle 
                        $("#salle").css("background-color", reponse.Conf_coul_salle);
                        //et du champ de saisie du formulaire de configuration
                        $this = $("#C_salle");
                        $this.val(reponse.Conf_coul_salle)
                                .parent().children("a").css("background-color", $this.val());

                        //Initialisation des couleurs Terrain libre et occupé 
                        //dans les champs de saisie du formulaire de configuration     
                        $this = $("#T_libre");
                        $this.val(reponse.Conf_coul_libre)
                                .parent().children("a").css("background-color", $this.val());
                        $this = $("#T_occupe");
                        $this.val(reponse.Conf_coul_occup)
                                .parent().children("a").css("background-color", $this.val());

                        //Initialisation des tempos
                        $("#Tp_1").val(reponse.Conf_tp1);
                        $("#Tp_2").val(reponse.Conf_tp2);
                        $('input[name=Tp_sens]').val([reponse.Conf_sens]);
                        if (reponse.Conf_tp1 !== "") {
                            temps_1 = conv_chaine_temps(reponse.Conf_tp1);
                            temps_2 = conv_chaine_temps(reponse.Conf_tp2);
                            str_temps_2 = reponse.Conf_tp2;
                            temps_sens = parseInt(reponse.Conf_sens);

                        }
                        //initialisation des zones de temps dans les terrains

                        $(".draggable").each(function () {
                            var $this = $(this);
                            if (temps_sens == 0) {
                                $this.children("input:first").val("0");//memo_temps
                                $this.children("div:eq(4)").html("00:00");//affiche temps
                            } else {
                                $this.children("input:first").val(temps_2);//memo_temps
                                $this.children("div:eq(4)").html(reponse.Conf_tp2);//affiche temps
                            }

                        });


                        //reprise des données du zoom
                        $("#taille_zoom").html(reponse.Conf_zoom);
                        var zoom = parseInt(reponse.Conf_zoom) / 25;
                        if (zoom > 0) {
                            maj_taille_terrain(zoom);
                            $("#zoom").slider("value", zoom);
                        }

                        //Reprise valeur son
                        $("#son").prop('checked', (reponse.Conf_son == 1));

                        //Reprise valeur infobulle
                        $("#info_bulle_lst_joueurs").prop('checked', (reponse.info_bulles == 1));



                    }
                }
                ;
                /***********************************************************
                 * Reprise des parametres des chronos
                 ***********************************************************/
                $.ajax({
                    url: 'ajax/majchono.5.1.php',
                    dataType: 'json',
                    type: 'POST',
                    async: false,
                    success: function (reponse) {
                        mise_a_jour_parametre(reponse);
                    }
                }
                );
                /*************************************************************
                 * Formulaire Preferences
                 *************************************************************/
                $("#frm_designation").dialog({
                    title: "Gestion des préférences",
                    width: 'auto',
                    height: 'auto',
                    //position: 'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [
                        {
                            text: "Applique",
                            'click': function () {
                                var num_pref = $("#num_preference").val();
                                if (num_pref > 0) {
                                    //Reprise des positions des terrains dans la salle
                                    $.get('ajax/majpref.php', {num_pref: num_pref}, callComplete_multi, 'json');
                                    //Reprise des couleurs et autres parametres
                                    $.ajax({type: 'GET',
                                        url: 'ajax/maj_coul_pref.php',
                                        data: {num_pref: num_pref},
                                        success: function (reponse) {
                                            mise_a_jour_parametre(reponse);
                                        },
                                        dataType: 'json'}
                                    );
                                    $("#msg_pref").html("<span style='color:green;'>Préférences appliquées!</span>");
                                } else {
                                    $("#msg_pref").html("Choisissez la prérérence à appliquer !");
                                }
                                $("#msg_pref").show()
                                        .fadeOut(2000);
                                return false;

                            },
                            icons: {primary: 'ui-icon-check'}},
                        {
                            text: "Enregistre",
                            'click': function () {
                                var texte = $("#description").val();
                                if (texte != "") {
                                    $.ajax({url: "ajax/creation_preference.php",
                                        type: "POST",
                                        dataType: "json",
                                        data: {pref_nom: $("#pref_nom").val(),
                                            pref_description: $("#pref_description").val()
                                        },
                                        async: false,
                                        success: function (reponse) {
                                            $("#num_preference").val(reponse.num_pref);
                                        }
                                    });
                                    sauve_config("pref_", texte);
                                    $("#msg_pref").html("<span style='color:green;'>Enregistrement OK !</span>");

                                } else {
                                    $("#msg_pref").html("Saisissez un nom pour la prérérence à enregistrer !");

                                }
                                $("#msg_pref").show()
                                        .fadeOut(2000);
                            },
                            icons: {primary: 'ui-icon-disk'}},
                        {
                            text: "Supprime",
                            'click': function () {
                                var num_pref = $("#num_preference").val();

                                if (num_pref > 0) {
                                    $.ajax({
                                        type: 'GET',
                                        url: 'ajax/efface_pref.php',
                                        data: {num_pref: num_pref}
                                    });
                                } else {
                                    $("#msg_pref").html("Choisissez la prérérence à effacer !")
                                            .show()
                                            .fadeOut(2000);

                                }
                            },
                            icons: {primary: 'ui-icon-trash'}},
                        {
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }],
                    close: function () {
                        // En quittant le formulaire on nettoie les variables
                        $("#num_preference").val("0");
                        $("#pref_nom").val("");
                        $("#pref_description").val("");
                        $("#choix_preferences").val("");
                    }
                });
                /*************************************************************
                 * Liste de choix de préférences
                 *************************************************************/
                $("#choix_preferences").autocomplete({
                    source: "ajax/lst_preferences.php",
                    minLength: 0,
                    select: function (event, ui) {
                        $("#choix_preferences").val(ui.item.value);
                        $("#num_preference").val(ui.item.id);
                        $("#pref_description").val(ui.item.texte);
                        $("#pref_nom").val(ui.item.value);
                    }
                });

                /***************************************************************
                 * Formulaire neutralisation terrain
                 ***************************************************************/
                $("#frm_neutralise").dialog({title: "Neutralisation",
                    width: 'auto',
                    height: 'auto',
                    //position: 'center',
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    buttons: [{
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]
                });
                /**************************************************************
                 * Gestion du changement d'état de la case à cocher
                 **************************************************************/
                $("#ok_neutralise").on('change', function () {

                    var id_obj = $("#terrain_neutralise").val(), self = $("#draggable_" + id_obj);
                    //Bascule terrain neutralisé/actif
                    if (self.hasClass("neutralise")) {
                        self.removeClass("neutralise")
                                .addClass("stop");
                        $("#match_" + id_obj).removeAttr("disabled");// Rétabli la possibilité de saisir
                        $("#btn_" + id_obj).removeAttr("disabled")
                                .show();// Réactive le bouton
                        $("#pouce_" + id_obj).show();
                    } else {
                        if (self.hasClass("stop")) {  //passe en neutralise que si pas de match en cours sur le terrain
                            self.addClass("neutralise")
                                    .removeClass("stop", "go");
                            $("#match_" + id_obj).val("")//Raz de la zone N° de match
                                    .attr("disabled", "disabled");// Annule la possibilité de saisir
                            $("#btn_" + id_obj).attr("disabled", "disabled")   // Annule la possibilité d'activer le bouton
                                    .hide();
                            $("#pouce_" + id_obj).hide();
                        }
                    }

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
                if (!debug) {
                    //Gestion des temps chrono
                    timedCount();
                    //Horloge
                    timer();
                }
                //affichage par ddéfaut du recalcul des tranches
                affiche_horaires_reels();

            });
            /*****************************************************************************
             * Ouverture du formulaire de gestion des couleurs des tableaux
             *****************************************************************************/
            function gestion_couleurs() {
                $("#frm_couleurs").dialog("open");
            }
            ;
        </script>

    </head>
    <body>

        <?php
        include ("menu.5.1.php");

        echo '<h2>' . rtrim($titre) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="avance"></span><div style="float:right;"><span id="horloge"></span></div></h2>';
        ?>
        <div id="contenaire_echeancier"  style="height:300px;" >

            <div id="table" style="overflow-x: hidden; overflow-y: scroll; height:100%; width:100%; float:left;">
                <?php echo $tableau; ?>
            </div>
        </div>
        <br />
        <div id="salle" style="height:300px; padding:5px;"><div style="position:left;"><button id="verrou" title="Verrouillage/Déverrouillage des terrains"></button></div>  
            <input type="hidden" id="couleur" /> 
            <?php
            echo $terrains;
            ?>
        </div>
        <!-- Formulaire de configuration des terrains -->            
        <div id="dialog-form" title="Configurations salle et terrains">

            <form>
                <table>
                    <tr>
                        <td>Terrain libre</td><td><input type="hidden" name="T_libre" id="T_libre" class="couleur" value="<?php echo $couleur_terrain_libre; ?>" /> </td>
                    </tr>
                    <tr>
                        <td>Terrain occupé</td><td><input type="hidden" name="T_occupe" id="T_occupe" class="couleur" value="<?php echo $couleur_terrain_occupe; ?>" /></td>
                    </tr>
                    <tr>
                        <td>Terrain neutralisé</td><td><input type="hidden" name="T_neutre" id="T_neutre" class="couleur" value="<?php echo $couleur_terrain_neutre; ?>" /></td>
                    </tr>
                    <tr>
                        <td>Salle</td><td><input type="hidden" name="C_salle" id="C_salle" value="<?php echo $couleur_salle; ?>" class="couleur" /> </td>
                    </tr>
                    <tr>
                        <td>Temps alerte 1</td><td><input type="text" name="Tp_1" id="Tp_1" value="2:00" class="temp" /> </td>
                    </tr>
                    <td>Temps alerte 2</td><td><input type="text" name="Tp_2" id="Tp_2" value="3:00" class="temp" /> </td>
                    </tr>
                    <tr>
                        <td>Son alerte </td><td><input type="checkbox" name="son" id="son" /> </td>
                    </tr>
                    <tr>
                        <td>Sens </td><td><input type="radio" name="Tp_sens" id="Tp_sens" value="0" class="temp" />croissant<br /> <input type="radio" name="Tp_sens" id="Tp_sens" value="1" class="temp" checked /> décroissant</td>
                    </tr>
                    <tr>
                        <td>Zoom&nbsp;<span id="taille_zoom"><?php echo $zoom; ?></span> % </td><td><div  id="zoom"></div></td>
                    </tr>
                    <tr>
                        <td>Remise en position initial des terrains</td><td> <input type="button" onclick="raz();" value="RAZ" /></td>
                    </tr>
                    <tr>
                        <td>Nombre de terrains</td><td><input id="nbr_terrain" name="value"></td>
                    </tr>
                    <tr>
                        <td>Info-bulle liste joueurs</td><td><input type="checkbox" id="info_bulle_lst_joueurs" name="info_bulle_lst_joueurs" checked></td>
                    </tr>

                </table>
            </form>
            <span id="msg_conf" style="color:green"></span>
        </div>
        <!-- Formulaire pour affichage des statistiques -->            
        <div id="stat-form" title="Statistiques">
            <form>
                <table>
                    <tr>
                        <td>Matchs joués</td><td>&nbsp;:&nbsp;<span id="joues"></span> </td>
                    </tr>
                    <tr>
                        <td>Matchs en cours</td><td>&nbsp;:&nbsp;<span id="en_cours"></span></td>
                    </tr>
                    <tr>
                        <td>Matchs WO</td><td>&nbsp;:&nbsp;<span id="wo"></span> </td>
                    </tr>
                    <tr>
                        <td>Matchs restants</td><td>&nbsp;:&nbsp;<span id="restant"></span> </td>
                    </tr>
                    <tr>
                        <td>Temps de match moyen</td><td>&nbsp;:&nbsp;<span id="tp_moyen"></span> </td>
                    </tr>
                    <tr>
                        <td>Match le plus long</td><td>&nbsp;:&nbsp;<span id="tp_max"></span> </td>
                    </tr>
                    <tr>
                        <td>Match le plus court </td><td>&nbsp;:&nbsp;<span id="tp_min"> </span></td>
                    </tr>
                </table>
            </form>
        </div>
        <!-- Formulaire d'affichage message divers -->            
        <div id="msg-form" title="Avertissement">
            <form>
                <input id="obj" type="hidden" />
                <span id="message"></span>
            </form>
        </div>
        <!-- Formulaire pour messages -->
        <div id="prompt-form" title="Avertissement">
            <form>
                <!-- Pour passer l'id du terrain -->
                <input type="hidden" id="objet" />
                <!-- Pour passer la réponse au message -->
                <input type="hidden" id="reponse" />
                <!-- Pour indiquer quel 'bouton' à declanché le message -->
                <input type="hidden" id="source" />
                <!-- Pour indiquer l'état du terrain 2 ou 3 -->
                <input type="hidden" id="etat" />
                <!-- Pour afficher le message -->
                <span id="prompt"></span>
            </form>
        </div>
        <!-- Formulaire pour choix échéancier -->
        <div style="overflow-x:hidden;" id="modal_form">
            <input type="hidden" id="num_titre" />
            <fieldset>          

                Lieu et date : <input type="text" id="lieu_date" /><button class="deroule" id="lst_lieu_date"></button>

            </fieldset>
            <div style="float:right;font-size:8px;font-style:italic;">version 1.0</div>
        </div>
        <!-- Formulaire configuration echeancier -->
        <div id="frm_conf_ech">
            <button id="noir_blanc">Echéancier en noir et blanc</button><br /> <br />
            <button id="chg_couleur">Gestion les couleurs</button><br /> <br />
            <button id="chg_ech">Change d'échéancier</button ><br />
            Recalcul tranches horaires : <input type='checkbox' id='recalcul'  checked='checked'/>
        </div>
        <!-- Formulaire de gestion des couleurs de l'échéancier -->
        <form id="frm_couleurs">
            <?php echo $formulaire_couleurs; ?>
            <span id="msg_coul" style="color:green"></span>
        </form>
        <!-- formulaire saisie designation préférence -->
        <div id="frm_designation">
            <input type="hidden" id="num_preference" value="0">
            <table>
                <tr>
                    <td>Choix préférences</td><td> : <input type="text" id="choix_preferences" /><button id="lst_choix_preferences" class="deroule"></button></td>
                </tr>
                <tr>
                    <td>Nom préférence</td><td> : <input type="text" id="pref_nom" /></td>
                </tr>
                <tr>
                    <td>Description préférence</td><td> : <input type="text" id="pref_description" /></td>
                </tr>

            </table>
            <span id="msg_pref" style="color:red;"></span>
        </div>
        <div id="frm_neutralise">
            <input type='hidden' id='terrain_neutralise'>
            <input id="ok_neutralise" type="checkbox"> Terrain n°<span id="num_terrain_neutre"></span> neutralisé 
        </div>       
    </body>
</html>