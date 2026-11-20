<?php

class Actualites extends Bdo_Controller
{
    public function Index()
    {
        $this->loadModel('News');
        $this->view->addCssFile('style/actualites.css?v=20261120a');

        $taille = 12;
        $page = getValInteger('page', 1);
        if (!is_int($page) || $page < 1 || $page > intdiv(PHP_INT_MAX, $taille)) {
            $page = 1;
        }
        $debut = ($page - 1) * $taille;
        $limite = $taille + 1;
        $resultats = $this->News->load('c', "WHERE news_level = 5 ORDER BY news_date DESC, news_id DESC LIMIT $debut, $limite");
        $this->view->set_var(array(
            'PAGETITLE' => 'Toutes les actualités · BDovore',
            'actualites' => array_slice($resultats->a_dataQuery, 0, $taille),
            'page' => $page,
            'pageSuivante' => count($resultats->a_dataQuery) > $taille,
        ));
        $this->view->render();
    }

    public function Lire()
    {
        $this->loadModel('News');
        $this->view->addCssFile('style/actualites.css?v=20261120a');

        $id = getValInteger('id');
        $publication = null;
        if (is_int($id) && $id > 0) {
            $resultats = $this->News->load('c', "WHERE news_level = 5 AND news_id = $id");
            $publication = $resultats->a_dataQuery[0] ?? null;
        }
        if (!$publication) {
            http_response_code(404);
        }

        $this->view->set_var(array(
            'PAGETITLE' => $publication
                ? htmlspecialchars($publication->news_titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' · BDovore'
                : 'Actualité introuvable · BDovore',
            'publication' => $publication,
        ));
        $this->view->render();
    }
}
