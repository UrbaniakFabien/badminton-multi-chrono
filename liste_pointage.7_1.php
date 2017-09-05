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
 * ************************************************************** */
include("connect.7.php");

include ("liste_joueurs.7_1.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">

        <title>Liste de pointage : table pointage</title>
        <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />

        <link rel="stylesheet" type="text/css" href="jquery/css/ColumnFilterWidgets.css" />
        <link rel="stylesheet" type="text/css" title="currentStyle" href="css/liste.css" />
        <link rel="stylesheet" type="text/css" title="currentStyle" href="jquery/DataTables-1.9.0/media/css/demo_page.css" />
        <link rel="stylesheet" type="text/css" title="currentStyle" href="jquery/DataTables-1.9.0/media/css/demo_table.css" />

        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <script type="text/javascript" src="jquery/DataTables-1.9.0/media/js/jquery.DataTables.min.js"></script>
        <script type="text/javascript" src="jquery/DataTables-1.9.0/extras/FixedHeader/js/FixedHeader.js"></script>

        <script type="text/javascript" src="jquery/js/ColumnFilterWidgets.js" ></script>

        <script type="text/javascript">
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
            $.fn.dataTableExt.oApi.fnStandingRedraw = function (oSettings) {
                //redraw to account for filtering and sorting
                // concept here is that (for client side) there is a row got inserted at the end (for an add)
                // or when a record was modified it could be in the middle of the table
                // that is probably not supposed to be there - due to filtering / sorting
                // so we need to re process filtering and sorting
                // BUT - if it is server side - then this should be handled by the server - so skip this step
                if (oSettings.oFeatures.bServerSide === false) {
                    var before = oSettings._iDisplayStart;
                    oSettings.oApi._fnReDraw(oSettings);
                    //iDisplayStart has been reset to zero - so lets change it back
                    oSettings._iDisplayStart = before;
                    oSettings.oApi._fnCalculateEnd(oSettings);
                }

                //draw the 'current' page
                oSettings.oApi._fnDraw(oSettings);
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
                    $(oTable.fnGetNodes()).filter("#num" + reponse[i].num).toggleClass("etat1", reponse[i].etat == "1")
                            .toggleClass("etat2", reponse[i].etat == "2")
                            .toggleClass("etat3", reponse[i].etat == "3")
                            ;
                    // mise a jour de la colonne etat sans redessiner le tableau           
                    oTable.fnUpdate(etat, i, 0, false, false);
                    //mise a jour de l'infobulle
                    if (reponse[i].etat == "3") {
                        $("#num" + reponse[i].num).attr('title', reponse[i].commentaire)
                                .attr('commentaire', reponse[i].commentaire);
                    }
                }
                // re-dessine le tableau sans toucher l'affichage de la pagination en cours    
                oTable.fnStandingRedraw();
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
                    var aPos = oTable.fnGetPosition(this); //Indice de la ligne
                    // Valeur de l'état
                    count = oTable.fnGetData(aPos, 0);
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
                            oTable.fnUpdate(count, aPos, 0, false, false);
                            // re-dessine le tableau sans toucher l'affichage de la pagination en cours    
                            oTable.fnStandingRedraw();
                        }
                    });


                });
                // Initialisation de l'affichage du tableau
                oTable = $('#liste').dataTable({
                    //"sPaginationType": "full_numbers",
                    "bPaginate": false,
                    "oLanguage": {"sUrl": "jquery/DataTables-1.9.0/media/language/fr_FR.txt"},
                    "aoColumnDefs": [
                        {"bSortable": false, "bVisible": false, "aTargets": [0]}, //cache la colonne etat
                        {"sType": "num_match", "aTargets": [3]}  //tri sur N° de match par fonction perso
                    ],
                    "sDom": 'W<"clear">lfrtip',
                    "oColumnFilterWidgets": {
                        "aiExclude": [0, 1, 2, 3]
                    },
                    "aaSorting": [[1, "asc"]], //Tri par défaut sur le nom
                    "fnInitComplete": function () {
                        connect();//Lancement de la boucle de  raffraichissment des données dés que le tableau est en place 
                    }
                });
                new FixedHeader(oTable);
                //Modification de la largeur occupée par le tableau
                $("#liste").attr("width", "90%");
                //Lancement de la boucle de  raffraichissment des données
                //connect(); 
                //Prend en charge le changement de valeur du filtre
                $('input[type=radio][name=filtre]').click(function () {
                    var filtre = $('input[type=radio][name=filtre]:checked').attr('value'); //Retourne la valeur du bouton radio selectionné
                    filtre_tableau(filtre);
                });

                /***************************************************************
                 * Menu général
                 * *************************************************************/
                $("#menuprinc").menu({position: {using: positionnerSousMenu}});

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
                $("#frm_reglement").on('click','.saisie_reglement',function(){
                    var flag = 0,
                    id = $(this).data('id_reglement');
                   
                    if ($(this).attr('src') === "images/regle.png") {
                          $(this).attr('src',"images/en_attente.png");
                          $(".id_"+id).attr('src',"images/en_attente.png");
                          flag = 0;
                    } else {
                         $(this).attr('src',"images/regle.png");
                          $(".id_"+id).attr('src',"images/regle.png");
                         flag = 1;
                    }
                    $.ajax({url:"ajax/enregistre_reglement.php",
                            data:{'reg_joueurs_id':id,
                                  'reg_joueurs_regle':flag},
                            type : "GET",
                            success:function(){
                                
                            }
                    });
                });
            }); //Fin document ready


            function filtre_tableau(filtre) {
                var test = (filtre != "99");
                if (!test) {
                    filtre = "";
                }
                oTable.fnFilter(filtre, 0);
            }


        </script>

    </head>
    <body>
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
    </body>
</html>