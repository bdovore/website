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
    public function Index ()
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
                        "title" => "Top des votes"
                ),
                array(
                        'mode' => 2,
                        "checked" => "",
                        "title" => "Les plus r&eacute;pandus"
                ),
                array(
                        'mode' => 3,
                        "checked" => "",
                        "title" => "Derni&egrave;rs avis de lecture"

                ),
                array(
                        'mode' => 4,
                        "checked" => "",
                        "title" => "Derni&egrave;res parutions"
                ),
                array(
                        'mode' => 5,
                        "checked" => "",
                        "title" => "À paraitre"
                ),
                array(
                        'mode' => 6,
                        "checked" => "",
                        "title" => "Derniers ajouts"

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
            if ($_GET['rb_mode'] == $chGuide['mode']) $a_chGuide[$key]['checked'] = 'checked';
        }

        // checkbox de type de rendu
        if (! isset($_GET['rb_list'])) $_GET['rb_list'] = 'album';

        switch ($_GET['rb_mode']) {

            case 1: // top des votes
                {
                    $title .= "le top des votes";
                    if ($_GET['rb_list'] == 'serie')
                    {
                        $this->loadModel('Serie');

                        $dbs_serie = $this->Serie->load('c', "
                            WHERE note_serie.NB_NOTE_SERIE > 5
                            ".($_GET['a_idGenre'] ? "AND bd_genre.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                           and bd_genre.ORIGINE = '".$filter_origine ."'
                           group by ID_SERIE ORDER BY (note_serie.MOYENNE_NOTE_SERIE*log(note_serie.NB_NOTE_SERIE)) DESC ".$limit);

                        $this->view->set_var('dbs_serie', $dbs_serie);
                    }
                    else
                    {
                        $this->loadModel('Tome');
                        $dbs_tome = $this->Tome->load('c', " 

                            WHERE note_tome.NB_NOTE_TOME> 5

                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.ORIGINE = '".$filter_origine ."'
                            ORDER BY (note_tome.MOYENNE_NOTE_TOME*log(note_tome.NB_NOTE_TOME)) DESC ".$limit);

                        $this->view->set_var('dbs_tome', $dbs_tome);
                    }

                    break;
                }

            case 2: // Les plus repandus
                {
                    $title .= "les plus répandus";
                    if ($_GET['rb_list'] == 'serie')
                    {
                        $this->loadModel('Serie');

                        $dbs_serie = $this->Serie->load('c', "

                            WHERE `bd_edition_stat`.`NBR_USER_ID_SERIE` > 100 
                            ".($_GET['a_idGenre'] ? "AND bd_genre.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                            and bd_genre.origine = '".$filter_origine ."'
                         GROUP BY ID_SERIE   ORDER BY `bd_edition_stat`.`NBR_USER_ID_SERIE` DESC ".$limit);

                        $this->view->set_var('dbs_serie', $dbs_serie);
                    }
                    else
                    {
                        $this->loadModel('Tome');

                        $dbs_tome = $this->Tome->load('c', "
                            WHERE `bd_edition_stat`.`NBR_USER_ID_TOME` > ".($_GET['a_idGenre'] ? 20 : 200)."
                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."'
                            ORDER BY `bd_edition_stat`.`NBR_USER_ID_TOME` DESC ".$limit);

                        $this->view->set_var('dbs_tome', $dbs_tome);
                    }

                    break;
                }

            case 3: // derniers commentaires
                {
                    $title .= "les derniers avis";
                    $_GET['rb_list'] = 'album';
                    
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
                    $title .= "les dernières parutions";
                    $_GET['rb_list'] = 'album';

                    $this->loadModel('Edition');

                    $dbs_tome = $this->Edition->load('c', "
                        WHERE bd_edition.`DTE_PARUTION`<= NOW() and bd_edition.`DTE_PARUTION` >= DATE_ADD(NOW(), INTERVAL -1 YEAR)
                        ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."'
                    ORDER BY bd_edition.`DTE_PARUTION` DESC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);

                    break;
                }

            case 5: // A paraitre
                {
                    $title .= "les albums à paraitre";
                    $_GET['rb_list'] = 'album';

                    $this->loadModel('Edition');

                    $dbs_tome = $this->Edition->load('c', "
                        WHERE bd_edition.`DTE_PARUTION`>'".date('Y-m-d')."'
                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                 and g.origine = '".$filter_origine ."'
                            ORDER BY bd_edition.`DTE_PARUTION` ASC ".$limit);

                    $this->view->set_var('dbs_tome', $dbs_tome);

                    break;
                }

            case 6: // Derniers ajouts
                {
                    $title .= "derniers ajouts dans la base";
                    if ($_GET['rb_list'] == 'serie')
                    {
                        $this->loadModel('Serie');

                        $dbs_serie = $this->Serie->load('c', "
                            WHERE 1
                            ".($_GET['a_idGenre'] ? "AND bd_genre.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                            and bd_genre.origine = '".$filter_origine ."'
                           group by id_serie ORDER BY `bd_edition_stat`.`ID_EDITION` DESC ".$limit);

                        $this->view->set_var('dbs_serie', $dbs_serie);
                    }
                    else
                    {
                        $this->loadModel('Tome');

                        $dbs_tome = $this->Tome->load('c', "
                            WHERE 1
                            ".($_GET['a_idGenre'] ? "AND g.ID_GENRE IN (" . Db_Escape_String(implode(',',$_GET['a_idGenre'])).")":'') ."
                                     and g.origine = '".$filter_origine ."'
                            ORDER BY `bd_edition_stat`.`ID_EDITION` DESC ".$limit);

                        $this->view->set_var('dbs_tome', $dbs_tome);
                    }

                    break;
                }

            case 7: // Derniers commentaires
                {
                    break;
                }
        }

        $this->view->set_var('PAGETITLE', $title);

        // checkbox de type de rendu
        $a_checkboxType['album'] = ($_GET['rb_list'] == 'album') ? 'checked' : '';
        $a_checkboxType['serie'] = ($_GET['rb_list'] == 'serie') ? 'checked' : '';

        $this->view->set_var('a_chGuide', $a_chGuide);
        $this->view->set_var('a_checkboxType', $a_checkboxType);

        $this->loadModel('Actus');

        $this->view->set_var(array(
                'ACTUAIR' => $this->Actus->actuAir(),
                'LASTAJOUT' => $this->Actus->lastAjout(),
                'NUM_PAGE' => $page
        ));
        $this->view->render();
    }
}

