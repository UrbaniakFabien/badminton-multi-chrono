<?php
$tab_config=parse_ini_file("config.ini");
$version=$tab_config["version"]; ?>
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
  var tentative=0; //Nombre de tentatives avant rejet
  $(document).ready(function() {
           $("#frm").dialog({
                              title:"Connexion multi-chrono <?php echo $version;?>",
                              autoOpen: true,
                              width: 'auto',
                              height:'auto',
                              //position:'center',
                              resizable:false,
                            	modal: true,
                            	buttons: {
                                				Connecte: function() {
                                					                   $.post("ajax/test_loggin.5.php",
                                                                    $("#frm_connect").serialize(),
                                                                    function(reponse){
                                                                                               if (reponse!="") {
                                                                                                  $("#msg").html("Nom utilisateur et Mot de passe valides !")
                                                                                                           .css("color","green")
                                                                                                           .fadeIn(1500).delay(3500).fadeOut(1500);
                                                                                                  window.location=reponse;
                                                                                               }
                                                                                               else {
                                                                                                  $("#msg").html("Mot de passe ou Nom utilisateur invalide !")
                                                                                                           .css("color","red")
                                                                                                           .fadeIn(1500).delay(3500).fadeOut(1500);
                                                                                                                                                                                                                    
                                                                                                  tentative++;
                                                                                                  if (tentative>=3) {
                                                                                                            $("#msg").html("Nombre max de tentatives !<br/>vous allez être redirigé vers une autre page !")
                                                                                                                     .css("color","red")
                                                                                                                     .fadeIn(1500).delay(3500).fadeOut(1500);
                                                                                                            window.location="mon_echeancier.7.php";
                                                                                                  }
                                                                                               }
                                                                      }
                                                             )
                                                                      
                                                             
                                				},
                                        Annule: function(){
                                                           window.location="mon_echeancier.7.php";
                                        },
                                        Deconnecte : function(){
                                                                window.location="deconnexion.7.php";
                                        }
                                      },
                            	close: function() { $.post("deconnexion.php");
                                                  window.location="mon_echeancier.7.php";                                                                		
                            	}
                            });
  
  
  });

</script>
<style type="text/css">
    .ui-dialog {
        opacity:0.74;
    }
</style>
  </head>
  <body>
  <div id="frm">
   <form id="frm_connect"> 
        <h2>Multi-Chrono <?php echo $version;?></h2> 
        <div id="msg"></div>
        <fieldset>
          <table>
          <tr>
            <td>Utilisateur</td><td> : <input type="text" name="login" value=""/></td>
          </tr>
          <tr>
            <td>Mot de passe</td><td> : <input type="password" name="mdp" value=""/></td>
          </tr>
          </table>
         </fieldset>
    </form>
   </div>
  </body>
</html>
