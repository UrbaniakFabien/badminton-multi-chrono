<?php 
$tab_config=parse_ini_file("config.ini");
$version=$tab_config["version"];
/*******************************************************************************
 * Remise à 0 des tables echeancier et joueurs
 *******************************************************************************/ 
$path=isset($path) ? $path : "";
?>

<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title></title>
  <script type="text/javascript" src="<?php echo $path; ?>jquery/jquery-2.1.3.js"></script>
  <script type="text/javascript" src="<?php echo $path; ?>jquery/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.css" />
 
  <style type="text/css">
   /*Pour limiter la taille affichée de la liste et scroll */
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
    
    /*pour l'animation d'attente dans la liste autocomplete */
    .ui-autocomplete-loading {
        background: white url('<?php echo $path; ?>images/ui-anim_basic_16x16.gif') right center no-repeat;
    }
    
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
 
  jQuery(document).ready(function() {
    $(".deroule").button({ icons: {
                                   primary: "ui-icon-triangle-1-s"
                                   },
                           text:false
                        })
                     .click(function(){
                                      var $this=$(this);
                                      var id=$this.attr("id");
                                      var nom_liste=id.substring(4,id.length);
                                      //Avant de delouler la liste, on ferme toutes celles déjà ouvertes
                                      $(".deroule").each (function(){
                                                                      var $this=$(this);
                                                                      var id=$this.attr("id");
                                                                      var nom_liste=id.substring(4,id.length);
                                                                      if ($("#"+nom_liste).autocomplete("widget").is(":visible")) {
                                                                          $("#"+nom_liste).autocomplete("close")
                                                                      }
                                                                      });
                                      //On ouvre la liste ciblée  
                                      // et on transfert le focus a la zone de saisie.
                                      //du coups l'effacement de la liste est geré si on quitte la zone de saisie                              
                                      $("#"+nom_liste).autocomplete("search","")
                                                      .focus();
                                                      
                                      return false;
                                      });
                         
      $("#modal_form").dialog({ title:"Choix lieu et date <?php echo " : ".$titre; ?>",
                                      width:  450,
                                      height: 300,
                                      position:'center',
                                      modal: true,
                                      resizable:false,
                                      autoOpen:true,
                                      buttons: [{
                                          text:"OK",
                                          'click': function() {
                                                                window.location="<?php echo $appelant.'.php?num_titre=' ?>" + $("#num_titre").val();
                                                              },       
                                           
                                          icons: { primary: 'ui-icon-check' }}, 
                                          {
                                          text:"Quitter", 
                                          'click':function() {
                                             
                                                              $( this ).dialog( "close" );
                                                              window.location="index.php";
                                          },
                                           icons: { primary: 'ui-icon-close' }
                                      }]
                                  }); 
   $("#message_form").dialog({ title:'Avertissement',
                                      width:  250,
                                      height: "auto",
                                      position:'center',
                                      modal: true,
                                      resizable:false,
                                      autoOpen:false, 
                                      buttons: [{
                                                  text:"Quitter",
                                                  click :function() {
                                                                $( this ).dialog( "close" );
                                                  },
                                                   icons: { primary: 'ui-icon-close' }
                                              }]
  }); 
                                       
                  
     $("#lieu_date").autocomplete({
                                  source:"<?php echo $path; ?>ajax/lst_lieux_dates.1.1.php?cible=ech&action=lst",
                                  minLength :0,
                                  select:function(event,ui) {
                                                             $("#lieu_date").val(ui.item.value);
                                                             $("#num_titre").val(ui.item.id);                                                        
                                                        }
                                });
       
                        });
</script>                        
    </head>
  <body>
   <div style="overflow-x:hidden;" id="modal_form">
      <input type="hidden" id="num_titre" />
           <fieldset>          
              
                  Lieu et date : <input style="width:300px;" type="text" id="lieu_date" /><button class="deroule" id="lst_lieu_date"></button>
               
          </fieldset>
          <div style="float:right;font-size:8px;font-style:italic;">version 1.0</div>
      </div>
      <div id="message_form">
       
        
      </div>
      
  </body>
</html>
