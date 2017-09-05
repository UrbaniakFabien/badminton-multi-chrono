// Module de controle de l'echeancier
 

  // Initialisation du document
   function init_ech() {
       $("#echeancier td").each(function() {
                                var $thisParagraph = $(this);
                                var count = 0;
                                var class_ini="";
                                $thisParagraph.click(function() {
                                 var Sys_date_heure=new Date();
                                 var heure=Sys_date_heure.getHours()  ;
                                 var min= Sys_date_heure.getMinutes();
                                 if (min<10) {min="0"+min}
                                count++;  
                                var id=$thisParagraph.attr("id");
                                //si un id existe on traite
                                if (id!=undefined) {
                                        if (class_ini=="") {
                                          tab=id.split("_");
                                          class_ini=tab[1];
                                          class_ini=$("#classe_"+class_ini).val();
                                        }
                                        if (count>=4) {
                                            count=0;
                                            $("#input_"+id).val("0");
                                        } ;
                                        $thisParagraph.removeClass()
                                                      .toggleClass("etat_"+count,(count!=0))
                                                      .toggleClass(class_ini, (count==0 || count==3)); 
                                       
                                        
                                        
                                        /*****************************************************
                                         * Mise à jour de la base
                                         *****************************************************/                                                                                 
                                        $.ajax({
                                                type: "POST",
                                                url: "ajax/majech.1.1.php",
                                                data: {num: id,
                                                       etat: count,
                                                       terrain: $("#input_"+id).val(),
                                                       heure :heure+"h"+min
                                                       }
                                              });
                                        /*****************************************************
                                         * Mise à jour des zones heures début et fin de match 
                                         * et N° de terrain
                                         *****************************************************/
                                        switch  (count) {
                                              case 3  :
                                              case 0  : $thisParagraph.children("span:eq(1)").html("");
                                                       $thisParagraph.children("span:eq(2)").html("");
                                                       $thisParagraph.children("span:eq(0)").hide();
                                                       break;
                                              case 1 :$thisParagraph.children("span:eq(1)").html("D&eacute;but : "+heure+"h"+min+"<br />");
                                                      $thisParagraph.children("span:eq(0)").show();
                                                      break;
                                              case 2 : $thisParagraph.children("span:eq(2)").html("Fin : "+heure+"h"+min+"<br />");
                                                      break;
                                              
                                                      
                                        }
                                }        
                                });
        });  
       
       
     //Par défaut =>echéancier noir et blanc                         
     $("#css_coul").attr ("href","echeancier_no_couleurs.css") 
     };



