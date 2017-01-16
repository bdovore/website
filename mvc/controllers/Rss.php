<?php

/*
 * Controller la génération des flux rss
 * @author : Tom
 *
 */

class Rss extends Bdo_Controller {

    public function Index () {
        // flux par défaut = derniers ajouts
        $this->loadModel("Tome");
        $order = " ORDER BY bd_tome.id_tome DESC ";
        $limit = "limit 0, 30";
        $dbs_tome = $this->Tome->load('c', $order . $limit);
        $this->view->set_var(array(
            "dbs_tome" => $dbs_tome,
            "title" => "Bdovore - derniers ajouts"

        ));
        $this->view->layout = "rss";
        $this->view->render();
    }

    public function Dernieres_sorties () {
          // flux dernières sorties
        $this->loadModel("Tome");
        $where = " WHERE en.DTE_PARUTION <= CURDATE() AND en.DTE_PARUTION >= DATE_SUB(NOW(),INTERVAL 3 MONTH)";
        $order = " ORDER BY en.DTE_PARUTION DESC ";
        $limit = "limit 0, 30";
        $dbs_tome = $this->Tome->load('c',$where. $order . $limit);
        $this->view->set_var(array(
            "dbs_tome" => $dbs_tome,
            "title" => "Bdovore - dernieres sorties"

        ));
        $this->view->layout = "rss";
        $this->view->render();

    }

    public function Sorties_a_venir (){
           // flux sorties à venir
        $this->loadModel("Tome");
        $where = " WHERE en.DTE_PARUTION > CURDATE() ";
        $order = " ORDER BY en.DTE_PARUTION ASC ";
        $limit = "limit 0, 30";
        $dbs_tome = $this->Tome->load('c',$where. $order . $limit);
        $this->view->set_var(array(
            "dbs_tome" => $dbs_tome,
            "title" => "Bdovore - futures sorties"

        ));
        $this->view->layout = "rss";
        $this->view->render();
    }

}