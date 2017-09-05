<?php
session_start();
// on inclus la page de config
include("connect.7.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <link rel="icon" href="images/favicon.gif" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
   <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
  <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" >
  <link href="css/menu.css" rel="stylesheet" >
   <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
   <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
  
   <script type="text/javascript" src="js/menu.js"></script>
<style type="text/css">
body {
      background-color:lightgrey;
      color:black;
      margin:auto;
      width:800px;
      height:600px;
      background-image:url('images/multi_chrono.png');
      background-repeat:no-repeat;
      background-position:center center;
      background-size : 260px auto; 
     
    } 

</style>
<script type="text/javascript">
$(document).ready(function(){

      $("#frm_info_echeancier").dialog({ title:'Informations Echéancier',
                                      width:  500,
                                      height: 'auto',
                                      //position:'center',
                                      modal: true,
                                      resizable:false,
                                      buttons: [{
                                          text:"Enregistre",
                                          'click': function() {
                                                               $.ajax({
                                                                      url:"ajax/enregistre_echeancier.php",
                                                                      data:$("#frm_info_echeancier").serialize(),
                                                                      type:"POST",
                                                                      success:function(reponse) { 
                                                                                $("#msg").html("<p style='color:green;'>Enregistrement ok !</p>");
                                                                      }
                                                               }); 
                                                              },       
                                           
                                          icons: { primary: 'ui-icon-disk' }}, 
                                          {
                                          text:"Quitter", 
                                          'click':function() {
                                             
                                                              $( this ).dialog( "close" );
                                                              window.location="index.php";
                                          },
                                           icons: { primary: 'ui-icon-close' }
                                      }]
                                  });

});
</script> 
</head>

<body>
<?php include ("menu.5.1.php");?>
<div style="text-align:center"><h1>MULTI-CHRONO <?php echo $version; ?> <h1></div>

    <form id="frm_info_echeancier">
        <fieldset>
        <table>
          <tr>
            <td>Lieu du tournoi</td><td> : <input type="text" name="lieu"></td>
          </tr>
          <tr>
            <td>Date du tournoi</td><td> : <input type="text" name="date"></td>
          </tr>
          <tr>
            <td>Heure de d&eacute;but (hh:mm)</td><td> : <input type="text" name="heure_debut"></td>
          </tr>
          <tr>
            <td>Nombre de match</td><td> : <input type="text" name="nombre_match"></td>
          </tr>
          <tr>
            <td>Temps de match (mm)</td><td> : <input type="text" name="temps_match"></td>
          </tr>
           <tr>
            <td>Num&eacute;ro du premier match</td><td> : <input type="text" name="premier_match"></td>
          </tr>
          <tr>
            <td>Liste des pauses (xx;xx;xx) </td><td> : <input type="text" name="liste_pauses"></td>
          </tr>
          <tr>
            <td>Nombre de terrains</td><td> : <input type="text" name="nombre_terrain"></td>
        </table>
        </fieldset>
        <span id="msg"></span>
    </form> 
    <script type="text/javascript">
        $(function() {
            $( "#menuprinc" ).menu({
                autoExpand: true,
            		menuIcon: true,
            		buttons: true,
                position: {at: "left bottom"}
            });
        
     });
    </script>
</body>
</html>