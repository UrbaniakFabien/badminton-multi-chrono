<?php

/***********************************************************
 * Module pour generer les deux CSS
 * pour echéancier avec ou sans couleurs
 * 
 *FU 
 *le 10/01/2014
 *mise en majuscule des nom de class pour les specialités    
 * *********************************************************/  


function couleur_aleatoire() {
global $encours;
global $termine;
global $pause;
global $horaire;
    //couleur de fond au hasard 
 	 $a = DecHex(mt_rand(0,15)); 
 	 $b = DecHex(mt_rand(0,15)); 
 	 $c = DecHex(mt_rand(0,15)); 
 	 $d = DecHex(mt_rand(0,15)); 
 	 $e = DecHex(mt_rand(0,15)); 
 	 $f = DecHex(mt_rand(0,15)); 
 	  
 	 $hexa = $a . $b . $c . $d . $e . $f; 
   //Pour Ã©viter les couleur retenue pour : encours et fini et pause
 	 if (($hexa==$encours) || ($hexa==$termine) || ($hexa==$pause)|| ($hexa==$horaire)) {
      $hexa=couleur_aleatoire();
    } else { 	
      return $hexa;
    }	  
 
}
function html2rgb($color){
    if ($color[0] == '#')
    $color = substr($color, 1);
    
    if (strlen($color) == 6)
    list($r, $g, $b) = array($color[0].$color[1],
    $color[2].$color[3],
    $color[4].$color[5]);
    elseif (strlen($color) == 3)
    list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1],   $color[2].$color[2]);
    else
    return false;
    
    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    
return array("r"=>$r,"g"=>$g,"b"=>$b);
}

function rgb2hex($r, $g, $b, $uppercase=false, $shorten=false)
        {
        // The output
        $out = "";

        // If shorten should be attempted, determine if it is even possible
        if ($shorten && ($r + $g + $b) % 17 !== 0) $shorten = false;

        // Red, green and blue as color
        foreach (array($r, $g, $b) as $c)
        {
        // The HEX equivalent
        $hex = base_convert($c, 10, 16);

        // If it should be shortened, and if it is possible, then
        // only grab the first HEX character
        if ($shorten) $out .= $hex[0];

        // Otherwise add the full HEX value (if the decimal color
        // is below 16 then we have to prepend a 0 to it)
        else $out .= ($c < 16) ? ("0".$hex) : $hex;
        }
        // Package and away we go!
return $uppercase ? strtoupper($out) : $out;
}
        
      
/*********************************************
 *Generation chaine des class specialités
**********************************************/
   $sql="show tables like \"echeancier\"";
   $result=mysqli_query($connect,$sql) ;
  
   if ($data=mysqli_fetch_row($result)) {
   
            $f = 'css/echeancier_no_couleurs.css';
            $text = "";
            $handle = fopen($f,"w");
            
            // regarde si le fichier est accessible en écriture
            if (is_writable($f)) {
              //Sql pour ne generer que les spe du tournoi en cours
                $replace="replace(upper(SPE),'$sign[0]','$chg_sign[0]')";
                for ($i=1;$i<count($sign);$i++) {
                    $replace = "replace($replace,'$sign[$i]','$chg_sign[$i]')";
                }
              $sql2="SELECT  $replace as spec FROM `echeancier` 
                    WHERE num_titre=".$_SESSION["num_titre"]."
                    group by spec";
                $sql="SELECT coul_specialite,coul_couleur 
                      FROM tbl_couleurs
                      WHERE coul_id_titre=0 and coul_specialite in ($sql2)
                            OR coul_specialite='Couleur_texte'
                      ORDER BY coul_specialite";
              $result=mysqli_query($connect,$sql);
			 
              $style="";
              $lst_style="";
              $lst_coul_style="";
              
              // Génération des classes en fonction des catégories des tableaux
              while ($data=mysqli_fetch_row($result)) {   
                $index_couleur=trim(str_replace($sign,$chg_sign,$data[0]));
                $style="." .$index_couleur." ";
                if ($style!=".PAUSE ") {
                    //$couleur=couleur_aleatoire();      
                    $couleur=$data[1];      
                }
                else {
                    $couleur=$pause;
                }
                if ($index_couleur=='Couleur_texte') {
                    $couleur_texte="#".$couleur;
                }
                $tab_couleur[$index_couleur]=html2rgb($couleur);
                $lst_coul_style.= $style."{ background-color:#".$couleur.";color:white;}"."\r\n";
                $lst_style.=$style;
              }
              $lst_coul_style=str_replace("white",$couleur_texte,$lst_coul_style);
              //Génération du texte des fichiers css
              $text=$lst_style ." {
                  color : black;
                  background-color:white;
                  
                }
                .PAUSE {
                        background-color:#".$pause.";
                        font-style:italic;
                }
                #echeancier td {
                  width:180px;
                  font-weight : bold; 
                  border-style:solid;
                  border-width:thin;
                  border-color:grey;
                  text-align:center;
                  border-radius : 10px;
                }
                #echeancier {                          
                            width:100%;
                            }
                 .num_match{
                  width:30px;
                  background:transparent;
                  font-weight : bold;
                  
                  border-style:hidden;
                  font-style: century gothic;
                  font-size: 16px;
                }
                
                #echeancier .horaire {
                  width:60px;
                  text-align:center;
                  background-color:#CCFFFF ;
                  color:black;
                }
.etat_1 {
          background-color: #".$encours.";
          color: red;      
          
}
.etat_2 {
          background-color: #".$termine.";
          text-decoration:line-through;
          background-image:url('images|stripetranspar.png');
          color:white;
      
}
.etat_3 {
          text-decoration:line-through;
          background-image:url('images|WO.png');
          background-repeat:no-repeat;
          background-position:center;
          background-size: 90px;
          color:white;
      
}
.aucun {
         background-color:LightGray;
}
.titre {
  font-style:bold; 
  texte-decoration:underline;
  background-color:red;
}
@media print {
button {
display:none;}
input {
display:none;}
.terrain {
display:none;
}
}
                ";       
             // Ecriture   echeancier_no_couleur.css
            
              if (fwrite($handle, str_replace("|","/",str_replace("/","_",$text))) === FALSE) {
                  echo 'Impossible d\'écrire dans le fichier '.$f.'';
                  exit;
                }
                fclose($handle);
                
                /*Génération du fichier  echeancier_couleur.css*/    
                $f = 'css/echeancier_couleurs.css';
                $text = "";
                $handle = fopen($f,"w");
            
            // regarde si le fichier est accessible en écriture
                    if (is_writable($f)) {
                          $text.=$lst_coul_style ."
                          #echeancier td {
                            width:180px;
                            font-weight : bold;
                           
                            border-style:solid;
                            border-width:thin;
                            border-color:grey;
                            text-align:center;
                            border-radius : 10px;
                          }
                          #echeancier {
                                      width:100%;
                                      }
                          .num_match {
                            width:30px;
                            background:transparent;
                            font-weight : bold;
                            color:white;
                            border-style:hidden;
                            font-style: century gothic;
                            font-size: 16px;
                          }
                          .PAUSE {
                                  background-color:#".$pause.";
                                  font-style:italic;
                                 }
                          #echeancier .horaire {
                            width:60px;
                            text-align:center;
                            background-color:#CCFFFF ;
                            color:black;
                          }
                .etat_1 {
          background-color: #".$encours."; 
          color:red;     
          
}
.etat_2 {
          background-color: #".$termine.";
          text-decoration:line-through;
          background-image:url('images|stripetranspar.png');
          color:white;
      
}
.etat_3 {
          text-decoration:line-through;
          background-image:url('images|WO.png');
          background-repeat:no-repeat;
          background-position:center;
          background-size: 90px;
          color:white;
      
}
.aucun {
         background-color:LightGray;
}
.titre {
  font-style:bold; 
  texte-decoration:underline;
  background-color:red;
}
@media print {
button {
display:none;}
input {
display:none;}
.terrain {
display:none;
}
}
                          
                          ";       
                           // Ecriture  fichier echeancier_couleur.css
                          
                              if (fwrite($handle, str_replace("|","/",str_replace("/","_",$text))) === FALSE) {
                                echo 'Impossible d\'écrire dans le fichier '.$f.'';
                                exit;
                              }
                              fclose($handle);                  
                    }
            }
            else {
                  echo 'Impossible d\écrire dans le fichier '.$f.'';
            }

 }
 $tab_couleur["horaire"]=html2rgb($horaire);
 $_SESSION["tab_couleur"]=$tab_couleur;
?>
