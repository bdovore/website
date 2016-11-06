<?php

class Sitemapindex extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $this->view->layout = "sitemapindex";
        $this->view->render();
    }
}