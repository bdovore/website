<?php



class FicheAlbum

{



    public function small ($o_tome, $getUrlSerie = true)

    {

        if (is_array($o_tome)) {

            $o_tome = (object) $o_tome;

        }



        $html = '

<div class="cadre1">

	<div style="float:left" class="mw50">

		'. $this->urlAlbum($o_tome, 'couvSmall').'

	</div>

    <div style="float:left" class="mw50">

	<table>';

  

    

    // titre de l'album

	if ($o_tome->TITRE_TOME) {

	    $html .= '<tr><td class="fiche_h2r">Titre : </td><td class="fiche_h2l">';

		if ($o_tome->NUM_TOME AND !(stristr($o_tome->TITRE_TOME,$o_tome->NUM_TOME))) {

    	        $html .= '<i>tome '.$o_tome->NUM_TOME.' -</i> ';

		}

	    $html .=$this->urlAlbum($o_tome, 'albTitle').'</td></tr>';

	}
// nom de la serie

	if ($o_tome->NOM_SERIE 

	        AND ($o_tome->TITRE_TOME AND (strtolower($o_tome->NOM_SERIE)!=strtolower($o_tome->TITRE_TOME)) AND $getUrlSerie)) {

	    $html .= '<tr><td class="fiche_h2r">Série : </td><td class="fiche_h2l">'.$this->urlSerie($o_tome).'</td></tr>';

	}
	// genre

	if ($o_tome->NOM_GENRE) {

	    $html .= '<tr><td class="fiche_h3r">Genre : </td><td class="fiche_h3l">';

	    $html .= '<i>'.$o_tome->NOM_GENRE.'</i></td></tr>';

	}

	// editeur

	if ($o_tome->NOM_EDITEUR) {

	    $html .= '<tr><td class="fiche_h3r">Editeur : </td><td class="fiche_h3l">';

	    $html .= '<i>'.$o_tome->NOM_EDITEUR.'</i></td></tr>';

	}

	

	// collection editeur

	if (($o_tome->NOM_COLLECTION) and  ($o_tome->NOM_COLLECTION!= '<N/A>')) {

	    $html .= '<tr><td class="fiche_h3r">Collection : </td><td class="fiche_h3l">';

	    $html .= '<i>'.$o_tome->NOM_COLLECTION.'</i></td></tr>';

	}

	

	$html .= '</table>

	</div>

	<hr class="expSep">

</div>';

        return $html;

    }



    public function medium ($o_tome,$getUrlSerie = true)

    {

        if (is_array($o_tome)) {

            $o_tome = (object) $o_tome;

        }

        $html = '

<div class="cadre1">

	<div style="float:left">

		'. $this->urlAlbum($o_tome, 'couvMedium').'

	</div>

    <div style="float:left">

	<table>';

        

       

        // titre de l'album

    	if ($o_tome->TITRE_TOME) {

    	    $html .= '<tr><td>Titre : </td><td>';

    		if ($o_tome->NUM_TOME AND !(stristr($o_tome->TITRE_TOME,$o_tome->NUM_TOME))) {

    	        $html .= '<i>tome '.$o_tome->NUM_TOME.' -</i> ';

    		}

    	    $html .=$this->urlAlbum($o_tome, 'albTitle').'</td></tr>';

    	}

         // nom de la serie

        if ($o_tome->NOM_SERIE

                AND ($o_tome->TITRE_TOME AND (strtolower($o_tome->NOM_SERIE)!=strtolower($o_tome->TITRE_TOME)) AND $getUrlSerie)) {

            $html .= '<tr><td>Série : </td><td>'.$this->urlSerie($o_tome).'</td></tr>';

        }

        $html .= '</table>

	</div>

	<hr class="expSep">

</div>';

        return $html;

    }



    public function big ($o_tome,$sep=true,$getUrlSerie = true)

    {

        if (is_array($o_tome)) {

            $o_tome = (object) $o_tome;

        }

        $html = '

<div class="cadre1">

	<div style="float:left" class="mw50">

		'. $this->urlAlbum($o_tome, 'couvBig').'

	</div>

    <div style="float:left" >

	<table>';

        

       

        // titre de l'album

    	if ($o_tome->TITRE_TOME) {

    	    $html .= '<tr><td class="fiche_h1r">Titre : </td><td class="fiche_h1l">';

    		if ($o_tome->NUM_TOME AND !(stristr($o_tome->TITRE_TOME,$o_tome->NUM_TOME))) {

    	        $html .= '<i>tome '.$o_tome->NUM_TOME.' - </i> ';

    	    }

    	    $html .=$this->urlAlbum($o_tome, 'albTitle').'</td></tr>';

    	}
 // nom de la serie

        if ($o_tome->NOM_SERIE

                AND ($o_tome->TITRE_TOME AND (strtolower($o_tome->NOM_SERIE)!=strtolower($o_tome->TITRE_TOME)) AND $getUrlSerie)) {

            $html .= '<tr><td class="fiche_h1r">Série : </td><td class="fiche_h1l">'.$this->urlSerie($o_tome).'</td></tr>';

        }
    	// genre

    	if ($o_tome->NOM_GENRE) {

    	    $html .= '<tr><td class="fiche_h2r">Genre : </td><td class="fiche_h2l">';

    	    $html .= '<i>'.$o_tome->NOM_GENRE.'</i></td></tr>';

    	}



    	// editeur

    	if ($o_tome->NOM_EDITEUR) {

    	    $html .= '<tr><td class="fiche_h2r">Editeur : </td><td class="fiche_h2l">';

    	    $html .= '<i>'.$o_tome->NOM_EDITEUR.'</i></td></tr>';

    	}



    	// collection editeur

    	if (($o_tome->NOM_COLLECTION) and  ($o_tome->NOM_COLLECTION!= '<N/A>')) {

    	    $html .= '<tr><td class="fiche_h2r">Collection : </td><td class="fiche_h2l">';

    	    $html .= '<i>'.$o_tome->NOM_COLLECTION.'</i></td></tr>';

    	}



    	// date de parution

    	if ($o_tome->DATE_PARUTION_EDITION) {

    	    $html .= '<tr><td class="fiche_h2r">Date parution : </td><td class="fiche_h2l">';

    	    $html .=$this->dateParution($o_tome->DATE_PARUTION_EDITION).'</td></tr>';

    	}



    	// ISBN

    	if ($o_tome->ISBN_EDITION or $o_tome->EAN_EDITION) {

    	    $html .= '<tr><td class="fiche_h3r">Référence(s) : </td><td class="fiche_h3l">';

    	    if ($o_tome->ISBN_EDITION) $html .= ' ISBN-'.$o_tome->ISBN_EDITION;

    	    if ($o_tome->EAN_EDITION) $html .= ' EAN-'.$o_tome->EAN_EDITION;

    	    $html .= '</td></tr>';

    	}



    	// note/votes

    	if ($o_tome->NB_NOTE_TOME) {

    	    $html .= '<tr><td class="fiche_h2l" colspan="2">Album noté '.$o_tome->MOYENNE_NOTE_TOME.'/10 pour '.$o_tome->NB_NOTE_TOME.' votes</td><td>';

    	}

    	 



    	// nb_user tome

    	if (!is_null($o_tome->NBR_USER_ID_TOME)) {

    	    $html .= '<tr><td class="fiche_h2l" colspan="2">'.$o_tome->NBR_USER_ID_TOME.' utilisateurs possèdent cet album</td><td>';

    	}

    	 
        if (Bdo_Cfg::user()->minAccessLevel(2)) {
            // ajout des liens d'ajout dans la collection
            $html .= '<tr><td class="fiche_h2r" colspan="2">'.$this->linkCollection($o_tome). '</td></tr>';
           
        }
    	 

    	$html .= '</table>

	</div>';
        

        if ($sep){
            $html .= '<hr class="expSep">';
        }
       $html .= '
        </div>';

        return $html;

    }



    public function serie ($o_serie)

    {

        if (is_array($o_serie)) {

            $o_serie = (object) $o_serie;

        }

        $html = '

<div class="cadre1">
    <img src="'. BDO_URL_COUV.$o_serie->IMG_COUV_SERIE.'" class="couvSmall" style="float:left;margin=2px">
	<div><table>

		<tr><td class="fiche_h2l">'. $this->urlSerie($o_serie).'</td></tr>';

            // genre

        if ($o_serie->NOM_GENRE) {

            $html .= '<tr><td class="fiche_h3l">Genre : <i>'.$o_serie->NOM_GENRE.'</i></td></tr>';

        }

        //avancement 0: Finie, 1: En cours, 2: One-shot, 3:Interrompue

        

        if (!is_null($o_serie->FLG_FINI_SERIE)) {

            $a_avancement = array(

                 0=> 'Finie',

                 1=> 'En cours',

                 2=> 'One-shot',

                 3=> 'Interrompue',

                 );



            $html .= '<tr><td class="fiche_h3l">Avancement : '.$a_avancement[$o_serie->FLG_FINI_SERIE].'</td></tr>';

        }

        

        

        

        $html .= '</table>';



	$html .= '</div><hr class="expSep" />
            
</div>';

        return $html;

    }

    

    /*

     * urlAlbum

    * fournit l'url d'un lien vers la iframe album

    *

    */

    public function urlAlbum ($o_tome, $class = 'couvBig',$is_edition = false)
            /*
             *  Fonction de construction d'une url d'un album
             * Si la variable $is_edition = true, on ajoute l'id édition dans les liens
             *  Utile pour l'accès depuis la collection de l'utilisateur, où on sélectionne une édition précise
             */
    {

        if (is_array($o_tome)) {

            $o_tome = (object) $o_tome;

        }

        $id_link = "Album?id_tome=" . $o_tome->ID_TOME;
        if ($is_edition) $id_link.="&id_edition=".$o_tome->ID_EDITION;

        // couverture par defaut

        if (!$o_tome->IMG_COUV) $o_tome->IMG_COUV="default.png";

        

        $x = getenv("HTTP_USER_AGENT");

        if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {

            return '#" onclick="window.open(' . "'" . BDO_URL . $id_link . "','Album','width=600,height=600,scrollbars=1')" . ';return false;';

        }

        else {



            // titre html

            if ($o_tome->NOM_SERIE AND (strtolower($o_tome->NOM_SERIE)!=strtolower($o_tome->TITRE_TOME))) {

                $titleHtml = $o_tome->NOM_SERIE;

            	if ($o_tome->NUM_TOME AND !(stristr($o_tome->TITRE_TOME,$o_tome->NUM_TOME))) {

        	        $titleHtml .= ' - '. 'tome '.$o_tome->NUM_TOME;

        	    }

    	        $titleHtml .= ' - '.$o_tome->TITRE_TOME;

                }

            else {

                $titleHtml = $o_tome->TITRE_TOME;

                

            }

            

            $html = '<a class="fancybox fancybox.iframe {width:600,height:400}"

                href="' . BDO_URL . $id_link . '" title="' .$titleHtml . '">';

            switch ($class) {

                case "couvBig":

                    {

                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' .$titleHtml. '"/>';

                        $html .= '</a>'.($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; '.$o_tome->NOM_EDITEUR.'</div>' : '');

                        break;

                    }

                case "couvMedium":

                    {

                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' .$titleHtml. '"/>';

                        $html .= '</a>'.($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; '.$o_tome->NOM_EDITEUR.'</div>' : '');

                        break;

                    }

                case "couvSmall":

                    {

                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' .$titleHtml . '"/>';

                        $html .= '</a>';

                        break;

                    }

                case "albTitle":

                    {

                        $html .= $o_tome->TITRE_TOME . '</a>';

            if (Bdo_Cfg::user()->minAccessLevel(1)){

                $html .= '&nbsp;&nbsp;<a href="' . '#' . '" ><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';

            }

                        break;

                    }

                default:

                    {

                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" title="' .$titleHtml . '"/>';

                        $html .= '</a>'.($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; '.$o_tome->NOM_EDITEUR.'</div>' : '');

                        break;

                    }

            }
          
            return $html;

        }

    }

    

    

    public function urlSerie ($o_serie)

    {

        if (is_array($o_serie)) {

            $o_serie = (object) $o_serie;

        }

        $html = '<a href="' . BDO_URL . 'SerieBD?id_serie=' . $o_serie->ID_SERIE . '" title="' . $o_serie->NOM_SERIE . '">

           ' . $o_serie->NOM_SERIE . '</a>';

        if (Bdo_Cfg::user()->minAccessLevel(1)){

            $html .= '&nbsp;&nbsp;<a href="' . '#' . '"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';

        }

        return $html;

    }

    public function linkCollection($o_tome) {
     // fonction pour ajouter les liens d'ajouts dans la collection
        $html = "" ;    
        
         if (User::minAccesslevel(2)) {
             $id_source = "addAlbum".$o_tome->ID_EDITION; 
             $html = "<div id='".$id_source."'>";
            if ($o_tome->DATE_AJOUT) {
                 if ($o_tome->FLG_ACHAT == "O") {
                     $html.= "ajouté à vos futurs achats le ".date_format(date_create($o_tome->DATE_AJOUT),"d/m/Y");
                 } else {
                     $html.= "ajouté à votre collection le ".date_format(date_create($o_tome->DATE_AJOUT),"d/m/Y");
                 }
                
                 $html .= " - <a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' title='Supprimer l'édition de ma collection' onclick='deleteEdition(".$o_tome->ID_EDITION.")'>Supprimer</a>";
              }
            else {
                
                $html .= "<a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' href='javascript:addAlbum(".$o_tome->ID_TOME.",".$o_tome->ID_EDITION.',"N")'."'".'>Ajouter à ma collection</a>';
                $html .= " - <a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' href='javascript:addAlbum(".$o_tome->ID_TOME.",".$o_tome->ID_EDITION.',"O")'."'".'>Futur Achat</a>';
               

            }
             $html.= "</div>";
         }
         else {
             $html="<i>Connectez vous pour ajouter cet album dans votre collection !</i>";
            }
        return $html;
    }
    
    public function getSponsor($o_tome){
        $html = "<a href='";
        if ($o_tome->ISBN_EDITION) {
            $html .= "http://www.amazon.fr/exec/obidos/ASIN/".$o_tome->ISBN_EDITION."/bdovorecom-21/";
        } 
        else if ($o_tome->EAN_EDITION) {
            $html .= "http://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=".$o_tome->EAN_EDITION."&mode=books-fr";
        }
        else {
            $html .= "http://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=".htmlspecialchars(stripslashes($o_tome->TITRE_TOME))."&mode=books-fr";
            
        }
        $html .="' target='_blank'><img src='".BDO_URL_IMAGE."amazon.gif'></a>";
        return $html;
    }
    public function dateParution ($date, $flag = 0)

    {

        if (! empty($date)) {

            // date de parution superieur a date du jour moins 1 mois

            if (TimestampDate($date) > (time() - 2592000)) {

                $dte_parution = translate_date($date);

            }

            else {

                $dte_parution = month_to_text(substr($date, 5, 2)) . " " . substr($date, 0, 4);

            }

        }

        else {

            if ($flag == 1) $dte_parution = "Date introuvable";

            else

                $dte_parution = "Champ 'date' non-rempli";

        }

        return $dte_parution;

    }

}
