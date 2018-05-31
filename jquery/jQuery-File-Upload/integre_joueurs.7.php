<?php
// Module qui met en forme et importe les données
//pour la listes des joueurs du tournoi
include ("../../connect.7.php"); 
//On reccupere le parametre de type d'import
//0->remplace tout
//1->garde état joueurs
$param_import=isset($_POST["prm_import"])? $_POST["prm_import"] : 0;
$msg="";
//Parcour du répértoire pour créé un tableau de nom de fichier
$fichier = array();
$Directory="server/php/files";
$MyDirectory = opendir($Directory) or die('Erreur');
	while($Entry = @readdir($MyDirectory)) {
		if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..') {
    }  
		else {
    if ($Entry != '.' && $Entry != '..') {
			$fichier[]=$Entry;
    }
    } 
                 
	 }
closedir($MyDirectory);
$tab_titre=array();//Contient les informations pour le retour
//Integration de chaque fichier trouvé 
foreach ($fichier as $e_fichier) {          
          
//ouvrir le fichier txt
  $handle=fopen($Directory."/".$e_fichier,'r');
  if ($handle) {
      $ligne=utf8_encode(fgets($handle));
      $titre=addslashes($ligne);
      if (strpos($titre,"Joueurs en cours : ")===false) {
            $tab_titre[]=array("num_titre"=>0,"titre"=>"Fichier ".$e_fichier." non conforme !","nbr_joueur"=>0,"sans_licence"=>0);    
      }
      else {
                //On vide la table temporaire
                $sql="TRUNCATE tmp_joueurs";
                mysqli_query($connect,$sql);
                
                //Test si ce tournoi existe déjà
                $lieu_date=trim(str_replace("Joueurs en cours : ","",$titre));
                $sql="SELECT `num_titre` FROM `titre` WHERE  lieu_date='".$lieu_date."'";
                $result=mysqli_query($connect,$sql);             
                if ($data=mysqli_fetch_assoc($result)) { 
                  $num_titre=$data["num_titre"];
                  if ($param_import==1)  {
                  
                     //Copie des informations dans une table tampon
                   $sql="INSERT INTO tmp_joueurs ( tmp_nom, tmp_licence, tmp_etat, tmp_commentaire )
                          SELECT Joueur, Licences, etat ,commentaire
                          FROM joueurs
                          WHERE etat>0 and num_titre=".$num_titre;
                 
                    mysqli_query($connect,$sql);
                   
                  }
              
               //On supprime si les info existant déjà dans listes des joueurs 
                $sql="DELETE 
                      FROM joueurs
                      WHERE num_titre=".$num_titre;
                mysqli_query($connect,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error());
                
                }
                else { 
                 //sinon on créé ce tournoi dans la liste des tournoi
                  $sql="INSERT INTO titre (lieu_date)
                        VALUES ('".$lieu_date."');";
                       
                  $result=mysqli_query($connect,$sql);
                  $sql="SELECT MAX(num_titre) as id from titre;";
                  $result=mysqli_query($connect,$sql);
                  $data=mysqli_fetch_assoc($result);
                  $num_titre=$data["id"];
                
                }
              //lire la premiere ligne  : c'est le titre 
              
              $pos=strrpos($ligne," le ");
              $date_liste=substr($ligne,$pos+4);
              $lieu=substr($ligne,0,$pos);
              $lieu=str_replace("Joueurs en cours : ","",$lieu);
              $prem=true;
              $nbr_joueur=0; 
              //tant que pas fin de fichier
              while (!feof($handle)) 
                  {
                  //  lire l'enregistrement
                     $ligne=utf8_encode(rtrim(fgets($handle)));
                     if ($ligne!="") {
                         //L'export badplus de cette liste est incorrect
                         //Il n'y a pas de ; entre les champs comme c'est demandé lors du parametrage des exports
                         //Il faut donc les ajouter par programme avant le traitement de la ligne
                         $ligne=ltrim($ligne);
                         $ligne=str_replace(", N° licence : ",";",$ligne);
                         $ligne=str_replace(", matchs :",";",$ligne);
                         $enreg=explode(";",$ligne);// Converti la ligne lue en tableau. le point-virgule sert de separateur
                         if ($prem) {
                                  //on créé la partie invariable de la chaine sql d'insertion des nouveaux enregistrements
                                  $result=mysqli_query($connect,"show columns from joueurs");
                                  $sql="insert into joueurs (";
                                  while ($row=mysqli_fetch_assoc($result)){
                                      $sql.="`".addslashes($row["Field"])."`, ";
                                  
                                  }
                                  $sql=substr($sql,0,-2) .") VALUES (NULL, "; 
                                  $prem=false;
                          }                                                       
                          //pour chaque lignes lues on créé la partie variable de la commande sql
                          $sql_suite="";
                        foreach ($enreg as $e_enreg) {
                            $e_enreg=str_replace("matchs :","",$e_enreg); 
                            
                            //Pour mettre un 0 devant le 1er N° de match d'une liste ex 3,23,55 devient 03,23,55 =>tri par N° de match dans le tableau d'affichage
                            $pos_virg=strpos(trim($e_enreg),",");//Position de la premiere virgule dans le champ
                            if (!$pos_virg===false) { 
                              //S'il y a une , en position 1 alors la liste des N° de match débute par un N° compris entre 1 et 9
                              //Il faut le faire précéder d'un 0
                              if ($pos_virg==1) {$e_enreg="0".trim($e_enreg);}  
                            } else {
                                 // Il n'y a pas de , dans la liste 
                                 // si e_enreg contient  un N° de match, il est seul et s'il est compris entre 1 et 9 on le fait précéder d'un 0 
                                 // si e_enreg contient le N° de licence, celui-ci est >9
                                 // Si e_enreg contient le Nom du joueur, intval()) retourne 0
                                 if ((intval($e_enreg)<10) && intval($e_enreg>0)){                      
                                  $e_enreg="0".trim($e_enreg); //Si ce N° est entre 1 et 9 => on le fait précéder d'un 0
                                 }
                            }
                            $sql_suite.= "'".addslashes($e_enreg)."', ";
                           //echo $sql_suite.PHP_EOL;   
                        }
                        if ($sql_suite!='') {
                              $sql_suite.="'".addslashes($lieu)."', '".$date_liste."', NULL,".$num_titre.",'');";
                              //Execution de la commande
                              
                              $sql_suite=$sql.$sql_suite;
                              $sql_suite=str_replace("N° licence :","",$sql_suite);
                              mysqli_query($connect,$sql_suite) or die('Erreur SQL !<br>'.$sql_suite.'<br>'.mysqli_error());
                              $nbr_joueur++; //On incremente le nombre des joueurs importés
                         
                        }
                      }
                    }//ligne suivante  
                    //on memorise les informations nesessaires pour formuler la réponse
                    $tab_titre[]=array("num_titre"=>$num_titre,"titre"=>str_replace(chr(92),"",$lieu_date),"nbr_joueur"=>$nbr_joueur,"sans_licence"=>0);
                   
                    //Si on doit reprendre l'état des joueurs
                    //!! l'état d'un joueur sans licence n'est pas repositionné
                    if ($param_import==1) {
                        $sql= "UPDATE joueurs 
                               INNER JOIN tmp_joueurs ON Licences = tmp_licence                                                            
                               SET etat = tmp_etat ,commentaire = tmp_commentaire
                               WHERE num_titre=".$num_titre;
                        mysqli_query($connect,$sql);         
                    }
        }
  fclose($handle);
  //Suppression du fichier
  unlink($Directory."/".$e_fichier);
  
 
  }

}//fichier suivant
//Préparation du retour
$num=0;
$num_sans_licence=0;
$reponse=array();
$lst_titre="";
$i=0;
foreach ($tab_titre as $etab_titre) {
  if ($etab_titre["nbr_joueur"]>0) {
    
        //mise a jour des joueurs sans licence
        $sql = "UPDATE `joueurs` 
                SET `Licences`=round((rand()*100000),0) 
                WHERE Licences='' and num_titre = ".$etab_titre["num_titre"].";";
        mysqli_query($connect,$sql);
        $tab_titre[$i]["sans_licence"]= (mysqli_affected_rows($connect)>0) ? mysqli_affected_rows($connect):0;
  }
  $i++;
}  
  echo json_encode($tab_titre);//Retourne le nombre d'enreg integrés

