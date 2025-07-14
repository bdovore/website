<?php


class Sponsors extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $this->view->set_var('json', json_encode($this->getSponsorDefinitions()));
        $this->view->layout = "ajax";
         $this->view->render();
    }
    
    public function getSponsorDefinitions(): array {
    return [
        [
            'label' => 'BDfugue',
            'title' => "Achetez sur BDfugue !",
            'logo' => BDO_URL_IMAGE . "bdfugue.png",
            'patterns' => [
                'ean' => "https://www.bdfugue.com/a/?ref=295&ean={ean}",
                'title' => "https://www.bdfugue.com/catalogsearch/result/?ref=295&q={title}"
            ]
        ],
        [
            'label' => 'Amazon',
            'title' => "Achetez sur Amazon !",
            'logo' => BDO_URL_IMAGE . "amazon%20blanc.jpg",
            'patterns' => [
                'isbn' => "https://www.amazon.fr/exec/obidos/ASIN/{isbn}/bdovorecom-21/",
                'title' => "https://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword={title}&mode=books-fr"
            ]
        ],
        [
            'label' => 'Recyclivre',
            'title' => "Achetez sur Recyclivre !",
            'logo' => BDO_URL_IMAGE . "site/recyclivre.png",
            'patterns' => [
                'ean' => "https://www.recyclivre.com/search?q={ean}&utm_source=affiliation&utm_medium=affilae&utm_campaign=Bdovore#ae132",
                'title' => "https://www.recyclivre.com/search?filter%5Btaxon%5D=1151&q={title}&utm_source=affiliation&utm_medium=affilae&utm_campaign=Bdovore#ae132"
            ]
        ],
        [
            'label' => 'Budule',
            'title' => "Achetez d'occasion sur Budule !",
            'logo' => BDO_URL_IMAGE . "budule.png",
            'patterns' => [
                'title' => "https://www.budule.fr/recherche-bd?series={title}"
            ]
        ]
    ];
}
}
