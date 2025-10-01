<?php

/**
 * @author Tom
 *
 */
class Wikipediainfo extends Bdo_Controller {

    /**
     */
    public $user_agent = "Bdovore/1.0 (tomlameche@bdovore.com)";
    

    public function Index() {
        Bdo_Cfg::setVar('debug',false);
        $search_item = getVal("search_item","");
        $section = getValInteger("section",0);
        $url = 'http://fr.wikipedia.org/w/api.php?format=json&action=parse&page='.urlencode($search_item)."&prop=text&section=".$section;
       
        $data2 = $this->doRequest($url);
       
        $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
    
    public function Extract() {
        Bdo_Cfg::setVar('debug',false);
         $search_item = getVal("search_item","");
         $url = "https://fr.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exlimit=1&titles=".urlencode($search_item);
         
         $data2 = $this->doRequest($url);
         $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }
    
    public function Sections() {
        Bdo_Cfg::setVar('debug',false);
         $search_item = getVal("search_item","");
         $url = 'http://fr.wikipedia.org/w/api.php?format=json&action=parse&page='.urlencode($search_item)."&prop=sections";
       
        $data2 = $this->doRequest($url);
       
        $this->view->layout = "json";
        $this->view->set_var('json', $data2);
        $this->view->render();
    }

    private function doRequest($url, $headers = []) {

		// Headers par défaut (exemple avec User-Agent pour Wikidata)
        $defaultHeaders = [
            'User-Agent: '.$this->user_agent
        ];

        // Fusion des headers par défaut avec ceux passés en paramètre
        $allHeaders = array_merge($defaultHeaders, $headers);

        // Création du contexte avec les headers
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $allHeaders),
            ]
        ];
        $context = stream_context_create($options);

        // Récupération des données
        $data = @file_get_contents($url, false, $context);
        if (!$data) {
            $error = error_get_last();
            throw new HttpRequestException($error['message']);
        }
        return $data;
       

	}
}
