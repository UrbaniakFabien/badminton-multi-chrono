<?php
/***************************************************
 * Pour regenerer les CSS de l'échéancier via
 * AJAX
 * *************************************************/  
    include ("../connect.7.php");
  

        $encours="90EE90"; //couleur pour match en cours
        $termine="FF7F50"; //Couleur match termine
        $pause  ="DCDCDC"; //Couleur Pause
        $horaire="CCFFFF";  // Couleur pour horaire
       
         //table de conversion des signes pour le nom des classes
        $sign=array(" ","+","-","/");
        $chg_sign=array("_","plus",'moins',"_");
       
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
           //Pour éviter les couleur retenue pour : encours et fini et pause
         	 if (($hexa==$encours) || ($hexa==$termine) || ($hexa==$pause)|| ($hexa==$horaire)) {
              $hexa=couleur_aleatoire();
            } else { 	
              return $hexa;
            }	  
         
        }
        
      
/*********************************************
 *Generation du fichier CSS echeancier_no_couleur.css
 *retourne un tableau classe et couleur 
**********************************************/
   $sql="show tables like \"echeancier\"";
   $result=mysqli_query($connect,$sql) ;
  
   if ($data=mysqli_fetch_row($result)) {
   
            
              $sql="SELECT  SPE FROM `echeancier` group by spe";
              $result=mysqli_query($connect,$sql);
              $style=array();
             
              while ($data=mysqli_fetch_row($result)) {
                $style[]=array('classe'=> trim(str_replace($sign,$chg_sign,rtrim($data["spe"]))),'Couleur'=>"#".couleur_aleatoire());
              }
              echo json_encode($style);
    }
    
?>
