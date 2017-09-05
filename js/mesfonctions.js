/*******************************************************************************
 * Fonctions utilisées dans la partie echeancier et chrono
 * Fu
 * 24/01/2014
 *    
 * couleur_hasard()
 * change_regle(nom_classe, nom_regle, param)
 * hex2rgb(h)
 * cutHex(h)  
 * rgb2hex(rgb)
 * get_couleur(p_nom_classe, couleur)
 * timer()
 * change_couleur()
 * heure_en_minute
 * minute_en_heure      
 *******************************************************************************/ 


  /***************************************************************************
     * Genere une couleur au hasard
     ***************************************************************************/         
    function couleur_hasard() {
          var color = new Array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');

          a = Math.floor(Math.random() * color.length);
          b = Math.floor(Math.random() * color.length);
          c = Math.floor(Math.random() * color.length);
          d = Math.floor(Math.random() * color.length);
          e = Math.floor(Math.random() * color.length);
          f = Math.floor(Math.random() * color.length);
          coul = '#' + color[a] + '' + color[b] + '' + color[c] + '' + color[d] + '' + color[e] + '' + color[f];
          if ((coul == $(".etat1").css("background-color")) || (coul == $(".etat2").css("background-color")) || (coul == $(".PAUSE").css("background-color"))) {
              couleur_hasard();
          }
          return coul;
      }
     /**************************************************************************
      * Fonction pour modifier une regle dans une classe dans la css           
      **************************************************************************/ 
     function change_regle(nom_classe, nom_regle, param) {
       
        var ss = document.styleSheets;

        for (var i = 0; i < ss.length; i++) {
            var rules = ss[i].cssRules || ss[i].rules;

            for (var j = 0; j < rules.length; j++) {
                if (rules[j].selectorText == nom_classe) {
                    rules[j].style[nom_regle] = param;
                }
            }
        }
    };
    
    /***************************************************************************
     * Conversion d'une couleur hexa en rgb
     ***************************************************************************/         
    function hex2rgb(h)
    {
        var hexa=cutHex(h),r = parseInt(hexa.substring(0, 2), 16), g = parseInt(hexa.substring(2, 4), 16), b = parseInt(hexa.substring(4, 6), 16);
        return {r: r, g: g, b: b};
    }
    function cutHex(h) {
        return (h.charAt(0) == "#") ? h.substring(1, 7) : h;
    }

    /***************************************************************************
     * Fonction de conversion d'une couleur rgb en hexa
     ***************************************************************************/         
    function rgb2hex(rgb) {
        rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        return "#" +
                ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2);
    }
    
    /***************************************************************************
     * Fonction de lecture d'une couleur d'une classe dans la CSS
     ***************************************************************************/         
    function get_couleur(p_nom_classe, couleur) {
        var ss = document.styleSheets;

        for (var i = 0; i < ss.length; i++) {
            var rules = ss[i].cssRules || ss[i].rules;

            for (var j = 0; j < rules.length; j++) {
                if (rules[j].selectorText == p_nom_classe) {
                    return rgb2hex(rules[j].style.backgroundColor);
                }
            }
        }
    }       
 /******************************************************************************
  * Affichage de l'heure
  ******************************************************************************/          
 function timer() {
 /*Affichage de l'heure en continu */
    var sys_time=new Date();
    var heure= sys_time.getHours();
    var min=   sys_time.getMinutes();  
    var sec=   sys_time.getSeconds();
    heure= ("00"+heure).slice(-2);
    min  = ("00"+min).slice(-2);
    sec  = ("00"+sec).slice(-2);
    $("#horloge").html( heure+ ":" + min + ":" + sec); 
    var  t=setTimeout( "timer();",1000);   //Appel Tempo toutes les 10s
 }  
 /******************************************************************************
  * mise en forme d'une heure
  ******************************************************************************/   
  function formatSecondsAsTime(secs, format) {
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600)) / 60);
    var sec = Math.floor(secs - (hr * 3600) - (min * 60));
  
    hr  = ("00" + hr).slice(-2);
    min = ("00" + min).slice(-2);
    sec = ("00" + sec).slice(-2);
    
    if (format !== null) {
        var formatted_time = format.replace('hh', hr);
        formatted_time = formatted_time.replace('h', hr * 1 + ""); // check for single hour formatting
        formatted_time = formatted_time.replace('mm', min);
        formatted_time = formatted_time.replace('m', min * 1 + ""); // check for single minute formatting
        formatted_time = formatted_time.replace('ss', sec);
        formatted_time = formatted_time.replace('s', sec * 1 + ""); // check for single second formatting
        return formatted_time;
    } else {
        return hr + ':' + min + ':' + sec;
    }
}

  /*****************************************************
   * Passe l'echéancier de couleur à Noir et blanc ou
   * réciproquement
   *****************************************************/
 function change_couleur(el) {

 var $o= $(el);
  if (!($o.html().indexOf('couleur') == -1)) {
      $o.html($o.html().replace('couleur','noir et blanc'));
     for (i = 0; i < tab_class.length; i++) {
          if (tab_class[i] !== "PAUSE") {
              //Remise en place des couleurs telles qu'elles sont dans le formulaire de gestion des couleurs
              var couleur= hex2rgb( $("#"+tab_class[i]).val());
              change_regle("." + tab_class[i], "backgroundColor", "rgb(" + couleur.r + "," + couleur.g + "," + couleur.b + ")");
          }
      }
      //le texte passe en blanc
      //change_regle("#echeancier td", "color", "rgb(255,255,255)");
  } else {
      $o.html($o.html().replace('noir et blanc','couleur'));
      for (i = 0; i < tab_class.length; i++) {
          if (tab_class[i] !== "PAUSE") {
              //On met en noir et blanc
              change_regle("." + tab_class[i], "backgroundColor", "rgb(255,255,255)");
          }
      }
      //le texte passe en noir
      //change_regle("#echeancier td", "color", "rgb(0,0,0)");
  }
}
/*******************************************************************************
 * Modifie la couleur du texte dans l'échéancier
 *******************************************************************************/ 
 
function text_noir_blanc(p_rgb) {
    var i=0;
    for (i = 0; i < tab_class.length; i++) {
              if  ((tab_class[i] !== "Couleur_texte"))  {
                           change_regle("."+tab_class[i],'color',"rgb("+p_rgb.r+","+p_rgb.g+","+p_rgb.b+")");
               
              }
     }
}

/*******************************************************************************
 * Conversion d'heures xxhxx en minutes
 *******************************************************************************/
 function heure_en_minute(p_heure) {
    var tab_heure=p_heure.split("h");
    return (parseInt(tab_heure[0])*60) + parseInt(tab_heure[1]);
 
 }
 /*******************************************************************************
 * Conversion minutes xxxx en xxhxx
 *******************************************************************************/
 function minute_en_heure(p_minute) {
    var heures=Math.floor (p_minute/60);
    var minutes=p_minute-(heures*60);
    
    return ("00"+heures).slice(-2)+"h"+("00"+minutes).slice(-2);
 
 }  
 
   /********************************************************
   * Affichage des horaires rééls   
   * ******************************************************/
   function affiche_horaires_reels(){
         $.ajax({
                    type    : 'GET',
                    url     : "ajax/horaires.php",
                    dataType: 'json',
                    timeout : 10000,
                    async   : false,
                    success : function(reponse){
                                      var derniere_heure_reel   = "00h00",
                                          derniere_heure_prevue = "00h00",
                                          ecart = 0;
                                      //Mise à jour des tranches horaires avec les données réelles
                                      for (i=0;i<reponse.length;i++){
                                          $("#" + reponse[i].horaire).children(".horaire").html(reponse[i].reel);
                                      }
                                      //Calcul de l'avance/retard par rapport à l'horaire théorique 
                                      derniere_heure_reel   = $(".horaire").last().html();
                                      derniere_heure_prevue = $(".horaire").last().parent().attr("id");
                                      ecart = heure_en_minute(derniere_heure_reel) - heure_en_minute(derniere_heure_prevue);
                                      avance="A l'heure ! ";
                                      $("#avance").css("color","green");
                                      if (ecart<0) {
                                          avance = "Avance : ";                                          
                                      }
                                      if (ecart>0) {
                                           avance = "Retard : ";
                                           $("#avance").css("color","red");
                                      } 
                                      if (ecart!=0)  { 
                                          ecart =  Math.abs(ecart);
                                          if (ecart<60) {
                                              avance+=ecart + "mn";
                                          }
                                          else {
                                              avance+=minute_en_heure(ecart);
                                          }
                                          $("#avance").html(avance);
                                      }
			}
		});
                  
   
   }