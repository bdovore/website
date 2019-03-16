<?php

/**
 *
 * @author laurent
 *
 */

class Leguide extends Bdo_Controller
{

    /**
     */
    public function Index () {
        
            header('Status: 301 Moved Permanently', false, 301);
            header('Location: '.BDO_URL."leguide/avis");

    }
    
    public function Avis () {
        $this->Render();
    }
    
    public function Sorties_bd () {
        $this->Render(4);
    }
    
    public function Actualites_bd () {
        $this->Render(7);
    }
    
    public function Prochaines_sorties_bd () {
        $this->Render(5);
    }
    
     public function Ajouts () {
        $this->Render(6);
    }
    
    public function Top_bd () {
        $this->Render(1);
    }
    
    public function Incontournables () {
        $this->Render(2);
    }
    
            
    private function Render ($mode = 3)
    {
        // liste des genres
        $origine = getVal("origine",1);
        $this->loadModel('Genre');
        switch ($origine) :
            case 1 :
                $dbs_genre = $this->Genre->bd();
                $filter_origine = "BD";
                break;
            case 2 :
                $dbs_genre = $this->Genre->mangas();
                $filter_origine = "Mangas";
                break;
            case 3 :
                $dbs_genre = $this->Genre->comics();
                $filter_origine = "Comics";
                break;
            default :
                $dbs_genre = $this->Genre->all();
                $filter_origine = "BD";

        endswitch;

        $page = getValInteger('page',1);
        $limit = "LIMIT ".(($page-1)*20).",20";
        //if (! isset($_GET['a_idGenre'])) $_GET['a_idGenre'][] = $dbs_genre->a_dataQuery[0]->ID_GENRE;

        $this->view->set_var('dbs_genre', $dbs_genre);
        $this->view->set_var('origine', $origine);

        $a_chGuide = array(
                array(
                        'mode' => 1,
                        "checked" => "",
                        "title" => "Top des Meilleurs Albums BD, Mangas, Comics"
                ),
                array(
                        'mode' => 2,
                        "checked" => "",
                        "title" => "Les Séries BD, Comics, Mangas Incontournables"
                ),
                array(
                        'mode' => 3,
                        "checked" => "",
                        "title" => "Tous les Avis BD, Comics, Mangas"

                ),
                array(
                        'mode' => 4,
                        "checked" => "",
                        "title" => "Derni&egrave;res Parutions BD, Comics, Mangas"
                ),
                array(
                        'mode' => 5,
                        "checked" => "",
                        "title" => "BD, Comics, Mangas À paraitre"
                ),
                array(
                        'mode' => 6,
                        "checked" => "",
                        "title" => "Derniers ajouts sur Bdovore"

                ),
             array(
                        'mode' => 7,
                        "checked" => "",
                        "title" => "Actualités BD, Comics, Mangas"

                ),
               /* array(
                        'mode' => 6,
                        "checked" => "",
                        "title" => "Derniers commentaires"
                )*/
        );

        $title = "Le Guide BD Bdovore : " ;

        // checkbox de recherche
        if (! isset($_GET['rb_mode'])) $_GET['rb_mode'] = 3;

        foreach ($a_chGuide as $key => $chGuide) {
            if ($mode == $chGuide['mode']) {
                $a_chGuide[$key]['checked'] = 'checked';
                $title.= $a_chGuide[$key]["title"];
            }
        }

        

        switch ($mode) {

            case 1: // top des votes
                {
                               

                    $this->loadModel('Tome');
                    $dbs_tome = $this->Tome->load('c', "

                        WHERE note_tome.NB_NOTE_TOME> 5

                        ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                             and g.ORIGINE = '".$filter_origine ."'
                        ORDER BY (note_tome.MOYENNE_NOTE_TOME*log(note_tome.NB_NOTE_TOME)) DESC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);


                    break;
                }

            case 2: // Les plus repandus
                {
                   
                    $this->loadModel('Serie');

                    $dbs_serie = $this->Serie->load('c', "

                        WHERE `bd_edition_stat`.`NBR_USER_ID_SERIE` > 100
                        ".($_GET['a_idGenre'] ? "AND bd_genre.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                        and bd_genre.origine = '".$filter_origine ."'
                     GROUP BY ID_SERIE   ORDER BY `bd_edition_stat`.`NBR_USER_ID_SERIE` DESC ".$limit);

                    $this->view->set_var('dbs_serie', $dbs_serie);


                    break;
                }

            case 3: // derniers commentaires
                {
                   
                    $this->loadModel('Comment');

                    $dbs_comment = $this->Comment->load('c', "
                      WHERE   c.comment <> ''
                      ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                        and g.origine = '".$filter_origine ."'
                        ORDER BY c.`DTE_POST` DESC ".$limit);

                    $this->view->set_var('dbs_comment', $dbs_comment);
                    break;
                }

            case 4: // Dernieres parutions
                {
                   
                    $this->loadModel('Edition');

                    $dbs_tome = $this->Edition->load('c', "
                        WHERE bd_edition.`DTE_PARUTION`<= NOW() and bd_edition.`DTE_PARUTION` >= DATE_ADD(NOW(), INTERVAL -1 YEAR)
                        ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."' and bd_edition.PROP_STATUS=1
                    ORDER BY bd_edition.`DTE_PARUTION` DESC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);

                    break;
                }

            case 5: // A paraitre
                {
                   
                    $this->loadModel('Edition');

                    $dbs_tome = $this->Edition->load('c', "
                        WHERE bd_edition.`DTE_PARUTION`>'".date('Y-m-d')."'
                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."' and bd_edition.PROP_STATUS=1
                            ORDER BY bd_edition.`DTE_PARUTION` ASC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);

                    break;
                }

            case 6: // Derniers ajouts
                {
                       $this->loadModel('Tome');

                        //this doesn't use the cache in bd_edition_stat (seems light though)
                        $where = " WHERE";

                        if ($_GET['a_idGenre']) {
                            $where .= " g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])) . ") AND";
                        }

                        $where .= " g.origine = '" . $filter_origine . "'";

                        $order = " ORDER BY bd_tome.id_tome DESC ";

                        $dbs_tome = $this->Tome->load('c', $where .$order . $limit);
                        //this uses the cache:
//"
//                            WHERE 1
//                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
//                                     and g.origine = '".$filter_origine ."'
//                            ORDER BY `bd_edition_stat`.`ID_EDITION` DESC ".$limit);

                        $this->view->set_var('dbs_tome', $dbs_tome);


                    break;
                }

            case 7: // Derniers commentaires
                {
                   
                    $this->loadModel('Edition');

                    $dbs_tome = $this->Edition->load('c', "
                        WHERE bd_edition.`DTE_PARUTION`> date_add(now(),INTERVAL -6 MONTH)
                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."' and bd_edition.PROP_STATUS=1
                            ORDER BY `NBR_USER_ID` DESC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);
                    break;
                }
        }

        $this->view->set_var('PAGETITLE', $title);        
        $this->view->set_var('a_chGuide', $a_chGuide);       

        $this->loadModel('Actus');
        $this->view->addPhtmlFile("leguide/index","BODY");
        $this->view->set_var(array(
                'ACTUAIR' => $this->Actus->actuAir(),
                'LASTAJOUT' => $this->Actus->lastAjout(),
                'NUM_PAGE' => $page
        ));
        $this->view->render();
    }
}

