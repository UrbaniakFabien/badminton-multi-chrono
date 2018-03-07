<?php
session_start();
/***********************************************************************
 * Affichage automatique des informations relatives au plus grand N° de match en cours
 * FU
 * 2013
 * ********************************************************************/
    
 $num_titre= isset($_GET["num_titre"]) ? $_GET["num_titre"]:0;
 if ($num_titre==0) { 
 $num_titre=isset($_SESSION["num_titre"]) ? $_SESSION["num_titre"]:0;
 }
  if ($num_titre==0) {
    $appelant="dernier_match.7";
    $titre="Dernier match";
    include ("demande_num.7.php");
    exit();
  }     

        $_SESSION["num_titre"]=$num_titre; //Mémorise pour la session le N° de lieu date en cours
        include("connect.7.php");
        include ("couleurs_ech.5.2.php");
        //Titre donné à la page
        $sql="SELECT lieu_date from titre where num_titre=" .$num_titre;
        $result=mysqli_query($connect,$sql);
        $data=mysqli_fetch_assoc($result);
        $titre="Echéancier " .$data["lieu_date"];
//
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title><?php echo $titre?></title>

   <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
<link id="css_coul" rel="stylesheet" type="text/css" title="currentStyle" href="css/echeancier_couleurs.css"> 
<link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" rel="stylesheet" />

 <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
 <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

 <script type="text/javascript" src="js/menu.js"></script>

<script type="text/javascript">
    var tempo=10; //Temporisation par défaut pour le rafraichissement
  function change_ech() {
      $("#modal_form").dialog("open");
    } 
 function callComplete(reponse) {
 /******************************************************************************
 *Mise à jour du tableau si modification de la base
 *reponse contient le Num et l'état des lignes modifiées
 *******************************************************************************/
    if (reponse.length>0) {
        $("#num_match").html(reponse[0].Num_match);
        $("#tableau").html(reponse[0].tableau);
        $("#heure_debut").html(reponse[0].heure_debut);
        $("#num_terrain").html(reponse[0].terrain);
        $("#spe").html(reponse[0].spe);
    }
    //Appel Tempo toutes les 10 secondes
    var t=setTimeout( "connect();",tempo*1000);   
    };
    
    

  function connect() {
    // boucle infinie : demande de donnée toutes les 15s
   
      $.post('ajax/info_match.1.php', {}, callComplete, 'json');
    
  };
  // Initialisation du document
    $(document).ready(function() {
      $("#dernier_match").html("<a href='#'>Dernier match</a><ul><li><a>Rafraichissement</a><ul><li><a>1 s<span></span></a></li><li><a>5 s<span></span></a></li><li><a>10 s<span class='sel' ></span></a></li><li><a>15 s<span></span></a></li></ul></li>");
        
           
/* definition du menu */            
$( "#menuprinc" ).menu({
                        autoExpand: true,
                  			menuIcon: true,
                  			buttons: true,
                        position: { using: positionnerSousMenu},
                        select:function(event,ui){
                                                 var text_select=ui.item.text();
                                                 if ((text_select=="1 s") || (text_select=="5 s") || (text_select=="10 s") || (text_select=="15 s")) {
                                                        //On efface la selection précédente
                                                        $("span.ui-icon-check.ui-menu-icon").removeClass();
                                                        //On montre la selection courante
                                                        ui.item.children().children().addClass('ui-menu-icon ui-icon-check ui-icon');
                                                         switch  (text_select) {
                                                             case "1 s": tempo=1;
                                                                         break;
                                                             case "5 s": tempo=5;
                                                                         break;
                                                             case "10 s" :tempo=10;
                                                                          break;
                                                             case "15 s" :tempo=15;
                                                                          break;
                                                         }
                                                }
                        }
            });
      //Ajout des classes pour montrer quel est le délai de rafraichissement par défaut
      $(".sel").addClass('ui-menu-icon ui-icon-check ui-icon');                              
     //Lancement de la boucle de  rafraichissment des données
      connect();
     //Lancement du timer pour l'horloge  
      timer();                             
      
     });
     
     
 function timer() {
 /*Affichage de l'heure en continu */
    var sys_time=new Date();
    var heure= sys_time.getHours();
    var min=   sys_time.getMinutes();  
    var sec=   sys_time.getSeconds();
    if (heure<10) {heure="0"+heure};
    if (min<10) {min="0"+min};
    if (sec<10) {sec="0"+sec};
    $("#horloge").html( heure+ " : " + min + " : " + sec); 
    var  t=setTimeout( "timer();",1000);   //Appel Tempo toutes les 10s
 }  
  
   
  
 


 </script>
<style type="text/css">
    td {
  border-radius: 30px;
  border-style: solid;
  border-width: 1px;
  font-size: 55px;
  padding-left: 20px;
}
span {text-align:center;}
.grand_car {
	font-size : 300px;
	color:white;
}
.moyen_car {
	font-size : 90px;
	color:white;
	text-align:center;
}
</style>
</head>
<body style='background-color:black';>

<?php include ("menu.5.1.php"); ?>


  
      <div id="dernier"  >
	  <!--
        <table>
          <tr>
            <td>Dernier N° de match en cours</td><td><span id="num_match"></span></td>
          </tr>
          <tr>
            <td rowspan="2">Tableau</td><td> <span id="spe"></span></td>
          </tr>
          <tr>
            <td><span id="tableau"></span></td>
          </tr>
          <tr>
            <td>Heure de début</td><td><span id="heure_debut"></span></td>
          </tr>
          <tr>
            <td>Terrain N°</td><td><span id="num_terrain"></span></td>
          </tr>
        </table>
		-->
		
		<p class="moyen_car">PROCHAIN MATCH</p>
		<div style="margin:auto;width:210px;" class='grand_car' id="num_match"></div>
      </div>
</body>
</html>
