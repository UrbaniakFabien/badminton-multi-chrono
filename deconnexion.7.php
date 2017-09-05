<?php
 session_start(); // on initialise les sessions PHP

unset($_SESSION['_login']);
unset($_SESSION['_pass']);
unset($_SESSION["num_titre"]);
unset($_SESSION["_niveau"]);
$tab_config=parse_ini_file("config.ini");
$version=$tab_config["version"];
?>
<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel="icon" href="images/favicon.gif" />
  <title>Deconnexion multi-chrono</title>
  <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
  <script type="text/javascript" src="jquery/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
  <link rel="stylesheet" type="text/css" href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" />
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
  $(document).ready(function() {
           $("#frm_deconnect").dialog({
                              title:"Déconnexion multi-chrono <?php echo $version;?>",
                              autoOpen: true,
                              width: 420,
                              height:'auto',
                              //position:'center',
                              resizable:false,
                            	modal: true,
                            	buttons: {
                                			OK:function(){$(this).dialog("close");	
                                          }
                                      },
                            	close: function() {
                                        window.location="index.php";                                                                		
                            	}
                            });
  
  
  });

</script>
  </head>
  <body>
  <div id="frm_deconnect">
    
        <h2>Multi-Chrono <?php echo $version;?></h2> 
                                         
          <h3>Vous êtes maintenant deconnecté de l'application !!!</h3>
         
   
   </div>
  </body>
</html>