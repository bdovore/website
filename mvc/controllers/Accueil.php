<?php

/**
 *
 * @author laurent
 *
 */
class Accueil extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $this->view->addCssFile('style/album-lists.css?v=20261118b');
        $this->view->addCssFile('style/actualites.css?v=20261118c');
        $this->view->addCssFile('style/accueil.css?v=20261118b');
        // pas la peine de faire un $this->loadModel('Actus'). il est fait par
        // defaut dans le Bdo_Controller

        // modif Tom : on sort la gestion des derniers albums du model actus qui doit disparaitre à terme
        // TODO : créer model News, gérer le cache des top directement dans Accueil

        $this->loadModel('Actus');
        $this->loadModel("Tome");
        $this->loadModel("News");
        $this->view->set_var(array(
                'ACTUAIR' => $this->tendances(5),
                'LASTAJOUT' => $this->Actus->lastAjout()
        ));
        $opengraph = array(
            "type" => "website",
            "image" => "https://www.bdovore.com/images/site/bdo_fond1.jpg"
        );
        $this->loadModel("Comment");
        $dbs = $this->Comment->load("c"," WHERE c.comment <> ''  order by DTE_POST desc limit 0, 6");

        $this->view->set_var(
                array(
                        'a_lastSorties' => $this->lastSorties(5),
                        'a_lastNews' => $this->News->getLastSecondNews(5),
                        'a_laUne' => $this->News->getLastNewsPerType(),
                       /* 'a_lastBD' => $this->lastCommentaires(3),
                        'a_lastManga' => $this->lastCommentaires(3,"Mangas"),
                        'a_lastComics' => $this->lastCommentaires(3,"Comics"),*/
                        'a_lastComment' => $dbs->a_dataQuery,
                        'a_futurSorties' => $this->futurSorties(5),
                        'nbTome' => $this->Tome->getNbTotalTome(),
                    'opengraph' => $opengraph,
                        'PAGETITLE' => "BDOVORE.com - gestion de collection de BD, Manga et Comics"
                ));

        $this->view->render();
    }

    private function tendances($limit)
    {
        $albums = array_slice((array) $this->Actus->actuAir(), 0, $limit);
        if (!$albums) {
            return $albums;
        }

        // Les entrées du cache portent le classement et la couverture, mais pas les auteurs.
        $ids = implode(',', array_map('intval', array_column($albums, 'ID_TOME')));
        $details = $this->Tome->load('c', "WHERE bd_tome.ID_TOME IN ($ids)")->a_dataQuery;
        $parAlbum = array_column($details, null, 'ID_TOME');
        foreach ($albums as $album) {
            $detail = $parAlbum[$album->ID_TOME] ?? null;
            $album->ID_SCENAR = $detail->ID_SCENAR ?? null;
            $album->scpseudo = $detail->scpseudo ?? null;
            $album->ID_DESSIN = $detail->ID_DESSIN ?? null;
            $album->depseudo = $detail->depseudo ?? null;
        }
        return $albums;
    }

    private function lastCommentaires ($max=5,$origine="BD")
    {
        $origine = Db_Escape_String($origine);
        $this->loadModel("Comment");
        $dbs = $this->Comment->load("c"," WHERE c.comment <> '' and g.origine = '$origine' order by DTE_POST desc limit 0,".intval($max));

        return $dbs->a_dataQuery;
    }

    private function lastSorties ($max=5)
    {
        $this->loadModel("Tome");
        $dbs = $this->Tome->load("c"," WHERE en.DTE_PARUTION <= CURDATE() and en.DTE_PARUTION > DATE_ADD(CURDATE(), INTERVAL -2 MONTH) and bd_tome.id_genre not in (17,55) order by en.DTE_PARUTION desc limit 0,".intval($max));

        return $dbs->a_dataQuery;
    }

    private function futurSorties ($max=5)
    {
        $this->loadModel("Tome");
        $dbs = $this->Tome->load("c"," WHERE en.DTE_PARUTION > CURDATE() and bd_tome.id_genre not in (17,55) order by en.DTE_PARUTION limit 0,".intval($max));

        return $dbs->a_dataQuery;
    }

    private function lastNews($limit = 5) {
        $this->loadModel("News");
        $dbs = $this->News->load("c", "WHERE news_level>=5  ORDER BY News_id DESC LIMIT 0, ".intval($limit));

        return $dbs->a_dataQuery;
    }

}

