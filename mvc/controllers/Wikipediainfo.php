<?php

/**
 * @author Tom
 *
 */
class Wikipediainfo extends Bdo_Controller {

    /**
     */

    public function Index() {
        $search_item = getVal("search_item","");
        
        $url = 'http://fr.wikipedia.org/w/api.php?format=json&action=parse&page='.urlencode($search_item)."&prop=text&section=0";
       
        $data2 =file_get_contents($url);
       
        $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
}
