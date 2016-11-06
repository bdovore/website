<?php


/**
 * Liste les albums dans un sitemap xml pour indexation google
 *
 * @author Tom
 */
class Sitemapalbum extends Bdo_Controller {
    
    public function Index () {
        $top = getValInteger("top",10000);
        $page = getValInteger("page",1);
        $this->loadModel("Tome");
        
        $start = ($page-1)*$top;
        $dbs_album = $this->Tome->load("c", " WHERE 1 ORDER BY bd_tome.ID_TOME LIMIT ".$start.",".$top);
        
        $this->view->set_var(array(
            "dbs_tome" => $dbs_album
        ));
        $this->view->layout = "sitemap";
        $this->view->render();
    }
}