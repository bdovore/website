<?php

class FicheSerie {

    public function urlSerie($o_serie, $class = 'couvBig')
    {
      if (is_array($o_serie)) {
            $o_serie = (object) $o_serie;
        }

        $id_link = $this->getURLSerie($o_serie);

        // couverture par defaut
        if (!$o_serie->IMG_COUV_SERIE)
            $o_serie->IMG_COUV_SERIE = "default.png";

        $x = getenv("HTTP_USER_AGENT");

        if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {
            return '#" onclick="window.open(' . "'" . $id_link . "','Serie','width=600,height=700,scrollbars=1')" . ';return false;';
        } else {
            // titre html
            $titleHtml = $o_serie->NOM_SERIE;

            $html = '<a href="' . $id_link . '" title="' . $titleHtml . '">';

            switch ($class) {
                case "couvBig": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                        break;
                }

                case "couvBigManque": {
                  $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                  break;
                }

                case "couvBigComplete": {
                  $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                  break;
                }

                case "couvBigExclue": {
                  $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                  break;
                }

                case "couvMedium": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                        break;
                }

                case "couvSmall": {
                        $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV_SERIE . '" class="' . $class . '" title="' . $titleHtml . '"/></a>';
                        break;
                }
                case "serTitle": {
                        $html .= $o_serie->nom_serie . '</a>';
                        if (Bdo_Cfg::user()->minAccesslevel(1)) {
                            $html .= '&nbsp;&nbsp;<a href="' .BDO_URL. 'admin/editserie?alb_id=' .$o_serie->ID_SERIE. '" target="_blank"><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>';
                        }
                        break;
                }
                default: {
                        $html .= '<img src="' . BDO_URL_COUV . $o_serie->IMG_COUV . '" title="' . $titleHtml . '"/></a>';
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

    public function big($o_serie,$sep=true,$incomplet=false,$exclu=false) {
      if (is_array($o_serie)) {
        $o_serie = (object) $o_serie;
      }

      $html = '<div class="cadre1 fiche_big">
                 <div style="float:left" class="mw50">';
      if ($incomplet !== false) {
        // Des albums sont à acheter (manquant)
        $html .= $this->urlSerie($o_serie);
      } elseif ($exclu == 'serie') {
        // Rien n'est à acheter (manquant) car toute la série est exclue
        $html .= $this->urlSerie($o_serie);
      } elseif ($exclu == 'album') {
        // Rien n'est à acheter (manquant) car les albums sont tous exclus (un par un)
        $html .= $this->urlSerie($o_serie);
      } else {
        $html .= $this->urlSerie($o_serie);
      }
      $html .= '</div>
                 <div class="mw50 couvleft">';

      // titre de la série
      $html .= '<h3>';
      $html .= '<a href="' . $this->getURLSerie($o_serie) . '" ';
      $html .= '   title="' . $o_serie->NOM_SERIE . '">';
      $html .= $o_serie->NOM_SERIE . '</a></h3>';

       // note/votes
      if ($o_serie->NB_NOTE_SERIE > 0) {
          $html .= '<div id=noteTome' . $o_serie->ID_SERIE . '> </div>';
          $html .= "<script>";
          $html .= "  $('#noteTome" . $o_serie->ID_SERIE . "').raty({score: " . $o_serie->NOTE_SERIE / 2 . ", readOnly: true});";
          $html .= "</script>";
      }

      $html .= "<p class='fiche_album'>";
      
      // Statut
      if ($o_serie->LIB_FLG_FINI_SERIE) {
          $html .= 'Avancement : ';
          $html .= '<i>' . $o_serie->LIB_FLG_FINI_SERIE . '</i><br>';
      }

      // genre
      if ($o_serie->NOM_GENRE) {
          $html .= 'Genre : ';
          $html .= '<i>' . $o_serie->NOM_GENRE . '</i><br>';
      }

      // Possédés
      if ($o_serie->NB_USER_ALBUM) {
        $html .= 'Dans ma collection : ';
        $html .= '<a href="'.BDO_URL.'macollection/mesetageres?l_search=Série%3A'.  htmlspecialchars( $o_serie->NOM_SERIE) .'"><i>' . $o_serie->NB_USER_ALBUM . '</i></a><br>';
      }

      // Nb Tome
      if ($o_serie->NB_TOME) {
        $html .= 'Nombre de tome : ';
        $html .= '<i>' . $o_serie->NB_TOME . '</i><br>';
      }

      // Connus
      if ($o_serie->NB_ALBUM) {
        $html .= 'Albums dans la base : ';
        $html .= '<i>' . $o_serie->NB_ALBUM . '</i><br>';
      }

      // Manquant ou Exclus
      // Il faut tester sur false pour éviter la valeur 0, considérée aussi comme false...
      if ($incomplet !== false) {
        // Des albums sont à acheter (manquant)
        $html .= '<a href="'.BDO_URL.'macollection/seriecomplete?lstSerie=' . $o_serie->ID_SERIE . '&flg_achat=1" title="Gestion des albums manquants pour cette série">Voir les albums manquants</a><br>';
      } elseif ($exclu == 'serie') {
        // Rien n'est à acheter (manquant) car toute la série est exclue
        $html .= '<i>Série exclue des "albums manquants" </i><br>';
      } elseif ($exclu == 'album') {
        // Rien n'est à acheter (manquant) car les albums sont tous exclus (un par un)
        $html .= '<i>Tous les albums manquants ont été exclus de la liste </i><br>';
      }

      $html .= "</p>";
      $html .= '</div>';

      if ($sep) {
          $html .= '<hr class="expSep">';
      }
      $html .= '</div>';

      return $html;
    }
}
