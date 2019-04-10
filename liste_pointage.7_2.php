<?php
session_start();

/* * **************************************************************
 * Liste de pointage version table de pointage
 * 0 joueur non-pointé
 * 1 joueur présent
 * 2 joueur WO    
 * Fu
 * 04/2014
 * Ajout de l'état  3 absence autorisée 
 * Gestion du commentaire sur absence autorisée   
 * 
 * FU
 * 07/2017
 * Ajout gestion reglement joueurs/clubs
 * 
 * FU
 * 03/2019
 * Ajout impression pdf de la liste des joueurs
 * Gestion delai convocation
 * ************************************************************** */
include("connect.7.php");

include ("liste_joueurs.7_1.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">

        <title>Liste de pointage : table pointage</title>
        <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />

        <link rel="stylesheet" type="text/css" href="jquery/css/ColumnFilterWidgets.css" /> 

        <link href="jquery/DataTables/datatables.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" title="currentStyle" href="css/liste.css" />
    
        <link href="jquery/jQuery.msgBox-master/styles/msgBoxLight.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

        <script src="jquery/DataTables/datatables.min.js" type="text/javascript"></script>

        <script src="jquery/DataTables/Buttons-1.5.6/js/dataTables.buttons.min.js" type="text/javascript"></script>

        <script src="jquery/DataTables/pdfmake-0.1.36/pdfmake.min.js" type="text/javascript"></script>
        <script src="jquery/DataTables/pdfmake-0.1.36/vfs_fonts.js" type="text/javascript"></script>
        <script src="jquery/DataTables/Buttons-1.5.6/js/buttons.html5.min.js" type="text/javascript"></script>
        <script src="jquery/jQuery.msgBox-master/scripts/jquery.msgBox.js" type="text/javascript"></script>
        <script type="text/javascript" src="jquery/js/ColumnFilterWidgets.js" ></script>

        <script type="text/javascript">
            var message_entete_liste = "Horaires à titre indicatifs";
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
            var oTable;
            /* Define two custom functions (asc and desc) for string sorting */
            jQuery.fn.dataTableExt.oSort['num_match-asc'] = function (p_x, p_y) {
                var t_x = p_x.split(",");
                var t_y = p_y.split(",");
                if (t_x.length > 0) {
                    x = parseInt(t_x[0], 10);
                } else {
                    x = parseInt(p_x, 10);
                }
                if (t_y.length > 0) {
                    y = parseInt(t_y[0], 10);
                } else {
                    y = parseInt(p_y, 10);
                }
                return ((x < y) ? -1 : ((x > y) ? 1 : 0));
            };

            jQuery.fn.dataTableExt.oSort['num_match-desc'] = function (p_x, p_y) {
                var t_x = p_x.split(",");
                var t_y = p_y.split(",");
                if (t_x.length > 0) {
                    x = parseInt(t_x[0], 10);
                } else {
                    x = parseInt(p_x, 10);
                }
                if (t_y.length > 0) {
                    y = parseInt(t_y[0], 10);
                } else {
                    y = parseInt(p_y, 10);
                }
                return ((x < y) ? 1 : ((x > y) ? -1 : 0));
            };

            function callComplete(reponse) {
                /*Mise à jour du tableau si modification de la base
                 reponse contient le Num et l'état des lignes modifiées
                 */

                for (i = 0; i < reponse.length; i++) {
                    var etat = reponse[i].etat;
                    if (etat == "") {
                        etat = "0";
                    }
                    // Bascule de la couleur d'arriére-plan en fonction de l'état        
                    $(oTable.row("#num" + reponse[i].num).node()).toggleClass("etat1", reponse[i].etat == "1")
                            .toggleClass("etat2", reponse[i].etat == "2")
                            .toggleClass("etat3", reponse[i].etat == "3")
                            ;
                    // mise a jour de la colonne etat sans redessiner le tableau  
                    oTable.row(i).data()[0] = etat;

                    //mise a jour de l'infobulle
                    if (reponse[i].etat == "3") {
                        $("#num" + reponse[i].num).attr('title', reponse[i].commentaire)
                                .attr('commentaire', reponse[i].commentaire);
                    }
                }
                // re-dessine le tableau sans toucher l'affichage de la pagination en cours    
                oTable.draw('page');
                // Relance la mise a jour 
                var t = setTimeout("connect();", 15 * 1000);   //Appel Temporisé
            }
            ;

            function connect() {
                // boucle infinie : demande de donnée toutes les 15s

                $.post('ajax/retourmaj.5.2.php', {}, callComplete, 'json');

            }
            ;
            // Initialisation du document
            $(document).ready(function () {
                $("tbody>tr").tooltip(
                        );
                /* Fonction sur double clic de toutes les lignes du tableau
                 * Chaque double clic change l'état du joueur dans la liste
                 * Si joueur WO alors les matchs de ce joueurs passent WO
                 * Si joueur repasse absent alors les matchs du joueur repassent à l'etat initial                  
                 */
                $("tbody>tr").dblclick(function () {
                    var $thisParagraph = $(this);
                    var count = 0;
                    var id = $thisParagraph.attr("id");
                    var aPos = oTable.row(this).index(); //Indice de la ligne
                    // Valeur de l'état
                    count = oTable.row(aPos).data()[0];
                    count++;
                    if (count >= 4) {
                        count = 0;
                    }
                    ;

                    if (count == 3) {
                        $("#commentaire").val($thisParagraph.attr("commentaire"));
                        $("#id_lig").val(id);
                        $("#frm_commentaire").dialog("open");
                    }

                    //Mise à jour de la base 
                    //si pas de mise a jour possible dans la seconde=>message
                    $.ajax({
                        type: "POST",
                        url: "ajax/majliste.5.2.php",
                        data: {num: id,
                            etat: count,
                            commentaire: $thisParagraph.attr("title")},
                        timeout: 1000,
                        error: function () {
                            alert("Une anomalie s'est produite : Pas de  mise à jour posssible !");
                        },
                        success: function () {
                            $thisParagraph.toggleClass("etat1", count == 1)
                                    .toggleClass("etat2", count == 2)
                                    .toggleClass("etat3", count == 3);
                            // Mise a jour de l'état
                            oTable.row(aPos).data()[0] = count;
                            // re-dessine le tableau sans toucher l'affichage de la pagination en cours    

                            oTable.draw('page');
                        }
                    });


                });
                //Version Datatables 1.10
                //FUR
                //04/2018
                oTable = $('#liste').DataTable({
                    //"sPaginationType": "full_numbers",
                    paging: false,
                    language: {"url": "jquery/DataTables/language/fr_FR.txt"},
                    columnDefs: [
                        {"bSortable": false, "bVisible": false, "aTargets": [0]}, //cache la colonne etat
                        {"sType": "num_match", "aTargets": [3]}  //tri sur N° de match par fonction perso
                    ],
                    dom: 'BW<"clear">lfrtip',
                    "oColumnFilterWidgets": {
                        "aiExclude": [0, 1, 2, 3, 7]
                    },
                    order: [[1, "asc"]], //Tri par défaut sur le nom
                    initComplete: function () {
                        connect();//Lancement de la boucle de  raffraichissment des données dés que le tableau est en place 
                        $(".buttons-pdf").hide();
                    },
                    scrollY: "800px",
                    scrollX: false,
                    scrollCollapse: true,
                    buttons: [
                        {
                            extend: 'pdfHtml5',
                            messageTop: function () {
                               
                                return [{text:message_entete_liste,fontSize:15,color:'red'}] ;
                            },
                            title: "Liste horaires convocations joueurs",
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            },
                            text: 'Impression liste'
                        }
                    ]
                });
                //new FixedHeader(oTable);
                //Modification de la largeur occupée par le tableau
                $("#liste").attr("width", "90%");
                //Lancement de la boucle de  raffraichissment des données
                //connect(); 
                //Prend en charge le changement de valeur du filtre
                $('input[type=radio][name=filtre]').click(function () {
                    var filtre = $('input[type=radio][name=filtre]:checked').attr('value'); //Retourne la valeur du bouton radio selectionné
                    filtre_tableau(filtre);
                });
                //Ajout des options sur liste de pointage
                $("#liste_pointage").html("<a href='#'>Table de pointage</a>\n\
                                            <ul>\n\
                                                <li>Impressions\n\
                                                    <ul>\n\
                                                        <li><a id='imprime_liste' title='En fonction du filtre'>Liste des joueurs selon filtre</a></li>\n\
                                                        <li><a id='imprime_reglement'>Réglement des joueurs</a></li>\n\
                                                    </ul>\n\
                                                </li>\n\
                                                <li><a id='Change_delai'>Change convocations</a></li>\n\
                                            </ul>");
                /***************************************************************
                 * Menu général
                 * *************************************************************/
                $("#menuprinc").menu({position: {using: positionnerSousMenu}});

                //==============================================================
                //Impression liste joueurs
                //==============================================================
                $('#imprime_liste').click(function (e) {
                    $.msgBox({
                        title: "Complement texte entete de liste",
                        type: "prompt",
                        inputs: [{header: "Texte en entete", type: "text", width:"300px", size:"200", name: "texte_entete", value: message_entete_liste}],
                        buttons: [{value: "OK"}, {value: "Annule"}],
                        success: function (result, values) {
                            if (result == 'OK') {
                                $(values).each(function (index, input) {
                                    message_entete_liste = input.value;
                                });
                                $(".msgBoxContainer").after( 'Préparation en cours <img src="images/wait.gif" style="width:7%">');
                                $(".buttons-pdf").click();
                            }
                        }
                    });

                });
                //==============================================================
                //Appel Impression reglement oueurs
                //==============================================================
                $('#imprime_reglement').click(function (e) {
                    imprime_reglement();
                });
                /******************************************************************
                 * Formulaire saisi du commentaire sur joueur état absence autorisée
                 ******************************************************************/
                $("#frm_commentaire").dialog({
                    title: 'Informations complémentaires',
                    width: 'auto',
                    height: 'auto',
                    modal: true,
                    autoOpen: false,
                    buttons: [
                        {
                            text: "Enregistre",
                            'click': function () {
                                var commentaire = $("#commentaire").val();
                                var id = $("#id_lig").val();
                                $.ajax({
                                    type: "POST",
                                    url: "ajax/majliste.5.2.php",
                                    data: {num: id,
                                        etat: 3,
                                        commentaire: commentaire
                                    }
                                });
                                $("#" + id).attr("title", commentaire)
                                        .attr("commentaire", commentaire);
                                $(this).dialog("close");


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
                //fonction pour tester si click droit
                function isRightClick(event) {
                    return event.button == 2;
                }
                $(document).delegate("tbody>tr", "mousedown", function (event) {
                    var self = $(this);
                    event.stopPropagation(); // Stop it bubbling

                    // Make sure it needs to be shown
                    function showIt(event) {
                        return isRightClick(event) && $(event.target).closest('tbody>tr')[0] == self[0];
                    }

                    if (!showIt(event)) {
                        return true;
                    }
                    //Affichage du formulaire
                    $("#commentaire").val(self.attr('commentaire'));
                    $("#id_lig").val(self.attr('id'));
                    $("#frm_commentaire").dialog("open");

                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function () {
                            return false;
                        });

                /**************************************************
                 * Click sur icone reglement
                 * FU
                 * 07/2017
                 */
                $("#liste").on("click", ".reglement", function () {
                    var id = $(this).data("id_reglement");
                    $.ajax({url: "ajax/reglements.php",
                        type: "GET",
                        dataType: "html",
                        data: {'id': id},
                        success: function (reponse) {
                            $("#info_reglement").html(reponse);
                            $("#frm_reglement").dialog("open");
                        }
                    });
                });
                //Conversion d'une chaine base64 en tableau
                function b64toBlob(b64Data, contentType, sliceSize) {
                    contentType = contentType || '';
                    sliceSize = sliceSize || 512;

                    var byteCharacters = atob(b64Data);
                    var byteArrays = [];

                    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                        var slice = byteCharacters.slice(offset, offset + sliceSize);

                        var byteNumbers = new Array(slice.length);
                        for (var i = 0; i < slice.length; i++) {
                            byteNumbers[i] = slice.charCodeAt(i);
                        }

                        var byteArray = new Uint8Array(byteNumbers);

                        byteArrays.push(byteArray);
                    }

                    var blob = new Blob(byteArrays, {type: contentType});
                    return blob;
                }
                //==============================================================
                //Impression de la liste des réglement
                //==============================================================
                function imprime_reglement() {
                    $.post("ajax/imprime_reglement.php",
                            function (data) {
                                var PdfData = b64toBlob(data, 'application/pdf;base64;');
                                //IE11 & Edge
                                if (navigator.msSaveBlob) {
                                    navigator.msSaveBlob(PdfData, "Reglements.pdf");
                                } else {
                                    //In FF link must be added to DOM to be clicked
                                    var link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(PdfData);
                                    link.setAttribute('download', "reglements.pdf");
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                }

                            }
                    );
                }
                ;
                /*************************************************
                 * formulaire info reglements
                 */
                $("#frm_reglement").dialog({
                    title: 'Informations reglement',
                    width: 'auto',
                    height: 'auto',
                    modal: true,
                    autoOpen: false,
                    buttons: [
                        {
                            text: "Imprime",
                            'click': function () {
                                imprime_reglement();
                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-print'}
                        },
                        {
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]

                });
                /**************************************************
                 * Action sur clic sur icones dans formulaire
                 */
                $("#frm_reglement").on('click', '.saisie_reglement', function () {
                    var flag = 0,
                            id = $(this).data('id_reglement');

                    if ($(this).attr('src') === "images/regle.png") {
                        $(this).attr('src', "images/en_attente.png");
                        $(".id_" + id).attr('src', "images/en_attente.png");
                        flag = 0;
                    } else {
                        $(this).attr('src', "images/regle.png");
                        $(".id_" + id).attr('src', "images/regle.png");
                        flag = 1;
                    }
                    $.msgBox({title: "Mode de r&eacute;glement",
                        type: 'prompt',
                        inputs: [
                            {header: "Chéque", type: "radio", name: "mode", value: "1"},
                            {header: "Espéces", type: "radio", name: "mode", value: "2"}
                        ],
                        buttons: [{value: "OK"}, {value: "Annule"}],
                        success: function () {}
                    });
                    $.ajax({url: "ajax/enregistre_reglement.php",
                        data: {'reg_joueurs_id': id,
                            'reg_joueurs_regle': flag},
                        type: "GET",
                        success: function () {

                        }
                    });
                });
//formulaire saisi delai convocation
                $("#frm_change_delai").dialog({
                    title: 'Changement délai de convocation',
                    width: 'auto',
                    height: 'auto',
                    modal: true,
                    autoOpen: false,
                    buttons: [
                        {
                            text: "Valide",
                            'click': function () {
                                $.ajax({
                                    url: 'ajax/maj_delai_convoc.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: $("#frm_change_delai").serialize(),
                                    success: function (data) {
                                        if (data.message == 'ok') {
                                            window.location = "liste_pointage.7_2.php";
                                        } else {
                                            $.msgBox({
                                                title: "Avertissement",
                                                content: data.message,
                                                type: "warning"
                                            });
                                        }
                                    }
                                });
                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-check'}
                        },
                        {
                            text: "Quitter",
                            'click': function () {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]

                });
                //
                // Affiche formulaire changement de délai
                //
                $("#Change_delai").click(function () {
                    $("#frm_change_delai").dialog("open");
                });
            }); //Fin document ready


            function filtre_tableau(filtre) {
                var test = (filtre != "99");
                if (!test) {
                    filtre = "";
                }
                oTable.column(0).search(filtre).draw('page');
            }


        </script>

    </head>
    <body>
         <img src="images/wait.gif" style="width:7%;display:none"><!-- sert a pre-charger l'image -->
        <?php include ("menu.5.1.php"); ?>

        Filtre :  <input type="radio" id="filtre" name="filtre" value="99" checked/>Tous&nbsp;
        <input type="radio" id="filtre" name="filtre" value="1" /><span class="etat1">Présents&nbsp;</span>
        <input type="radio" id="filtre" name="filtre" value="0" />En attente&nbsp;
        <input type="radio" id="filtre" name="filtre" value="2" /><span class="etat2">Absents (WO)&nbsp;</span>
        <input type="radio" id="filtre" name="filtre" value="3" ><span class="etat3">Absents autorisés&nbsp;</span>
        <br />

        <?php
        echo $entete . $corps;
        ?> 
        <!-- formulaire commentaire sur joueur état absent sur ok JA --> 
        <?php
        echo $formulaire;
        echo $formulaire_reglement;
        ?>
        <form id='frm_change_delai'>
            <?php echo $tab_frm_decalage; ?>
        </form>
    </body>
</html>
