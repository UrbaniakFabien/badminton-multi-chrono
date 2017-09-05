<?php
    $dossier = opendir ('.');
    
    while ($fichierAEncoder = readdir ($dossier))
    {
        if (is_file ($fichierAEncoder))
        {
			$lg=strlen($fichierAEncoder);
			$deb=$lg-3;
			$ext=substr($fichierAEncoder,$deb,3);
            if ($ext=='php')
			{
				echo "fichier $fichierAEncoder\n";
				$contenu = file_get_contents ($fichierAEncoder);
				$fichier = fopen ($fichierAEncoder, 'w');
				fputs ($fichier, utf8_encode ($contenu));
				fclose ($fichier);
			}
        }
    }
    
    closedir ($dossier);
?>
