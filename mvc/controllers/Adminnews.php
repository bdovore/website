<?php

/*
 * @author : Tom
 * Contrôleur pour l'ajout, la consultation, l'édition des news
 *
 */

class Adminnews extends Bdo_Controller {

    public function Index() {
        /*
         * Liste des news
         */
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            $status = getVal("status");
            $newsid = getVal("newsid");

            $this->loadModel("News");
// LISTE LES NEWS
            if ($act == "") {
                $dbs_news = $this->News->load("c", "");
                $this->view->set_var(array(
                    "PAGETITLE" => "Administration des news",
                    "dbs_news" => $dbs_news));
                $this->view->render();
            }

//SUPPRESSION DE NEWS
            elseif ($act == "supprim") {
                if ($status == "ok" and issetNotEmpty($newsid)) {//supreesion de la news
                    $this->News->add_dataPaste("news_id", $newsid);
                    $this->News->delete();

                    //rouvre la page
                    echo GetMetaTag(2, "La news a &eacute;t&eacute; effac&eacute;e", (BDO_URL . "adminnews"));
                    exit;
                } else {
                    // affiche la confirmation de la demande d'effacement
                    echo 'Etes-vous s&ucirc;r de vouloir effacer la news n&deg;' . $newsid . '  ?   <a href="adminnews?act=supprim&newsid=' . $newsid . '&status=ok">oui</a>
          - <a href="javascript:history.go(-1)">non</a>';
                    exit();
                }
            }
        } else {
            echo "Vous n'avez pas acces à cette page.";
            exit();
        }
    }

    public function editNews() {
        /*
         * Ajout ou modification d'une news
         */
        if (User::minAccesslevel(1)) {
            $this->loadModel("News");
            $status = getVal("status");
            $newsid = getVal("newsid",0);
            if ($status == "ok") {
                /*
                 * Enregistrement de la news
                 */
                $newsid = postVal("newsid",0);
                $this->News->set_dataPaste(array(
                    "news_titre" => postVal("txttitre"),
                    "news_text" => postVal("txtcontent"),
                    "news_level" => "5"
                ));
                if ($newsid > 0) {
                    // cas mise à jour
                     $this->News->add_dataPaste("news_id", $newsid);
                } else {
                    // cas création
                    $this->News->set_dataPaste(array(
                    "news_posteur" => $_SESSION["userConnect"]->username,
                    "USER_ID" => $_SESSION["userConnect"]->user_id,
                        "news_date" => date('d/m/Y H:i:s')
                        ));
                }
                $this->News->update();
                echo GetMetaTag(1, "Enregistrement effectu&eacute;", BDO_URL."adminnews/editnews?newsid=".$this->News->news_id);
                exit;
            }

            if ($newsid > 0) {
                $this->News->add_dataPaste("news_id", $newsid);
                $this->News->load();
                $this->view->set_var(array(
                    "titrenews" => $this->News->news_titre,
                    "txtnews" => $this->News->news_text,
                    "newsid" => $newsid
                ));
            }
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

}

?>
