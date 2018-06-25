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
      $html .= htmlspecialchars($o_auteur->pseudo) . '</a></h3>';

      $html .= "<p class='fiche_album'>";
      
      // Nom = Nom[, prénom]|Pseudo
      if ($o_auteur->nom) $nom = $o_auteur->nom;
      if ($o_auteur->prenom) {
        if ($nom) $nom .= ", ";
        $nom .= $o_auteur->prenom;
      }
      // Pour systématiser le champ nom afin d'y mettre le lien vers la fiche Auteur,
      // En absence d'information, on y met le pseudo.
      if (!$nom) $nom = $o_auteur->pseudo;
      $url =  BDO_URL . 'auteur-bd-' . $o_auteur->auteur . '-' .clean_url($o_auteur->pseudo);

      $html .= 'Nom : <a href="' .$url. '"><i>' . $nom . '</i></a><br>';

      // Activité
      $html .= "Activité : ";
      if ($o_auteur->scenar) $html .= '<span title="Scénariste" class="fas fa-file-signature"></span> ';
      if ($o_auteur->dessin) $html .= '<span title="Dessinateur" class="fas fa-pencil-alt"></span> ';
      if ($o_auteur->color) $html .= ' <span title="Coloriste" class="fas fa-palette"></span> ';
      $html .= '<br>';

      // Nombre d'albums
      $html .= "Albums dans la collection : ";
      $html .= '<a href="'.BDO_URL.'macollection/mesetageres?l_search=Auteur%3A'.  htmlspecialchars( $o_auteur->pseudo).'">' .$o_auteur->nbtomes."</a>";

      $html .= "</p>";
      $html .= '</div>';

      if ($sep) {
          $html .= '<hr class="expSep">';
      }
      $html .= '</div>';

      return $html;
    }
}
