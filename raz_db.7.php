<?php 
session_start();
include ('test_login.5.1.php');
$tab_config=parse_ini_file("config.ini");
$version=$tab_config["version"];
?>

<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>RAZ Base Multi-chrono <?php echo $version; ?></title>
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
           $("#frm_clear").dialog({
                              title:"RAZ Base multi-chrono <?php echo $version; ?>",
                              autoOpen: true,
                              width: 420,
                              height:'auto',
                              position:'center',
                              resizable:false,
                            	modal: true,
                            	buttons: {
                                				Ok: function() {
                                					                   $.post("ajax/razdb.php",
                                                                   function(reponse){ $("#msg").html("<p style='color:green;'>Base effacée!</p>");
                                                                                    }
                                                             )                   
                                				},
                                        Retour : function(){$(this).dialog("close");
                                                           
                                        }
                                      },
                            	close: function() { window.location="index.php";                                                               		
                            	}
                            });
  
  
  });

</script>
  </head>
  <body>
    <div id="frm_clear">
        <h2>Multi-Chrono <?php echo $version; ?></h2> 
        <h3>Confirmez-vous cette demande d'effacement de la base ? </h3>
        <span id="msg"></span>
    </div>
  </body>
</html>
