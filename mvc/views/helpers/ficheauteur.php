<?php

class FicheAuteur {

    public function urlAuteur($o_auteur, $class = 'couvBig') {
      if (is_array($o_auteur)) {
        $o_auteur = (object) $o_auteur;
      }

      $id_link = $this->getURLAuteur($o_auteur);

      // couverture par defaut
      if (!$o_auteur->img_aut) $o_auteur->img_aut = "default_auteur.png";

      $x = getenv("HTTP_USER_AGENT");

      if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {
          return '#" onclick="window.open(' . "'" . $id_link . "','Serie','width=600,height=700,scrollbars=1')" . ';return false;';
      } else {
        // titre html
        $titleHtml = $o_auteur->pseudo;

        $html = '<a href="' . $id_link . '" title="' . $titleHtml . '">';

        switch ($class) {
          default: {
            $html .= '<img src="' . BDO_URL_IMAGE.'auteur/'. $o_auteur->img_aut .'" class="'. $class.'" title="'. $titleHtml.'"/></a>';
            break;
          }
        }
        return $html;
      }
    }

    public function getURLAuteur($o_auteur,$page=1) {
      if (is_array($o_auteur)) {
        $o_auteur = (object) $o_auteur;
      }
      return BDO_URL . 'macollection/messeries?auteur=' . $o_auteur->auteur . '&pseudo=' . $o_auteur->pseudo;
    }

    public function big($o_auteur,$sep=true) {
      if (is_array($o_auteur)) {
        $o_auteur = (object) $o_auteur;
      }

      $html = '<div class="cadre1 fiche_big">
                 <div style="float:left" class="mw50">';
      $html .= $this->urlAuteur($o_auteur);
      $html .= '</div>
                 <div class="mw50 couvleft">';

      // Nom de l'auteur (pseudo)
      $html .= '<h3>';
      $html .= '<a href="' . $this->getURLAuteur($o_auteur) . '" ';
      $html .= '   title="' . $o_serie->pseudo . '">';
      $html .= $o_auteur->pseudo . '</a></h3>';

      $html .= "<p class='fiche_album'>";
      
      // Nom
      if ($o_auteur->nom) {
          $html .= 'Nom : ';
          $html .= '<i>' . $o_auteur->nom . '</i><br>';
      }

      // Prénom
      if ($o_auteur->prenom) {
          $html .= 'Prénom : ';
          $html .= '<i>' . $o_auteur->prenom . '</i><br>';
      }

      // Activité
      $html .= "Activité : ";
      if ($o_auteur->scenar) $html .= '<img src="' . BDO_URL_IMAGE.'site/picto-scenario.png" width="15px" alt="scenariste" title="Scénariste"/> ';
      if ($o_auteur->dessin) $html .= '<img src="' . BDO_URL_IMAGE.'site/picto-dessin.png" width="15px" alt="dessinateur" title="Dessinateur"/> ';
      if ($o_auteur->color) $html .= '<img src="' . BDO_URL_IMAGE.'site/picto-color.png" width="15px" alt="colorriste" title="Coloriste"/> ';
      $html .= '<br>';

      // Nombre d'albums
      $html .= "Albums dans la collection : ";
      $html .= $o_auteur->nbtomes;

      $html .= "</p>";
      $html .= '</div>';

      if ($sep) {
          $html .= '<hr class="expSep">';
      }
      $html .= '</div>';

      return $html;
    }
}
