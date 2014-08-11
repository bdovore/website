<?
/******************************************************************************************************************
    //Nom : ImageFilledArcPerso
    //Fonction : �quivalent � ImageFilledArc sans utiliser de fonctions GD >= 4.0.6
    //Cr��e par : pgpp05@hotmail.com
    //Cr�� le : 30/03/2003
*******************************************************************************************************************
    //Vitesse : 1.5 fois plus de temps d'ex�cution que ImageFilledArc
    //Les param�tres sont les m�mes que ceux de ImageFilledArc � savoir :
    //   $im = image destination
    //   $cx,$cy = position du centre
    //   $w,$h = taille de l'ellipse
    //   $s,$e = angles de d�part et d'arriv�e (en degr�s)
    //   $col = couleur de remplissage
    //      Note : il n'y a pas de contours
    //Notes : Pour comprendre le fonctionnement, retirer le commentaire de DrawPng($im2)
    //        !!! ne JAMAIS soumettre un camembert � une seule tranche (� cause des d�calages de traits de construction)
    //        !!! ne JAMAIS avoir des parts de moins de 0.33 degr�s
    //        !!! �VITER d'avoir des parts de plus de 330 degr�s (petit bug au niveau du centre)
    //        Je recommande un �clatement lors de l'utilisation de cette fonction, pour �viter les bugs graphiques certes mineurs
    //        Pour la fonction camembert(), l'�clatement des tranches est 'r�el' donc pas tr�s �l�gant avec des tranches disproportionn�es
*******************************************************************************************************************/
define("DEG2RAD",M_PI/180);
function ImageFilledArcPerso($im,$cx,$cy,$w,$h,$s,$e,$col)
{
    //marge de travail pour la construction
    $cx+=20;$cy+=20;

    $im2=ImageCreate(ImageSX($im)+40,ImageSY($im)+40);
    $white=ImageColorAllocate($im2,255,255,255); //fond temporaite
    $black=ImageColorAllocate($im2,0,0,0); //traits de construction
    $grey=ImageColorAllocate($im2,127,127,127); //remplac� par la couleur � la fin

    //traits de construction
    ImageLine($im2,Cos(($s)*DEG2RAD)*($w/2+15)+$cx,Sin(($s)*DEG2RAD)*($h/2+15)+$cy,$cx-(int)(Cos($s*DEG2RAD+M_PI/2)*1.8),$cy-(int)(Sin($s*DEG2RAD+M_PI/2)*1.8),$black);
    ImageLine($im2,Cos(($e)*DEG2RAD)*($w/2+15)+$cx,Sin(($e)*DEG2RAD)*($h/2+15)+$cy,$cx+(int)(Cos($e*DEG2RAD+M_PI/2)*1.8),$cy+(int)(Sin($e*DEG2RAD+M_PI/2)*1.8),$black);
    ImageArc($im2,$cx,$cy,$w+2,$h+2,($s-6)<0?($s):($s-6),$e+10,$black);
    ImageSetPixel($im2,$cx,$cy,$grey); //pour finir la pointe correctement

    //zone voulue en gris et le reste en noir
    ImageFill($im2,Cos(($s+$e)/2*DEG2RAD)*($w/2.2)+$cx,Sin(($s+$e)/2*DEG2RAD)*($h/2.2)+$cy,$grey); //remplit la zone voulue en gris
  //  ImageArc($im2,$cx,$cy,$w+2,$h+2,$e+.1,$s,$white); //corrige le bug de remplissage total impossible au del� de 330�

    //retirer le commentaire suivant pour voir le fonctionnement
    //DrawPNG($im2);

    ImageFill($im2,Cos(($s+$e)/2*DEG2RAD-M_PI)*($w/4)+$cx,Sin(($s+$e)/2*DEG2RAD-M_PI)*($h/4)+$cy,$black); //remplit tout le reste en noir
    ImageColorTransparent($im2,$black);                                          //rend le noir transparent

    //zone voulue en $col
    $rvb=ImageColorsForIndex($im,$col);                               //remplace la couleur grise...
    ImageColorSet($im2,$grey,$rvb["red"],$rvb["green"],$rvb["blue"]); //...par la couleur $col
    ImageColorDeAllocate($im2,$white); //corrige le bug du pixel blanc au centre au del� de 330�...
    ImageColorAllocate($im2,$rvb["red"],$rvb["green"],$rvb["blue"]); //...le mettre en $col

    ImageCopy($im,$im2,0,0,20,20,ImageSX($im)+40,ImageSY($im))+40;
    ImageDestroy($im2);
   // DrawPNG($im2);
}