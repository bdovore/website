<?php

class FicheAlbum {

    public function getTitreTome ($o_tome,$url=true, $comment = false) {
        $html = "";
        if (issetNotEmpty($o_tome->TITRE_TOME)) {
            $int = issetNotEmpty($o_tome->FLG_INT_TOME) ? $o_tome->FLG_INT_TOME : "";
            if ($int == "O" and stripos(strtolower($o_tome->TITRE_TOME),"intégrale") === false) {
                $html .= '<i>Intégrale - </i> ';
            }

           if (issetNotEmpty($o_tome->NUM_TOME)) {
               if (preg_match("/n°".$o_tome->NUM_TOME."\b/i",$o_tome->TITRE_TOME) !== 1) {
                   $html .= '<i>T' . $o_tome->NUM_TOME . ' - </i> ';
                   
               }
           }
                
            

            if ($url) {
                $html .=  "<strong>".$this->urlAlbum($o_tome, 'albTitle', $is_edition = false, $sponsor=true,$gotocomment = $comment)."</strong>" ;
            } else {
                $html .=  "<span itemprop='name'>".$o_tome->TITRE_TOME.'</span>';
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
            <div  class="mw50 couvleft">
            ' . $this->urlAlbum($o_tome, 'couvSmall') . '
            </div>
            <div  class="mw50 textleft">';

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
            $html .= '<tr><td>Série </td><td>' . $this->urlSerie($o_tome) . '</td></tr>';
        }

        $html .= '</table>';
        $html .= '</div><hr class="expSep"></div>';

        return $html;
    }

    public function big($o_tome, $sep = true, $getUrlSerie = true, $exclu = false) {
        if (is_array($o_tome)) {
            $o_tome = (object) $o_tome;
        }

        $html = '
            <div class="cadre1 fiche_big" itemscope itemtype="http://schema.org/Book">
            <div style="float:left" class="mw50">
            ' . $this->urlAlbum($o_tome, 'couvBig') . '
            </div>
            <div class="mw50 couvleft">';

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
            $html .=  $this->urlSerie($o_tome) .'<br>';
        }

        // genre
        if ($o_tome->NOM_GENRE) {
            //$html .= 'Genre : ';
            $html .= '<i><span itemprop="genre"> ' . $o_tome->NOM_GENRE . '</span></i><br>';
        }
        if ($o_tome->scpseudo) {
           // $html .= 'Auteur(s) : ';
            $url_scenar = $this->urlAuteur(array("ID_AUTEUR" => $o_tome->ID_SCENAR, "PSEUDO" =>$o_tome->scpseudo ));
            $url_dessin = $this->urlAuteur(array("ID_AUTEUR" => $o_tome->ID_DESSIN, "PSEUDO" =>$o_tome->depseudo ));
            $html .= '<i>' . $url_scenar . ($o_tome->ID_SCENAR == $o_tome->ID_DESSIN ? '' : " / " . $url_dessin ) . '</i><br>';
        }

        // editeur
        if (issetNotEmpty($o_tome->NOM_EDITEUR)) {
            $html .= 'Edition ';
            $html .= '<i><span itemprop="publisher">' . $o_tome->NOM_EDITEUR . '</span></i>';
            if (($o_tome->NOM_COLLECTION) and ($o_tome->NOM_COLLECTION != '<N/A>')) {
                $html .= ' -  <i>' . $o_tome->NOM_COLLECTION . '</i>';
            }
            if (issetNotEmpty($o_tome->DATE_PARUTION_EDITION)) {
           // $html .= 'Date parution : ';
            $html .= '<i> ('.$this->dateParution($o_tome->DATE_PARUTION_EDITION). ')</i>';
            }
            $html .= "<br>";
        }

        // collection editeur
        // date de parution
        

        $html .= "</p>";

        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            // ajout des liens d'ajout dans la collection
            $html .= $this->linkCollection($o_tome,$exclu);
        }

        $html .= '</div>';

        if ($sep) {
            $html .= '<hr class="expSep">';
        }
        $html .= '</div>';

        return $html;
    }

    public function serie($o_serie, $size="small") {
        if (is_array($o_serie)) {
            $o_serie = (object) $o_serie;
        }
        if ($size == "big") {
            $couv = "couvBig";
        } else {
            $couv = "couvSmall";
        }
        $html = '
            <div class="cadre1 fiche_big">
            <div style="float:left" class="mw50">
            ' . ($o_serie->IMG_COUV_SERIE ? '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="'.$couv.'" style="float:left;margin=2px">' : "") . '
            </div>
            <div style="float:left;margin-left: 5px;" class="mw50">';




        $html .= "<strong>".$this->urlSerie($o_serie)."</strong><br>";
        if ($o_serie->NB_NOTE_SERIE > 0 and $size == "big") {
             $html .= '<div align="center" id=noteSerie' . $o_serie->ID_SERIE . '> </div>';
            $html .= "<script>$('#noteSerie" . $o_serie->ID_SERIE . "').raty({score: " . $o_serie->NOTE_SERIE/2.0 . ", readOnly: true});</script>";

        }
        $html.="<p class='fiche_album'>";

        // genre
        if ($o_serie->NOM_GENRE) {
            if ($size == "big") $html.= "Genre : ";
            $html .= '<i>' . $o_serie->NOM_GENRE . '</i><br>';
        }

        //avancement 0: Finie, 1: En cours, 2: One-shot, 3:Interrompue
        if (!is_null($o_serie->FLG_FINI_SERIE)) {
            $a_avancement = array(
                0 => 'Finie',
                1 => 'En cours',
                2 => 'One-shot',
                3 => 'Interrompue',
            );
            if ($size == "big") $html.= "Avancement : ";
            $html .= "<i>" .$a_avancement[$o_serie->FLG_FINI_SERIE] . '</i><br>';
        }

        if ($size== "big") {
            $html.= "Albums dans la base : ".$o_serie->NB_ALBUM;
        }
        $html .= '</div><hr class="expSep" />';
        $html .= '</div>';

        return $html;
    }

    /*
     * urlAlbum
     * fournit l'url d'un lien vers la iframe album
     *
     */
    public function urlAlbum($o_tome, $class = 'couvBig', $is_edition = false, $sponsor=true,$gotocomment = false, $target="", $title="")
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
        
        if ($gotocomment) 
            $id_link .="#comment";
        // couverture par defaut
        if (!issetNotEmpty($o_tome->IMG_COUV))
            $o_tome->IMG_COUV = "default.png";

        $x = getenv("HTTP_USER_AGENT");

        if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {
            return '#" onclick="window.open(' . "'" . BDO_URL . $id_link . "','Album','width=600,height=700,scrollbars=1')" . ';return false;';
        } else {
            // titre html    
            
            if (isset($o_tome->NOM_SERIE)) {
                    if (strtolower($o_tome->NOM_SERIE) != strtolower($o_tome->TITRE_TOME)) {
                         $titleHtml = $o_tome->NOM_SERIE;

                        if (issetNotEmpty($o_tome->NUM_TOME)) {
                            if (stripos($o_tome->TITRE_TOME, "n°".$o_tome->NUM_TOME) === false) {
                                 $titleHtml .= ' - ' . 'tome ' . $o_tome->NUM_TOME;
                            }
                        //if ($o_tome->NUM_TOME) {
                           
                        }

                        $titleHtml .= ' - ' . $o_tome->TITRE_TOME;
                    } else {
                        $titleHtml = $o_tome->TITRE_TOME;
                    }
               
            } else {
                $titleHtml = $o_tome->TITRE_TOME;
            }
           
            $html = '<a '. ( $target  ? '': 'class="fancybox fancybox.iframe {width:600,height:600}"').
                'href="' . BDO_URL . $id_link . '" title="' . $titleHtml . '" '.( $target ? 'target="'. $target.'"' : '') .'>';

            switch ($class) {
                case "couvBig": {
                        $html .= '<img itemprop="image" src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . (isset($o_tome->NOM_EDITEUR) ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
                        if ($sponsor) $html .= "<div align=center>". $this->getSponsor($o_tome,true)."</div>";
                        break;
                }

                case "couvMedium": {
                        $html .= '<img itemprop="image" src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . (issetNotEmpty($o_tome->NOM_EDITEUR) ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
                        break;
                }

                case "couvSmall": {
                        $html .= '<img itemprop="image" src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" class="' . $class . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>';
                        break;
                }
                case "albTitle": {
                        $html .= '<span itemprop="name">'.$o_tome->TITRE_TOME . '</span></a>';
                        if (Bdo_Cfg::user()->minAccesslevel(1)) {
                            $html .= '&nbsp;&nbsp;<a href="' .BDO_URL. 'admin/editalbum?alb_id=' .$o_tome->ID_TOME. '" target="_blank"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
                        }
                        break;
                }
                case "link": {
                     $html .= ($title ? $title : $o_tome->TITRE_TOME).'</a>';
                      break;
                    
                }
                default: {
                        $html .= '<img itemprop="image" src="' . BDO_URL_COUV . $o_tome->IMG_COUV . '" title="' . $titleHtml . '"/>';
                        $html .= '</a>' . (issetNotEmpty($o_tome->NOM_EDITEUR) ? '<div class="copyright">&copy; ' . $o_tome->NOM_EDITEUR . '</div>' : '');
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

        $html = '<a href="' . $this->getURLSerie($o_serie,$page) .'" title="' . $o_serie->NOM_SERIE . '"'.( $target ? ' target="'. $target.'"' : '') .'>
            ' . $o_serie->NOM_SERIE . '</a>';

        if (Bdo_Cfg::user()->minAccesslevel(1)) {
            $html .= '&nbsp;&nbsp;<a class="fancybox fancybox.iframe {width:600,height:600}" href="' .BDO_URL .'admin/editserie?serie_id=' . $o_serie->ID_SERIE . '"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
        }

        return $html;
    }

    public function getURLAuteur($o_auteur) {
         if (is_array($o_auteur)) {
            $o_auteur = (object) $o_auteur;
        }
         $url =  BDO_URL . 'auteur-bd-' . $o_auteur->ID_AUTEUR . '-' .clean_url($o_auteur->PSEUDO);
         return $url;
    }
    public function urlAuteur($o_auteur,$target="") {
        if (is_array($o_auteur)) {
            $o_auteur = (object) $o_auteur;
        }
        if ($o_auteur->ID_AUTEUR == 885 OR $o_auteur->ID_AUTEUR < 4 ) {
            $html = htmlspecialchars($o_auteur->PSEUDO);
        } else {
        $url =  $this->getURLAuteur($o_auteur);
         $html = '<a itemprop="author" href="' . $url.'" title="Tous les albums de ' . $o_auteur->PSEUDO . '"' .( $target ? ' target="'. $target.'"' : '')  .'>
             ' . $o_auteur->PSEUDO . '</a>';

         if (Bdo_Cfg::user()->minAccesslevel(1)) {
             $html .= '&nbsp;&nbsp;<a class="fancybox fancybox.iframe {width:600,height:600}" href="' .BDO_URL .'admin/editauteur?auteur_id=' . $o_auteur->ID_AUTEUR . '"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
         }
        }

        return $html;
    }

    public function linkCollection($o_tome, $exclu = false) {
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
            $html= "<div id='".$id_source."'></div><script>getInfoCollectionFromTome(".$o_tome->ID_SERIE.",".$o_tome->ID_TOME.",".$o_tome->ID_EDITION.",".($exclu ? "true" : "false").",true)</script>";

        } else {
            $html = "<i>Connectez vous pour ajouter cet album dans votre collection !</i>";
        }

        return $html;
    }

    public function getSponsor($o_tome, $img=true, $all=false) {
        $html = "<a title='Achetez sur Amazon !' href='";
        // amazon
        if (issetNotEmpty($o_tome->ISBN_EDITION)) {
            $html .= BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/ASIN/" . $o_tome->ISBN_EDITION . "/bdovorecom-21/";
        } else if (issetNotEmpty($o_tome->EAN_EDITION)) {
            $html .= BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/ASIN/" . EAN_to_ISBN($o_tome->EAN_EDITION) . "/bdovorecom-21/";
        } else {
            $html .= BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=" . htmlentities ($o_tome->TITRE_TOME,$flag=ENT_QUOTES) . "&mode=books-fr";
        }
        if ($img) {
            $html .= "' target='_blank'><img src='" . BDO_URL_IMAGE . "amazon blanc.jpg' class='img-sponsor' height='22px' width='85px'></a>";
        } else {
            $html .= "' target='_blank'>Commandez sur Amazon</a>";
        }
        // bdfugue
        if (issetNotEmpty($o_tome->EAN_EDITION) and $all) {
            $html .= "&nbsp;<a title='Achetez sur BDfugue !' target='_blank' href='" . BDO_PROTOCOL . "://www.bdfugue.com/a/?ref=295&ean=" . $o_tome->EAN_EDITION ."'><img src='" . BDO_URL_IMAGE . "bdfugue.png' height='22px' width='85px' class='img-sponsor'></a>";
        } 
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

    public function getFicheWithComment($tome) {
        if (strlen($tome->COMMENT) < 100) {
            $comment = $tome->COMMENT;
        } else {
            $comment = substr($tome->COMMENT,0,100)."...". $this->urlalbum($tome,'link',$is_edition = false, $sponsor = true,  $gotocomment= true, "", $title='lire la suite');
        }
         $html = "<div class='cadre1'>
                <table>
            <tr class='listAlbum'>
                <td style='vertical-align:top'>".$this->urlalbum($tome,'couvBig')."</td>
            <td style='vertical-align:top'>
                ".$this->getTitreTome($tome, true, true)."<br>
                     ".(($tome->NOM_SERIE AND ($tome->TITRE_TOME AND (strtolower($tome->NOM_SERIE) != strtolower($tome->TITRE_TOME)) )) ? $this->urlSerie($tome) : "")." <br>
                <span id='noteTome".$tome->ID_TOME."_".$tome->user_id."'> </span>
                <p class='fiche_album'>               
                Publié par <a href='./guest?user=$tome->user_id'>$tome->username</a> le $tome->DTE_POST 
                    
                </p>
                $comment 
            </td>
            </table>
             </div>
             <script>$('#noteTome".$tome->ID_TOME."_".$tome->user_id."').raty({score: ".($tome->NOTE/2).", readOnly: true});</script>";
         return $html;
    }
}
