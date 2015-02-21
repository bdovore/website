<?php

class FicheAlbum {
    
    public function getTitreTome ($o_tome,$url=true) {
        $html = "";
        if ($o_tome->TITRE_TOME) {
            if ($o_tome->NUM_TOME AND stripos($o_tome->TITRE_TOME, "n°".$o_tome->NUM_TOME) === false) {
            //if ($o_tome->NUM_TOME) {
                $html .= '<i>T' . $o_tome->NUM_TOME . ' - </i> ';
            } else if ($o_tome->FLG_INT_TOME == "O" and stripos(strtolower($o_tome->TITRE_TOME),"intégrale") === false) {
                $html .= '<i>Intégrale - </i> ';
                
            }
            
            if ($url) {
                $html .=  "<strong>".$this->urlAlbum($o_tome, 'albTitle')."</strong>" ;
            } else {
                $html .=  $o_tome->TITRE_TOME;
            }
            
        }
        return $html;
    }
    
    public function small($o_tome, $getUrlSerie = true) {
        
        if (is_array($o_tome)) {
            $o_tome = (object) $o_tome;
        }

        $html = '
            <div class="cadre1 fiche_small">
            <div style="float:left" class="mw50">
            ' . $this->urlAlbum($o_tome, 'couvSmall') . '
            </div>
            <div style="float:left;font-size:0.9em" class="mw50">';
        
        // titre de l'album
        if ($o_tome->TITRE_TOME) {
            
            $html .= $this->getTitreTome($o_tome) . '<br>';
        }
        
        // nom de la serie
        if ($o_tome->NOM_SERIE AND $o_tome->TITRE_TOME AND $getUrlSerie
            AND (strtolower($o_tome->NOM_SERIE) != strtolower($o_tome->TITRE_TOME)) ) {
            $html .= 'Série : ' . $this->urlSerie($o_tome) . '';
        }
        
        // genre
        $html .= '</div><hr class="expSep"></div>';
        return $html;
    }
        
    public function medium($o_tome, $getUrlSerie = true) {
        if (is_array($o_tome)) {
            $o_tome = (object) $o_tome;
        }

        $html = '
            <div class="cadre1 fiche_medium">
            <div style="float:left">
            ' . $this->urlAlbum($o_tome, 'couvMedium') . '
            </div>
            <div style="float:left">';
        $html .= '<table>';
        
        // titre de l'album
        if ($o_tome->TITRE_TOME) {
            $html .= '<tr><td>Titre : </td><td>';
            if ($o_tome->NUM_TOME AND stripos($o_tome->TITRE_TOME, "n°".$o_tome->NUM_TOME) === false) {
            //if ($o_tome->NUM_TOME) {
                $html .= '<i>tome ' . $o_tome->NUM_TOME . ' -</i> ';
            }
            $html .= $this->getTitreTome($o_tome)  . '</td></tr>';
        }
        
        // nom de la serie
        if ($o_tome->NOM_SERIE AND ($o_tome->TITRE_TOME AND (strtolower($o_tome->NOM_SERIE) != strtolower($o_tome->TITRE_TOME)) AND $getUrlSerie)) {
            $html .= '<tr><td>Série : </td><td>' . $this->urlSerie($o_tome) . '</td></tr>';
        }

        $html .= '</table>';
        $html .= '</div><hr class="expSep"></div>';

        return $html;
    }
    
    public function big($o_tome, $sep = true, $getUrlSerie = true) {
        if (is_array($o_tome)) {
            $o_tome = (object) $o_tome;
        }

        $html = '
            <div class="cadre1 fiche_big">
            <div style="float:left" class="mw50">
            ' . $this->urlAlbum($o_tome, 'couvBig') . '
            </div>
            <div style="float:left;margin-left: 5px;" class="mw50">';

        // titre de l'album
        if ($o_tome->TITRE_TOME) {
           
            $html .=  $this->getTitreTome($o_tome) . '<br>';
        }

        // note/votes
        if ($o_tome->NB_NOTE_TOME > 0) {
            $html .= '<div align="center" id=noteTome' . $o_tome->ID_TOME . '> </div>';
            $html .= "<script>$('#noteTome" . $o_tome->ID_TOME . "').raty({score: " . $o_tome->MOYENNE_NOTE_TOME / 2 . ", readOnly: true});</script>";
        }
        
        // nom de la serie
        $html.="<p class='fiche_album'>";
        if ($o_tome->NOM_SERIE AND ($o_tome->TITRE_TOME AND (strtolower($o_tome->NOM_SERIE) != strtolower($o_tome->TITRE_TOME)) AND $getUrlSerie)) {
            $html .= 'Série : ' . $this->urlSerie($o_tome) . '<br>';
        }
        
        // genre
        if ($o_tome->NOM_GENRE) {
            $html .= 'Genre : ';
            $html .= '<i>' . $o_tome->NOM_GENRE . '</i><br>';
        }
        if ($o_tome->scpseudo) {
            $html .= 'Auteur(s) : ';
            $html .= '<i>' . $o_tome->scpseudo . ($o_tome->ID_SCENAR == $o_tome->ID_DESSIN ? '' : " / " . $o_tome->depseudo ) . '</i><br>';
        }
        
        // editeur
        if ($o_tome->NOM_EDITEUR) {
            $html .= 'Editeur : ';
            $html .= '<i>' . $o_tome->NOM_EDITEUR . '</i>';
            if (($o_tome->NOM_COLLECTION) and ($o_tome->NOM_COLLECTION != '<N/A>')) {
                $html .= ' -  <i>' . $o_tome->NOM_COLLECTION . '</i>';
            }
            $html .= "<br>";
        }
        
        // collection editeur
        // date de parution
        if ($o_tome->DATE_PARUTION_EDITION) {
            $html .= 'Date parution : ';
            $html .= $this->dateParution($o_tome->DATE_PARUTION_EDITION);
        }

        $html .= "</p>";
        
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            // ajout des liens d'ajout dans la collection
            $html .= '<br>' . $this->linkCollection($o_tome);
        }

        $html .= '</div>';
        
        if ($sep) {
            $html .= '<hr class="expSep">';
        }
        $html .= '</div>';

        return $html;
    }
    
    public function serie($o_serie) {
        if (is_array($o_serie)) {
            $o_serie = (object) $o_serie;
        }

        $html = '<div class="cadre1">';

        if ($o_serie->IMG_COUV_SERIE)
            $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="couvSmall" style="float:left;margin=2px">';

        $html .= '<div><table><tr><td class="fiche_h2l">';
        $html .= $this->urlSerie($o_serie);
        $html .= '</td></tr>';

        // genre
        if ($o_serie->NOM_GENRE) {
            $html .= '<tr><td class="fiche_h3l">Genre : <i>' . $o_serie->NOM_GENRE . '</i></td></tr>';
        }

        //avancement 0: Finie, 1: En cours, 2: One-shot, 3:Interrompue
        if (!is_null($o_serie->FLG_FINI_SERIE)) {
            $a_avancement = array(
                0 => 'Finie',
                1 => 'En cours',
                2 => 'One-shot',
                3 => 'Interrompue',
            );
            $html .= '<tr><td class="fiche_h3l">Avancement : ' . $a_avancement[$o_serie->FLG_FINI_SERIE] . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '</div><hr class="expSep" />';
        $html .= '</div>';

        return $html;
    }
    
    /*
     * urlAlbum
     * fournit l'url d'un lien vers la iframe album
     *
     */
    public function urlAlbum($o_tome, $class = 'couvBig', $is_edition = false)
    /*
     *  Fonction de construction d'une url d'un album
     *  Si la variable $is_edition = true, on ajoute l'id édition dans les liens
     *  Utile pour l'accès depuis la collection de l'utilisateur, où on sélectionne une édition précise
     */ 
    {
        if (is_array($o_tome)) {
            $o_tome = (object) $o_tome;
        }

        $id_link = "Album?id_tome=" . $o_tome->ID_TOME;

        if ($is_edition)
            $id_link.="&id_edition=" . $o_tome->ID_EDITION;
        
        // couverture par defaut
        if (!$o_tome->IMG_COUV)
            $o_tome->IMG_COUV = "default.png";

        $x = getenv("HTTP_USER_AGENT");
        
        if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {
            return '#" onclick="window.open(' . "'" . BDO_URL . $id_link . "','Album','width=600,height=600,scrollbars=1')" . ';return false;';
        } else {
            // titre html
            if ($o_tome->NOM_SERIE AND (strtolower($o_tome->NOM_SERIE) != strtolower($o_tome->TITRE_TOME))) {
                $titleHtml = $o_tome->NOM_SERIE;

                if ($o_tome->NUM_TOME AND stripos($o_tome->TITRE_TOME, "n°".$o_tome->NUM_TOME) === false) {
                //if ($o_tome->NUM_TOME) {
                    $titleHtml .= ' - ' . 'tome ' . $o_tome->NUM_TOME;
                }

                $titleHtml .= ' - ' . $o_tome->TITRE_TOME;
            } else {
                $titleHtml = $o_tome->TITRE_TOME;
            }

            $html = '<a class="fancybox fancybox.iframe {width:600,height:500}"
                href="' . BDO_URL . $id_link . '" title="' . $titleHtml . '">';
            
            switch ($class) {
                case "couvBig": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . ($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
                        break;
                }

                case "couvMedium": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . ($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
                        break;
                }

                case "couvSmall": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>';
                        break;
                }
                case "albTitle": {
                        $html .= $o_tome->TITRE_TOME . '</a>';
                        if (Bdo_Cfg::user()->minAccesslevel(1)) {
                            $html .= '&nbsp;&nbsp;<a href="' .BDO_URL. 'admin/editalbum?alb_id=' .$o_tome->ID_TOME. '" target="_blank"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
                        }
                        break;
                }
                default: {
                        $html .= '<img src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . ($o_tome->NOM_EDITEUR ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
                        break;
                }
            }
            return $html;
        }
    }
    public function getURLSerie($o_serie,$page=1) {
        if (is_array($o_serie)) {
            $o_serie = (object) $o_serie;
        }
        return BDO_URL . 'serie-bd-' . $o_serie->ID_SERIE . '-' .clean_url($o_serie->NOM_SERIE);
    }
    
    public function urlSerie($o_serie,$target="",$page=1) {
        if (is_array($o_serie)) {
            $o_serie = (object) $o_serie;
        }
       
        $html = '<a href="' . $this->getURLSerie($o_serie,$page) .'" title="' . $o_serie->NOM_SERIE . '"'.( $target ? 'target="'. $target.'"' : '') .'>
            ' . $o_serie->NOM_SERIE . '</a>';

        if (Bdo_Cfg::user()->minAccesslevel(1)) {
            $html .= '&nbsp;&nbsp;<a class="fancybox fancybox.iframe {width:600,height:600}" href="' .BDO_URL .'admin/editserie?serie_id=' . $o_serie->ID_SERIE . '"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
        }

        return $html;
    }
    
    

    public function linkCollection($o_tome) {
        // fonction pour ajouter les liens d'ajouts dans la collection
        $html = "";

        if (User::minAccesslevel(2)) {
//            $id_source = "addAlbum" . $o_tome->ID_EDITION;
//            $html = "<div id='" . $id_source . "' style='font-size:0.9em;'>";
//
//            if ($o_tome->DATE_AJOUT) {
//                if ($o_tome->FLG_ACHAT == "O") {
//                    $html.= "ajouté à vos futurs achats le " . date_format(date_create($o_tome->DATE_AJOUT), "d/m/Y");
//                } else {
//                    $html.= "ajouté à votre collection le " . date_format(date_create($o_tome->DATE_AJOUT), "d/m/Y");
//                }
//
//                $html .= " - <a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' title='Supprimer l'édition de ma collection' onclick='deleteEdition(" . $o_tome->ID_EDITION . ")'>Supprimer</a>";
//            } else {
//
//                $html .= "<a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' href='javascript:addAlbum(" . $o_tome->ID_TOME . "," . $o_tome->ID_EDITION . ',"N")' . "'" . ' title="Ajouter cet album dans votre collection">Dans ma collection</a>';
//                $html .= " - <a class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' href='javascript:addAlbum(" . $o_tome->ID_TOME . "," . $o_tome->ID_EDITION . ',"O")' . "'" . ' title="A acheter prochainement">Futur Achat</a>';
//            }
//
//            $html.= "</div>";
            $id_source = "infoCollection" . $o_tome->ID_TOME;
            $html= "<div id='".$id_source."'></div><script>getInfoCollectionFromTome(".$o_tome->ID_TOME.",".$o_tome->ID_EDITION.")</script>";

        } else {
            $html = "<i>Connectez vous pour ajouter cet album dans votre collection !</i>";
        }

        return $html;
    }

    public function getSponsor($o_tome) {
        $html = "<a href='";

        if ($o_tome->ISBN_EDITION) {
            $html .= "http://www.amazon.fr/exec/obidos/ASIN/" . $o_tome->ISBN_EDITION . "/bdovorecom-21/";
        } else if ($o_tome->EAN_EDITION) {
            $html .= "http://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=" . $o_tome->EAN_EDITION . "&mode=books-fr";
        } else {
            $html .= "http://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=" . htmlspecialchars(stripslashes($o_tome->TITRE_TOME)) . "&mode=books-fr";
        }
        $html .= "' target='_blank'><img src='" . BDO_URL_IMAGE . "amazon.gif'></a>";

        return $html;
    }

    public function dateParution($date, $flag = 0) {

        if (!empty($date)) {
            // date de parution superieur a date du jour moins 1 mois
            if (TimestampDate($date) > (time() - 2592000)) {
                $dte_parution = translate_date($date);
            } else {
                $dte_parution = month_to_text(substr($date, 5, 2)) . " " . substr($date, 0, 4);
            }
        } else {
            if ($flag == 1)
                $dte_parution = "Date introuvable";
            else
                $dte_parution = "Champ 'date' non-rempli";
        }

        return $dte_parution;
    }
}
