<?php
session_start();
/* * *********************************************************************
 * Affichage automatique des informations relatives au plus grand N° de match en cours
 * FU
 * 2013
 * ******************************************************************* */

$num_titre = isset($_GET["num_titre"]) ? $_GET["num_titre"] : 0;
if ($num_titre == 0) {
    $num_titre = isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"] : 0;
}
$appelant = "dernier_match.7";
if ($num_titre == 0) {

    $titre = "Dernier match";
    include ("demande_num.7.php");
    exit();
}

$_SESSION["num_titre"] = $num_titre; //Mémorise pour la session le N° de lieu date en cours
include("connect.7.php");
include ("couleurs_ech.5.2.php");
//Titre donné à la page
$sql = "SELECT lieu_date from titre where num_titre=" . $num_titre;
$result = exec_commande($sql);
$data = mysqli_fetch_assoc($result);
$titre = "Prochain match " . $data["lieu_date"];
//
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title><?php echo $titre ?></title>

        <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link id="css_coul" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" rel="stylesheet" />

        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

        <script type="text/javascript" src="js/menu.js"></script>

        <script type="text/javascript">
            var tempo = 10; //Temporisation par défaut pour le rafraichissement
            function change_ech() {
                $("#modal_form").dialog("open");
            }
            function callComplete(reponse) {
                /******************************************************************************
                 *Mise à jour du tableau si modification de la base
                 *reponse contient le Num et l'état des lignes modifiées
                 *******************************************************************************/
                var num;
                if (reponse.encours.Num_match.length > 0) {
                    if (isNaN(reponse.encours.Num_match)) {
                        num = reponse.encours.Num_match;
                    } else
                    {
                        num = parseInt(reponse.encours.Num_match) + 1;
                    }
                    $("#num_match").html(num);
                    $("#en_attente").html(reponse.en_attente);
//                    $("#tableau").html(reponse[0].tableau);
//                    $("#heure_debut").html(reponse[0].heure_debut);
//                    $("#num_terrain").html(reponse[0].terrain);
//                    $("#spe").html(reponse[0].spe);
                }
                //Appel Tempo toutes les 10 secondes
                var t = setTimeout("connect();", tempo * 1000);
            };
            



            function connect() {
                // boucle infinie : demande de donnée toutes les 15s

                $.post('ajax/info_match.1.php', {}, callComplete, 'json');
                $.ajax({
                    type: 'GET',
                    url: "ajax/horaires.php",
                    dataType: 'json',
                    timeout: 10000,
                    async: false,
                    data: {type_reponse: 1},
                    success: function (reponse) {
                        if (reponse.ecart == "00h00") {
                            reponse.ecart = "";
                        }
                        $("#ecart").html("<span style='color:" + reponse.couleur + ";'>" + reponse.type + " " + reponse.ecart);
                    }
                });

            }
            ;
            // Initialisation du document
            $(document).ready(function () {

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
                //Complement de menu
                $("#dernier_match").html("<a href='#'>Prochain match</a>\n\
        <ul>\n\
            <li><a>Taille</a>\n\
                <ul>\n\
                    <li><div id='choix_taille' style='height:10px;width:100%;'> <div id='custom-handle' class='ui-slider-handle'></div></div></li>\n\
                </ul>\n\
            </li>\n\
            <li><a>Rafraichissement</a>\n\
                <ul>\n\
                    <li><a>1 s<span class='tempo'></span></a></li>\n\
                    <li><a>5 s<span class='tempo'></span></a></li>\n\
                    <li><a>10 s<span class='tempo sel' ></span></a></li>\n\
                    <li><a>15 s<span></span></a></li>\n\
                </ul>\n\
            </li>\n\
            <li>Affichage\n\
                <ul>\n\
                    <li><a>Matchs en attente</a>\n\
                        <ul>\n\
                            <li><a>Oui<span class='attente sel'></span></a></li>\n\
                            <li><a>Non<span class='attente'></span></a></li>\n\
                        </ul>\n\
                    </li>\n\
                    <li><a>Etat</a>\n\
                        <ul>\n\
                            <li><a>Avec<span class='etat sel'></span></a></li>\n\
                            <li><a>Sans<span class='etat'></span></a></li>\n\
                        </ul>\n\
                    </li>\n\
                </ul>\n\
            </li>\n\
            <li>\n\
                <a onclick='change_ech();'>Change Echéancier</a>\n\
            </li>\n\
        </ul>");


                /* definition du menu */
                $("#menuprinc").menu({
                    autoExpand: true,
                    menuIcon: true,
                    buttons: true,
                    position: {using: positionnerSousMenu},
                    select: function (event, ui) {
                        var text_select = ui.item.text();
                        switch (text_select) {
                            case "1 s":
                            case "5 s":
                            case "10 s":
                            case "15 s" :
                                //On efface la selection précédente
                                $(".tempo").removeClass('ui-menu-icon ui-icon-check ui-icon sel');
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
                                break;
                                //avec ou sans lal liste des en attente
                            case "Oui":
                            case "Non":
                                //On efface la selection précédente
                                $(".attente").removeClass('ui-menu-icon ui-icon-check ui-icon sel');
                                //On montre la selection courante
                                ui.item.children().children().addClass('ui-menu-icon ui-icon-check ui-icon sel');
                                switch (text_select) {
                                    case "Oui" :
                                        $("#en_attente").show();
                                        break;
                                    case "Non":
                                        $("#en_attente").hide();
                                        break;
                                }

                                break;
                                //avec ou sans l'affichege du retard ou avance
                            case "Avec":
                            case "Sans":
                                //On efface la selection précédente
                                $(".etat").removeClass('ui-menu-icon ui-icon-check ui-icon sel');
                                //On montre la selection courante
                                ui.item.children().children().addClass('ui-menu-icon ui-icon-check ui-icon sel');
                                switch (text_select) {
                                    case "Avec" :
                                        $("#ecart").show();
                                        break;
                                    case "Sans":
                                        $("#ecart").hide();
                                        break;
                                }


                        }
                    }
                });
                //ajout des class pour les selections par défaut dans le menu
                $(".sel").addClass('ui-menu-icon ui-icon-check ui-icon');
                var handle = $("#custom-handle");
                $("#choix_taille").slider({
                    min: 100,
                    max: 600,
                    value: 300,
                    create: function () {
                        handle.text($(this).slider("value"));
                    },
                    slide: function (event, ui) {
                        handle.text(ui.value);
                        taille = ui.value;
                        $(".grand_car").css("font-size", taille + "px");
                        $(".moyen_car").css("font-size", (taille / 3) + "px");
                        $(".petit_car").css("font-size", (taille / 10) + "px");
                    }
                });
                /*****************************************************************************
                 * Ouverture du formulaire changement d'echeancier
                 *****************************************************************************/
                function change_ech() {
                    $("#modal_form").dialog("open");
                }

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

                //Lancement de la boucle de  rafraichissment des données
                connect();
            });//fin document.ready


            function timer() {
                /*Affichage de l'heure en continu */
                var sys_time = new Date();
                var heure = sys_time.getHours();
                var min = sys_time.getMinutes();
                var sec = sys_time.getSeconds();
                if (heure < 10) {
                    heure = "0" + heure
                }
                ;
                if (min < 10) {
                    min = "0" + min
                }
                ;
                if (sec < 10) {
                    sec = "0" + sec
                }
                ;
                $("#horloge").html(heure + " : " + min + " : " + sec);
                var t = setTimeout("timer();", 1000);   //Appel Tempo toutes les 10s
            }


            timer();



        </script>
        <style type="text/css">

            .grand_car {
                font-size : 300px;
                color:white; 
                border-radius: 30px;
                border-style: solid;
                border-width: 1px;

                padding-left: 20px;
            }
            .moyen_car {
                font-size : 90px;
                color:white;
                text-align:center;
            }
            .petit_car {
                font-size : 20px;
                color:white;
                text-align:center;
            }
            @font-face {  
                font-family: "digital";  
                src: url( css/fonts/digitaldreamfat.eot ); /* IE */  
                src: local("DIGITALDREAMFAT"), url( css/fonts/DIGITALDREAMFAT.ttf ) format("truetype"); /* non-IE */  
            }  

            /* THEN use like you would any other font */  
            #horloge { 
                font-family:"digital", verdana, helvetica, sans-serif;
                color:white;
            }
            #contenant {
                display:flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height :100%;
                width:100%
            }
            .element{

            }
            #horloge{
                align-self: flex-end;
            }

            #en_attente
            {
                font-weight: bold;
                font-style: italic;
                color: blue;
                font-size: 3em;
                animation: marqueelike 8s infinite linear;
                text-align: right;
                display:inline-block;
            }
            @keyframes marqueelike{
                0%  {
                    min-width:540px;
                }
                50% {
                    min-width:100%;
                }
                100%  {
                    min-width:540px;
                }
            }

            #custom-handle {
                width: 10px;
                height: 10px;
                /*    top: 50%;*/
                margin-top: -.8em;
                text-align: center;
                line-height: 1.6em;
            }
        </style>

    </head>
    <body style='background-color:black'>

        <?php include ("menu.5.1.php"); ?>  
<!--        <div style="float:right;"><h2><span id="horloge"></span></h2></div>-->
<!--            <table align="center">    
                <tr><td style="text-align:center">
                        <p class="moyen_car">PROCHAIN MATCH </p>
                    </td></tr>
                <tr>
                    <td style="text-align:center" class='grand_car' id="num_match">0                   
                    </td>
                </tr>
                 <tr>
                    <td style="text-align:center" class='moyen_car' id="ecart">0                   
                    </td>
                </tr>
            </table>-->
        <div id="contenant">
            <div id="horloge" class='petit_car'>

            </div>
            <div class="element moyen_car">
                PROCHAIN MATCH
            </div>
            <div class="element grand_car" id="num_match">
                0
            </div>
            <div class="element moyen_car" id="ecart">
                0
            </div>
            <div class="element moyen_car" id="en_attente" style="overflow-wrap: break-word;">

            </div>
        </div> 
        <div style="overflow-x:hidden;" id="modal_form">
            <input type="hidden" id="num_titre" />
            <fieldset>          

                Lieu et date : <input type="text" id="lieu_date" /><button class="deroule" id="lst_lieu_date"></button>

            </fieldset>
            <div style="float:right;font-size:8px;font-style:italic;">version 1.0</div>
        </div>
    </body>
</html>
