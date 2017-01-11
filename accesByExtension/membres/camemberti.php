<?php
//require("Ellipses.php");

/*
$freq=array(15,18,8,10,7,2,1);
$im=camembert($freq,0,800,400,15,10,1);
drawPNG($im);
*/
//$freq = table des valeurs (1 dimension)
//$ord = ordre [1=croissant | 2=décroissant]
//$rm,$rn = rayon max,min de l'ellipse
//$h = épaisseur
//$d1 = décalage de la 1ere tranche (en px)
//$dt = [1=décale ttes les tranches de $d1 px]
// $lib : libellés
// $ legW : width de la légende

/******************************************************************************************************************
    //Nom : ImageFilledArcPerso
    //Fonction : équivalent à ImageFilledArc sans utiliser de fonctions GD >= 4.0.6
    //Créée par : pgpp05@hotmail.com
    //Créé le : 30/03/2003
*******************************************************************************************************************
    //Vitesse : 1.5 fois plus de temps d'exécution que ImageFilledArc
    //Les paramètres sont les mêmes que ceux de ImageFilledArc à savoir :
    //   $im = image destination
    //   $cx,$cy = position du centre
    //   $w,$h = taille de l'ellipse
    //   $s,$e = angles de départ et d'arrivée (en degrés)
    //   $col = couleur de remplissage
    //      Note : il n'y a pas de contours
    //Notes : Pour comprendre le fonctionnement, retirer le commentaire de DrawPng($im2)
    //        !!! ne JAMAIS soumettre un camembert à une seule tranche (à cause des décalages de traits de construction)
    //        !!! ne JAMAIS avoir des parts de moins de 0.33 degrés
    //        !!! ÉVITER d'avoir des parts de plus de 330 degrés (petit bug au niveau du centre)
    //        Je recommande un éclatement lors de l'utilisation de cette fonction, pour éviter les bugs graphiques certes mineurs
    //        Pour la fonction camembert(), l'éclatement des tranches est 'réel' donc pas très élégant avec des tranches disproportionnées
*******************************************************************************************************************/
define("DEG2RAD",M_PI/180);
function ImageFilledArcPerso($im,$cx,$cy,$w,$h,$s,$e,$col)
{
    //marge de travail pour la construction
    $cx+=20;$cy+=20;

    $im2=ImageCreate(ImageSX($im)+40,ImageSY($im)+40);
    $white=ImageColorAllocate($im2,255,255,255); //fond temporaite
    $black=ImageColorAllocate($im2,0,0,0); //traits de construction
    $grey=ImageColorAllocate($im2,127,127,127); //remplacé par la couleur à la fin

    //traits de construction
    ImageLine($im2,Cos(($s)*DEG2RAD)*($w/2+15)+$cx,Sin(($s)*DEG2RAD)*($h/2+15)+$cy,$cx-(int)(Cos($s*DEG2RAD+M_PI/2)*1.8),$cy-(int)(Sin($s*DEG2RAD+M_PI/2)*1.8),$black);
    ImageLine($im2,Cos(($e)*DEG2RAD)*($w/2+15)+$cx,Sin(($e)*DEG2RAD)*($h/2+15)+$cy,$cx+(int)(Cos($e*DEG2RAD+M_PI/2)*1.8),$cy+(int)(Sin($e*DEG2RAD+M_PI/2)*1.8),$black);
    ImageArc($im2,$cx,$cy,$w+2,$h+2,($s-6)<0?($s):($s-6),$e+10,$black);
    ImageSetPixel($im2,$cx,$cy,$grey); //pour finir la pointe correctement

    //zone voulue en gris et le reste en noir
    ImageFill($im2,Cos(($s+$e)/2*DEG2RAD)*($w/2.2)+$cx,Sin(($s+$e)/2*DEG2RAD)*($h/2.2)+$cy,$grey); //remplit la zone voulue en gris
  //  ImageArc($im2,$cx,$cy,$w+2,$h+2,$e+.1,$s,$white); //corrige le bug de remplissage total impossible au delà de 330°

    //retirer le commentaire suivant pour voir le fonctionnement
    //DrawPNG($im2);

    ImageFill($im2,Cos(($s+$e)/2*DEG2RAD-M_PI)*($w/4)+$cx,Sin(($s+$e)/2*DEG2RAD-M_PI)*($h/4)+$cy,$black); //remplit tout le reste en noir
    ImageColorTransparent($im2,$black);                                          //rend le noir transparent

    //zone voulue en $col
    $rvb=ImageColorsForIndex($im,$col);                               //remplace la couleur grise...
    ImageColorSet($im2,$grey,$rvb["red"],$rvb["green"],$rvb["blue"]); //...par la couleur $col
    ImageColorDeAllocate($im2,$white); //corrige le bug du pixel blanc au centre au delà de 330°...
    ImageColorAllocate($im2,$rvb["red"],$rvb["green"],$rvb["blue"]); //...le mettre en $col

    ImageCopy($im,$im2,0,0,20,20,ImageSX($im)+40,ImageSY($im))+40;
    ImageDestroy($im2);
   // DrawPNG($im2);
}
function camembert($freq,$ord,$rm,$rn,$h,$d1,$dt,$lib,$legw)
{
    if($ord==1) sort($freq);
    if($ord==2) rsort($freq);
    $nb=count($freq);
    $somme=array_sum($freq);
    $wd = intval($rm+$d1*$dt*2+$d1*2+8+$legw);
    $hg = intval($rn+$d1*$dt*2+$h*3+8);
    $im=ImageCreate($wd,$hg); //3D
    // cadre pour la légende
    $white=ImageColorAllocate($im,255,255,255);
    $black = ImageColorAllocate($im,0,0,0);
    ImageColorTransparent($im,$white);
    /*

    */
    $pal=DoubleColorSet($im);
    $im2=ImageCreate(intval($rm+$d1*$dt*2+$d1*2+8),intval($rn+$d1*$dt*2+$h*3+8)); //dessus
    $white=ImageColorAllocate($im2,255,255,255);
    ImageColorTransparent($im2,$white);
    DoubleColorSet($im2);
    $xleg = $wd - $legw + 2;
    $legy = 15;
    for($last=0,$i=0;$i<$nb;$i++)
    {
        $degree=360*($freq[$i]/$somme);
        $col=$pal[(192/($nb+1))*$i];
        /* ImageFilledArc($im,(ImageSX($im)/2)+($i==0|$dt)*$d1*cos(($last+$degree/2)/360*2*M_PI),
        (ImageSY($im)/2)+($i==0|$dt)*$d1*sin(($last+$degree/2)/360*2*M_PI)
        *($rn/$rm*1.5),
        $rm,$rn,$last,$last+$degree,$col,IMG_ARC_NOFILL&IMG_ARC_EDGED);  */
        $cx = ((ImageSX($im)-$legw)/2)+($i==0|$dt)*$d1*cos(($last+$degree/2)/360*2*M_PI);
        $cy = (ImageSY($im)/2)+($i==0|$dt)*$d1*sin(($last+$degree/2)/360*2*M_PI)*($rn/$rm*1.5);
        $e = $last+$degree;
        ImageFilledArcPerso($im,$cx,$cy,$rm,$rn,$last,$e,$col);
        $last+=$degree;


    }
    ImageCopy($im2,$im,0,0,0,0,ImageSX($im),ImageSY($im));
    ImageColorMod($im2,64);
    for($i=0;$i<$h;$i++) //effet 3D en recopiant l'image h fois sur elle même
    ImageCopy($im,$im,0,0,0,1,ImageSX($im),ImageSY($im));
    ImageCopy($im,$im2,0,0,0,$h,ImageSX($im),ImageSY($im));
    ImageDestroy($im2);
    // ajout de la legende
    imagerectangle($im,
    1, 1,
    $wd-1, $hg-1,
    $black);
    for($last=0,$i=0;$i<$nb;$i++)
    {
        $col=$pal[(192/($nb+1))*$i];
        imagestring($im,2,$xleg,$legy,$lib[$i]." : ".$freq[$i],$col);
        $legy += 12;
    }
    return($im);
}

function ImageColorMod($im,$mod) //décale les couleurs d'une palette de $mod composantes r v b
{
    $nb=ImageColorsTotal($im);
    for($i=1;$i<$nb;$i++) //ne prend pas la 1e couleur (généralement le fond en transparent)
    {
        $rvb=ImageColorsForIndex($im,$i);
        if(($mod+$rvb['red'])>255) $rvb['red']=255-$mod;
        if(($mod+$rvb['green'])>255) $rvb['green']=255-$mod;
        if(($mod+$rvb['blue'])>255) $rvb['blue']=255-$mod;
        if(($mod+$rvb['red'])<0) $rvb['red']=-$mod;
        if(($mod+$rvb['green'])<0) $rvb['green']=-$mod;
        if(($mod+$rvb['blue'])<0) $rvb['blue']=-$mod;
        ImageColorSet($im,$i,$mod+$rvb['red'],$mod+$rvb['green'],$mod+$rvb['blue']);
    }
}

function DoubleColorSet($im) //192 couleurs
{
    for($i=0;$i<64;$i++)
    {
        $pal[$i]=ImageColorAllocate($im,230-$i*4,$i*3,64 + $i*3);
        $pal[$i+64]=ImageColorAllocate($im,230-$i*4,192-$i*3,$i*4);
        $pal[$i+64*2]=ImageColorAllocate($im,255-$i*4,0,$i*4);
    }
    return($pal);
}

function drawPNG($im){
    static $n;$n++;imagePNG($im,"tmp$n.png");echo"<img src=\"tmp$n.png\">";
}

