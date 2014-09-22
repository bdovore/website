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
        // pas la peine de faire un $this->loadModel('Actus'). il est fait par
        // defaut dans le Bdo_Controller
        
        // modif Tom : on sort la gestion des derniers albums du model actus qui doit disparaitre à terme
        // TODO : créer model News, gérer le cache des top directement dans Accueil
       
        $this->loadModel('Actus');

        $this->view->set_var(array(
                'ACTUAIR' => $this->Actus->actuAir(),
                'LASTAJOUT' => $this->Actus->lastAjout()
        ));
       
        $this->view->set_var(
                array(
                        'a_lastSorties' => $this->lastSorties(6),
                        'a_lastNews' => $this->lastNews(4),
                        'a_lastBD' => $this->lastCommentaires(3),
                        'a_lastManga' => $this->lastCommentaires(3,"Mangas"),
                        'a_lastComics' => $this->lastCommentaires(3,"Comics"),
                        'a_futurSorties' => $this->futurSorties(6),
                        'PAGETITLE' => "BDOVORE.com - gestion de collection de BD, actualité BD et forums BD"
                ));
        
        $this->view->addPhtmlFile('news', 'LASTNEWS', true);
        $this->view->render();
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
        $dbs = $this->Tome->load("c"," WHERE en.DTE_PARUTION <= CURDATE() and en.DTE_PARUTION > DATE_ADD(CURDATE(), INTERVAL -12 MONTH) and bd_tome.id_genre not in (17,55) order by en.DTE_PARUTION desc limit 0,".intval($max));

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

