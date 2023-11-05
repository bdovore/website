<?php

/**
 * @author Tom
 *
 */
class Wikipediainfo extends Bdo_Controller {

    /**
     */

    public function Index() {
        Bdo_Cfg::setVar('debug',false);
        $search_item = getVal("search_item","");
        $section = getValInteger("section",0);
        $url = 'http://fr.wikipedia.org/w/api.php?format=json&action=parse&page='.urlencode($search_item)."&prop=text&section=".$section;
       
        $data2 =file_get_contents($url);
       
        $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
    
    public function Extract() {
        Bdo_Cfg::setVar('debug',false);
         $search_item = getVal("search_item","");
         $url = "https://fr.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exlimit=1&titles=".urlencode($search_item);
         
         $data2 =file_get_contents($url);
         $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
    
    public function Sections() {
        Bdo_Cfg::setVar('debug',false);
         $search_item = getVal("search_item","");
         $url = 'http://fr.wikipedia.org/w/api.php?format=json&action=parse&page='.urlencode($search_item)."&prop=sections";
       
        $data2 =file_get_contents($url);
       
        $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
}
