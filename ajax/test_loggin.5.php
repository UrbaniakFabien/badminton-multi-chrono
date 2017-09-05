<?php
session_start();
// on inclu la page de config
include ("../loginconfig.5.php");

if($_POST && !empty($_POST['login']) && !empty($_POST['mdp']))
{
    // on crypt le mot de passe envoyer par le formulaire
    $password_md5 = md5($_POST['mdp'].$salt);
    // test si administrateur
    if((strtoupper($_admin_login) == strtoupper($_POST['login'])) && ($password_md5 == $_admin_pass))
    {
        $_SESSION['_login'] = $_admin_login;
        $_SESSION['_pass'] = $password_md5;
        $_SESSION['_niveau']="admin";
        echo  "index.php";
        exit();
    }
    //test si utilisateur
    if((strtoupper($_user_login) == strtoupper($_POST['login'])) && ($password_md5 == $_user_pass))
    {
        $_SESSION['_login'] = $_user_login;
        $_SESSION['_pass'] = $password_md5;
        $_SESSION['_niveau']="user";
        echo  "index.php";
        exit();
    } 
}
echo "";

?>