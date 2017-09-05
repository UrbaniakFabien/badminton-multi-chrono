<?php
/*******************************************************************************
 * Module initialisation du fichier config.ini
 * FU
 * 12/2013
 * *****************************************************************************/
 $tab_config=array();
 if (file_exists("config.ini")==true) {
   $tab_config=parse_ini_file("config.ini");
   $mdp_administrateur = $tab_config["mdp_administrateur"]; 
   $nom_administrateur = $tab_config["nom_administrateur"];
   $mdp_utilisateur= $tab_config['mdp_utilisateur'];
   $nom_utilisateur= $tab_config['nom_utilisateur'];
   $serveur=$tab_config["nom_serveur_sql"];
   $user=$tab_config["nom_utilisateur_sql"];
   $password=$tab_config["mdp_sql"];
   $db=$tab_config["nom_base_sql"];
   $version=$tab_config["version"];
 } else {
   $mdp_administrateur = "motdepasse"; 
   $nom_administrateur= "administrateur";
   $mdp_utilisateur="utilisateur";
   $nom_utilisateur="utilisateur";
   $serveur="localhost";
   $user="root";
   $password="";
   $db="tournoi";
   $version="7";
 }   
?>
<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel="icon shortcut" href="images/favicon.gif" />
  <title>Configuration accès multi-chrono</title>
  <script type="text/javascript" src="jquery/jquery-2.1.3.min.js"></script>
  <script type="text/javascript" src="jquery/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
  <link rel="stylesheet" type="text/css" href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" />
 
 
<script type="text/javascript">
  
  $(document).ready(function() {
           $("#frm").dialog({
                              title:"Configuration multi-chrono <?php echo $version; ?>",
                              autoOpen: true,
                              width: 'auto',
                              height:'auto',
                              position:'center',
                              resizable:false,
                            	modal: true,
                            	buttons: {
                                        'Test MYSQL': function(){
                                                              $.post("ajax/test_mysql.php",
                                                              $("#frm_connect").serialize(),
                                                              function(reponse){
                                                                                $("#msg").html("<p style='color:"+reponse.color+";'>"+reponse.msg+"</p>");        
                                                                               },
                                                              "json"    
                                                           )
                                        },
                                				Enregistre: function() {
                                					                   $.post("ajax/enregistre_config.php",
                                                                    $("#frm_connect").serialize(),
                                                                    function(reponse){
                                                                                      $("#msg").html("<p style='color:green;'>Donn&eacute;es enregistr&eacute;es!</p>");        
                                                                                               }
                                                                      
                                                             )
                                                                      
                                                             
                                				},
                                        Suite: function(){
                                                           window.location="index.php";
                                        }
                                       
                                      },
                            	close: function() { window.location="index.php";                                                                		
                            	}
                            });
  
                                   
  });

</script>
  </head>
  <body>
  <div id="frm">
   <form id="frm_connect">
        <input type='hidden' value='<?php echo $version;?>' name='version' /> 
        <h2>Configuration Multi-Chrono <?php echo $version;?> </h2> 
        <div id="msg"></div>
        <table>
          <tr><td colspan="2"><h3>Configuration MySQL</h3></td></tr>                                  
          <tr><td>Serveur Mysql       :</td><td> <input name="nom_serveur_mysql" type="text" value="<?php echo $serveur;?>"/></td></tr>
          <tr><td>Utilisateur Mysql   :</td><td> <input name="utilisateur_mysql" type="text" value="<?php echo $user;?>" /></td></tr>
          <tr><td>Mot de passe Mysql  :</td><td> <input name="mdp_mysql" type="text" value="<?php echo $password;?>" /></td></tr>
          <tr><td>Base de donn&eacute;es   :</td><td> <input name="nom_db_mysql" type="text" value="<?php echo $db; ?>" /></td></tr>
          
          <tr><td colspan="2"><h3>Configuration acc&eacute;s Multi-chrono</h3><br /></td></tr>
          <tr><td>Administrateur multi-chrono&nbsp;&nbsp;&nbsp;&nbsp;:</td><td><input type="text" name="nom_administrateur" value="<?php echo $nom_administrateur; ?>"/></td></tr>
          <tr><td>Mot de passe administrateur :</td><td><input type="password" name="mdp_administrateur" value="<?php echo $mdp_administrateur;?>"/></td></tr>
          <tr><td>Utilisateur multi-chrono&nbsp;&nbsp;&nbsp;&nbsp;:</td><td><input type="text" name="nom_utilisateur" value="<?php echo $nom_utilisateur; ?>"/></td></tr>
          <tr><td>Mot de passe utilisateur :</td><td><input type="password" name="mdp_utilisateur" value="<?php echo $mdp_utilisateur;?>"/></td></tr>
          
        </table> 
    </form>
   </div>
  </body>
</html>
