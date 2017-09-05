<?php

/* * *****************************************************************************
 * Modifications apport�es
 * Impression d'une image dans une colonne
 * Application d'une fonte diff�rente par colonne
 * Auteur FU
 * Date Novembre 2012  
 * ***************************************************************************** */
require_once('fpdf.php');

class PDF_MC_Table extends FPDF {

    var $widths;
    var $aligns;
    var $styles;
    var $fonds;
    var $text_color;

    function SetWidths($w) {
        //Tableau des largeurs de colonnes
        $this->widths = $w;
    }

    function SetAligns($a) {
        //Tableau des alignements de colonnes
        $this->aligns = $a;
    }

    function SetFonds($a) {
        //Tableau des arriere plan des cellules
        $this->fonds = $a;
    }

    function SetStyles($s) {
        //Tableau des styles de colonnes
        $this->styles = $s;
    }

    function SetTextColors($s) {
        //Tableau des couleurs de texte
        $this->textcolor = $s;
    }

    function Row($data) {
        //Calcule la hauteur de la ligne
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            //Si la source est une image alors on considere sa taille
            if (strpos($data[$i], "src_img") === false) {
                $s = isset($this->styles[$i]) ? $this->styles[$i] : array('', '', 0);
                $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i], $s));
            } else {
                $tab = explode(":", $data[$i]); //Si image alors format $data[$i]="src_img:chemin/image.jpg:taille"
                //$nb=max($nb,$tab[2]);
            }
        } 
        $h = 5 * $nb;
        //Effectue un saut de page si n�cessaire
        $this->CheckPageBreak($h);
        //Dessine les cellules
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Sauve la position courante
            $x = $this->GetX();
            $y = $this->GetY();
            $fond = "D";
            if (isset($this->fonds[$i])) {
                $this->SetFillColor($this->fonds[$i]["r"], $this->fonds[$i]["g"], $this->fonds[$i]["b"]);
                $fond = "DF";
            }
            //Dessine le cadre
            $this->Rect($x, $y, $w, $h, $fond);
            if (strpos($data[$i], "src_img") === false) {
                //Imprime le texte
                if (isset($this->styles[$i])) {
                    $this->SetFont($this->styles[$i][0], $this->styles[$i][1], $this->styles[$i][2]);
                }
                if (isset($this->textcolor[$i])) {
                    $this->SetTextColor($this->textcolor[$i]["r"], $this->textcolor[$i]["g"], $this->textcolor[$i]["b"]);
                }
                //Pour centrer le texte dans le rectangle trac�
                $s = isset($this->styles[$i]) ? $this->styles[$i] : array('', '', 0);
                $hl = $this->NbLines($this->widths[$i], $data[$i], $s) * 5;
                $this->SetXY($x, $y + (($h - $hl) / 2));
                /*                 * ************************************************** */
                $this->MultiCell($w, 5, $data[$i], 0, $a);
                $this->SetTextColor(0);
            } else {

                $this->Image($tab[1], $x + ($w / 2) - ($tab[2] / 2), $y + ($h / 2) - ($tab[2] / 2), $tab[2]); //L'image est centr�e dans le rectangle
            }
            //Repositionne � droite
            $this->SetXY($x + $w, $y);
        }
        //Va � la ligne
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        //Si la hauteur h provoque un d�bordement, saut de page manuel
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation, $this->CurPageSize);
    }

    function NbLines($w, $txt, $s) {
        //Calcule le nombre de lignes qu'occupe un MultiCell de largeur w
        //Applique si il existe une definition de font pour la colonne
        if ($s[2] != 0) {
            $this->SetFont($s[0], $s[1], $s[2]);
        }

        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    /* En-t�te
      function Header()
      {

      // Logo
      //$this->Image('logo.png',10,6,30);
      // Police Arial gras 15
      $this->SetFont('Arial','B',15);
      // D�calage � droite
      $this->Cell(80);
      // Titre
      $this->Cell(30,10,'Titre',1,0,'C');
      // Saut de ligne
      $this->Ln(20);

      } */

// Pied de page
    function Footer() {

        // Positionnement � 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        // Num�ro de page
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-15);
        $this->Cell(0, 10, utf8_decode("Imprimé le " . date("d/m/Y") . " à " . date("H:i")), 0, 0, "L");
    }

}


