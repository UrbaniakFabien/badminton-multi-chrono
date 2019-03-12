<?php
/******************************************************************************* 
 *Novembre 2013
 * FU
 * Ajout test sur droit d'accés =>construction différente du du menu 
 *  
 * 
 * 
 * 10/01/2014
 *Ajout 'A propos de'     
 *
 *12/2017
 *Ajout otion import reglement
 *Changemnt appel liste joueur pointage
 *******************************************************************************/
include("test_login.5.1.php");
$tab_config=parse_ini_file("config.ini");
$version=$tab_config["version"];
$niveau=$_SESSION["_niveau"];
$menu='
  <ul id="menuprinc">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="#">Pointage</a>
      <ul>
        <li id="liste_pointage"><a href="liste_pointage.7_2.php">Table de pointage</a></li>
        <li><a href="liste_table_marque.7_2.php">Table de marque</a></li>';
if ($niveau=="admin") {
$menu.= ' <li>-</li>
        <li><a href="integre_liste.7.php">Import liste</a></li>
        <li><a href="integre_reglement.7.php">Import réglements</a></li>
        <li><a href="raz.7.php?cible=lst">RAZ listes</a></li>';
}
$menu.=  '</ul>
    </li>
    <li><a href="#">Echéancier</a>
      <ul>
        <li id="menu_echeancier"><a href="echeancier.7.php">Affichage</a></li>
        <li id="chrono_ech"><a href="chrono_ech.7.php">Chrono + échéancier</a></li>
        <li id="dernier_match"><a href="dernier_match.7.php">Dernier match</a></li>';
if ($niveau=='admin') {
$menu.= ' <li>-</li>
        <li><a href="integre_echeancier.7.php">Import Echéancier</a></li>
        <li><a href="creation_echeancier.7.php">Création Echéancier</a></li>
        <li><a href="raz.7.php?cible=ech">RAZ Echéanciers</a></li>';
}      
 $menu.=  '</ul>
    </li>
     <li><a href="#">Outils</a>
        <ul>
          <li><a href="deconnexion.7.php">Deconnexion</a></li>
          <li><a id="a_propos" href="#">A propos</a></li>';
if ($niveau=="admin") {
$menu.='<li>-</li>
          <li><a href="init_config.7.php">Initialisation configuration</a></li>
          <li><a href="raz_db.7.php">Effacement de la base de données</a></li>
          ';
       
}
$menu.=  ' </ul>
       </li>
        <li><span style="font-size:10px;">Connecté -> '.$_SESSION["_login"].'</span></li>
  </ul>
  <br style="clear: left" />
<div id="present">
<h2>
Apr&eacute;s avoir import&eacute; l\'&eacute;ch&eacute;ancier et la liste des joueurs depuis <b>badplus</b>, <br>
cette application permet 
  <li>de piloter un tournoi en g&eacute;rant précisement
    <ul>
    <li> le temps entre l\'appel d\'un match et son d&eacute;but (3 min)</li>
    <li>l\'occupation des terrains</li>
    <li>le suivi de l\'&eacute;ch&eacute;ancier </li>
    </ul>
  </li>


<li>D\'autre part vous avez &eacute;galement
  <ul> 
    <li>la possibilit&eacute; de pointer les joueurs &agrave; leur arriv&eacute;e</li>
  </ul>
</li>
<li>En utilisation r&eacute;seau vous pouvez :
  <ul>
    <li>suivre depuis la table de marque, l\'arriv&eacute;e des joueurs point&eacute;s &agrave; la table de pointage</li>
    <li>afficher par exemple sur un &eacute;cran pr&eacute;s du panneau d\'affichage des tableaux, un &eacute;ch&eacute;ancier dynamique qui se met &agrave; jour en temps r&eacute;el.</li>
 </ul>
</li>
Dernier point : cette application est optimis&eacute;e pour <b>Firefox</b>. <br>
Avec d\'autres browser (ie, opera,chrome...) les affichages ne seront peut &ecirc;tre pas corrects
</h2>
<div style="float:right;font-size:small;" class="ui-menubar ui-widget-header">Version '.$version.'-Avril 2015-par F.Urbaniak</div>
</div>
<script text="text/javascript">
  $("#present").dialog({
                        title:"A propos de Multi_chorno '.$version.'",
                        resizable: false,
                        height: "auto",
                        width: "auto",
                        modal: true,
                        autoOpen: false,
                        buttons: {
                            OK: function() {
                                           $(this).dialog("close");
                            }
                        }
                      }
        );
    $("#a_propos").click(function(){
                                    $("#present").dialog("open");
                                  }
    );
            $(document).ready(function() {
             
               // remplace la flèche droite par la flèche bas pour les menus de premier niveau 
             $("#menuprinc > li > span.ui-icon-carat-1-e").removeClass("ui-icon-carat-1-e").addClass("ui-icon-carat-1-s"); 
            
            });    
</script>
';
echo $menu;

