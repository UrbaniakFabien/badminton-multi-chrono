<?php
//FUR
//02/2018
//Ajout liste joueurs pour accés vers échéancier personnel joueurs

$tab_config = parse_ini_file("config.ini");
$version = $tab_config["version"];
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="icon shortcut" href="images/favicon.gif" />
        <title>Connexion multi-chrono</title>
        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
        <link rel="stylesheet" type="text/css" href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" />


        <script type="text/javascript">
            var tentative = 0; //Nombre de tentatives avant rejet
            $(document).ready(function () {
                //Formulaire principal
                $("#frm").dialog({
                    title: "Connexion multi-chrono <?php echo $version; ?>",
                    autoOpen: true,
                    width: 'auto',
                    height: 'auto',
                    //position:'center',
                    resizable: false,
                    modal: true,
                    buttons: {
                        Connecte: function () {
                            var $licence = $("#licence").val();
                            if ($licence == 0) {
                            $.post("ajax/test_loggin.5.php",
                                    $("#frm_connect").serialize(),
                                    function (reponse) {
                                        if (reponse != "") {
                                            $("#msg").html("Nom utilisateur et Mot de passe valides !")
                                                    .css("color", "green")
                                                    .fadeIn(1500).delay(3500).fadeOut(1500);
                                            window.location = reponse;
                                        } else {
                                            $("#msg").html("Mot de passe ou Nom utilisateur invalide !")
                                                    .css("color", "red")
                                                    .fadeIn(1500).delay(3500).fadeOut(1500);

                                            tentative++;
                                            if (tentative >= 3) {
                                                $("#msg").html("Nombre max de tentatives !<br/>vous allez être redirigé vers une autre page !")
                                                        .css("color", "red")
                                                        .fadeIn(1500).delay(3500).fadeOut(1500);
                                                window.location = "mon_echeancier.7.php";
                                            }
                                        }
                                    }
                            );
                            }
                            else {
                                 window.location = "mon_echeancier.7.php?licence="+$licence;
                            }

                        },
                        Annule: function () {
                            window.location = "mon_echeancier.7.php";
                        },
                        Deconnecte: function () {
                            window.location = "deconnexion.7.php";
                        }
                    },
                    close: function () {
                        $.post("deconnexion.php");
                        window.location = "mon_echeancier.7.php";
                    }
                });
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
                //Liste des joueurs
                $("#login").autocomplete({
                    source: "ajax/lst_joueurs.php",
                    minLength: 0,
                    select: function (event, ui) {
                            if (ui.item.id == 0) {
                                $("#saisie_psw").show();
                            }
                            else {
                                $("#saisie_psw").hide(); 
                            }
                            $("#licence").val(ui.item.id);
                    }
                });

            });//Fin document ready

        </script>
        <style type="text/css">
            .ui-dialog {
                opacity:0.74;
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
        <div id="frm">
            <form id="frm_connect"> 
                <input type='hidden' name='licence' id='licence'>
                <h2>Multi-Chrono <?php echo $version; ?></h2> 
                <div id="msg"></div>
                <fieldset>
                    <table>
                        <tr>
                            <td>Utilisateur ou Joueur </td><td> : <input type="text" id='login' name="login" value=""/><button class='deroule' id='lst_login'></button></td>
                        </tr>
                        <tr  id="saisie_psw" style='display:none'>
                            <td>Mot de passe</td><td> : <input type="password" name="mdp" value=""/></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </div>
    </body>
</html>
