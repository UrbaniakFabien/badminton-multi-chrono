<?php
if (file_exists("config.ini")==false) {
  header ("Location:init_config.php");
  exit();
} 
session_start();
// on inclu la page de config
include("connect.7.php");
?>
<!DOCTYPE HTML>
<html>
<head>
  <link rel="icon" href="images/favicon.gif" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
  <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" rel="stylesheet" />
  <script type="text/javascript" src="jquery/jquery-2.1.3.js"> </script>
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
   /***************************************************************
    * Menu général
    * *************************************************************/  
    $("#menuprinc").menu({ position: { using: positionnerSousMenu} });
});
</script>                
</head>

<body>
<?php include ("menu.5.1.php");?>
<div style="text-align:center"><h1>MULTI-CHRONO <?php echo $version;?></h1></div>
</body>
</html>