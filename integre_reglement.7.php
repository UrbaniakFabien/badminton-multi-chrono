<?php
/********************************************************************************
 *  Module d'integration du fichier des réglements
 * FUR
 * 12/2017
 *
 * Le fichier est un export des convocations en mode csv
 * Il n'y a aucune information qui relie cet extraction à un tournoi
 * Par défaut on considére que c'est le fichier des régmenet du tournoi en cours
 * TODO
 *   Proposer la liste des dates de la base pour séléctionner les dates auxquelles rattacher ce fichier ??
 *******************************************************************************/
session_start();
?>
<!DOCTYPE HTML>
<!--
/*Basé sur 
 * jQuery File Upload Plugin Demo 9.0.0
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
-->
<html lang="fr">
<head>
<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<meta charset="utf-8">
<title>Import réglements</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap styles -->
<link rel="stylesheet" href="jquery/jQuery-File-Upload/css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="jquery/jQuery-File-Upload/css/style.css">
  <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="jquery/jQuery-File-Upload/css/jquery.fileupload.css">
<link rel="stylesheet" href="jquery/jQuery-File-Upload/css/jquery.fileupload-ui.css">

<link rel="stylesheet" type="text/css" href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css"  /> 

<style type="text/css">
icon-import {
    background-position: -265px -70px;
}
</style>
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="jquery/jQuery-File-Upload/css/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="jquery/jQuery-File-Upload/css/jquery.fileupload-ui-noscript.css"></noscript>

<script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  
$("#frm_msg").dialog({ title:'Message',
                                      width:  450,
                                      height: 300,
                                      modal: true,
                                      autoOpen: false,
                                      resizable:false,
                                      buttons: [{
                                          text:"OK",
                                          'click': function() {  
                                                                $( this ).dialog( "close" );     
                                                              },       
                                           
                                          icons: { primary: 'ui-icon-check' }}]
                                  }); 
                                  

                /***************************************************************
                 * Menu général
                 * *************************************************************/                                 
                $("#menuprinc").menu({ position: { using: positionnerSousMenu} });
});
/************************************************************
 * Fonction d'appel à la procédure d'integration des données
 ************************************************************/
  function Import() {
  //fonction d'import des fichiers uploadés
      $.post('jquery/jQuery-File-Upload/integre_reglements.7.php',
              function(data) {
                              ligne="<table class='table table-striped'><thead><tr><th>Titre</th><th>Joueurs import&eacute;s</th><th>Joueurs sans licences</th></tr></thead><tbody>";
                              for (i=0;i<=data.length-1;i++) {
                                  ligne=ligne + "<tr><td>" + data[i].titre + "</td><td>" + data[i].nbr_joueur + "</td><td>" + data[i].sans_licence + "</td></tr>";
                              }
                              ligne=ligne + "</tbody></table>";
                              $("#tab_cr").html(ligne); 
                              $("#frm_msg").dialog("open");
                              $("#msg").html("");   //Pour supprimer le message
                              $(".files").html(""); //pour supprimer la liste des fichiers
                              },
              'json');
   return false
}

      
</script>
</head>
<body>
<?php include ("menu.5.1.php");?>
<div class="container">
    <div class="page-header">
        <h1>T&eacute;l&eacute;chargement Réglements joueurs</h1>
    </div>
    <br />
    <span id="msg"></span>
    <br />
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" action="jquery/jQuery-File-Upload/server/php/" method="POST" enctype="multipart/form-data">
              
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Ajouter fichier...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Télécharger</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Annule</span>
                </button>
                 <button type='button' class="btn btn-success" onclick='Import();'>
                    <i class="glyphicon glyphicon-download-alt"></i>
                    <span>Import</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Efface</span>
                </button>
                <input type="checkbox" class="toggle">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
    <br>
    
   </div>

<!--Formulaire pour message de compte rendu d'integration -->
<form id="frm_msg">
    <div id="tab_cr"></div> 
</form>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">En cours...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Télécharge</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Annule</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Efface</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Annule</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>

<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="jquery/jQuery-File-Upload/js/load-image.min.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="jquery/jQuery-File-Upload/js/tmpl.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="jquery/jQuery-File-Upload/js/bootstrap.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="jquery/jQuery-File-Upload/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="jquery/jQuery-File-Upload/js/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<script src="jquery/jQuery-File-Upload/js/main.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="js/cors/jquery.xdr-transport.js"></script>
<![endif]-->


</body> 
</html>
