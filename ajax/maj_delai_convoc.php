<?php

/* 
 *Mise à jour du delai de convocation depuis liste de pointage
 * FUR
 * 03/2019
 */

include "../connect.7.php";
$message = "ok";
foreach ($_POST as $key=>$value) {
    list($filler,$num) =explode("_",$key);
    list($h,$m)=explode("h",$value);
    if (($h != "") && ($m!="")) {
    $sql= "UPDATE titre
           SET decalage_horaire_convocation = '$value'
           WHERE num_titre = $num";
    mysqli_query($connect,$sql);
    } else {
        $message = "Format invalide !";
    }
}
echo json_encode(["message"=>$message]);

