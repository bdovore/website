<?php

class Leguide extends Bdo_Controller
{
    private const RUBRIQUES = array(
        3 => ['avis', 'Avis', 'Derniers avis', 'Les derniers avis de lecture publiés par les membres.'],
        4 => ['sorties_bd', 'Dernières sorties', 'Dernières sorties', 'Les éditions déjà parues, classées de la plus récente à la plus ancienne.'],
        5 => ['prochaines_sorties_bd', 'À paraître', 'À paraître', 'Les prochaines parutions, de la date la plus proche à la plus lointaine.'],
        6 => ['ajouts', 'Ajouts au catalogue', 'Ajouts au catalogue', 'Les fiches ajoutées récemment à BDovore, quelle que soit la date de parution.'],
        7 => ['actualites_bd', 'Tendances', 'Tendances', 'Les éditions mises en avant selon les ajouts en collection et leur date de parution.'],
        1 => ['top_bd', 'Top BDovore', 'Top BDovore', 'Les albums classés selon leur note moyenne et le nombre de votes.'],
        2 => ['incontournables', 'Incontournables', 'Incontournables', 'Les séries les plus présentes dans les collections des membres.'],
    );

    private const RAYONS = array(1 => 'BD', 2 => 'Mangas', 3 => 'Comics');

    public function Index()
    {
        $mode = getValInteger('rb_mode', 3);
        $action = is_int($mode) ? (self::RUBRIQUES[$mode][0] ?? 'avis') : 'avis';
        $parametres = array_intersect_key($_GET, array_flip(['origine', 'a_idGenre', 'page']));
        $requete = http_build_query($parametres);
        header('Location: ' . BDO_URL . 'leguide/' . $action . ($requete !== '' ? '?' . $requete : ''), true, 301);
    }

    public function Avis()
    {
        $this->renderGuide(3);
    }

    public function Sorties_bd()
    {
        $this->renderGuide(4);
    }

    public function Actualites_bd()
    {
        $this->renderGuide(7);
    }

    public function Prochaines_sorties_bd()
    {
        $this->renderGuide(5);
    }

    public function Ajouts()
    {
        $this->renderGuide(6);
    }

    public function Top_bd()
    {
        $this->renderGuide(1);
    }

    public function Incontournables()
    {
        $this->renderGuide(2);
    }

    private function renderGuide(int $mode)
    {
        $origine = getValInteger('origine', 1);
        if (!is_int($origine) || !isset(self::RAYONS[$origine])) {
            $origine = 1;
        }
        $rayon = self::RAYONS[$origine];
        $this->loadModel('Genre');
        $genres = match ($origine) {
            2 => $this->Genre->mangas(),
            3 => $this->Genre->comics(),
            default => $this->Genre->bd(),
        };
        $genresRetenus = array_values(array_unique(array_intersect(
            (array) getValInteger('a_idGenre', []),
            array_column($genres->a_dataQuery, 'ID_GENRE')
        )));
        $colonneGenre = $mode === 2 ? 'bd_genre.ID_GENRE' : 'g.ID_GENRE';
        $filtreGenre = $genresRetenus ? ' AND ' . $colonneGenre . ' IN (' . implode(',', $genresRetenus) . ')' : '';

        $page = getValInteger('page', 1);
        if (!is_int($page) || $page < 1 || $page > intdiv(PHP_INT_MAX, 20)) {
            $page = 1;
        }
        $debut = ($page - 1) * 20;
        $limite = "LIMIT $debut,20";

        switch ($mode) {
            case 1:
                $this->loadModel('Tome');
                $resultats = $this->Tome->load('c', "
                    WHERE note_tome.NB_NOTE_TOME > 5 AND g.ORIGINE = '$rayon' $filtreGenre
                    ORDER BY (note_tome.MOYENNE_NOTE_TOME * LOG(note_tome.NB_NOTE_TOME)) DESC $limite");
                break;
            case 2:
                $this->loadModel('Serie');
                $resultats = $this->Serie->load('c', "
                    WHERE bd_edition_stat.NBR_USER_ID_SERIE > 100 AND bd_genre.ORIGINE = '$rayon' $filtreGenre
                    GROUP BY ID_SERIE ORDER BY bd_edition_stat.NBR_USER_ID_SERIE DESC $limite");
                break;
            case 3:
                $this->loadModel('Comment');
                $resultats = $this->Comment->load('c', "
                    WHERE c.COMMENT <> '' AND g.ORIGINE = '$rayon' $filtreGenre
                    ORDER BY c.DTE_POST DESC $limite");
                break;
            case 4:
                $this->loadModel('Edition');
                $resultats = $this->Edition->load('c', "
                    WHERE bd_edition.DTE_PARUTION <= NOW() AND bd_edition.DTE_PARUTION >= DATE_ADD(NOW(), INTERVAL -1 YEAR)
                    AND g.ORIGINE = '$rayon' AND bd_edition.PROP_STATUS = 1 $filtreGenre
                    ORDER BY bd_edition.DTE_PARUTION DESC $limite");
                break;
            case 5:
                $this->loadModel('Edition');
                $aujourdhui = date('Y-m-d');
                $resultats = $this->Edition->load('c', "
                    WHERE bd_edition.DTE_PARUTION > '$aujourdhui'
                    AND g.ORIGINE = '$rayon' AND bd_edition.PROP_STATUS = 1 $filtreGenre
                    ORDER BY bd_edition.DTE_PARUTION ASC $limite");
                break;
            case 6:
                $this->loadModel('Tome');
                $resultats = $this->Tome->load('c', "
                    WHERE g.ORIGINE = '$rayon' $filtreGenre
                    ORDER BY bd_tome.ID_TOME DESC $limite");
                break;
            case 7:
                $this->loadModel('Edition');
                $resultats = $this->Edition->load('c', "
                    WHERE bd_tome.ID_EDITION = bd_edition.ID_EDITION
                    AND bd_edition.DTE_PARUTION > DATE_ADD(NOW(), INTERVAL -6 MONTH)
                    AND g.ORIGINE = '$rayon' AND bd_edition.PROP_STATUS = 1 $filtreGenre
                    ORDER BY (15.0 * bd_edition_stat.NBR_USER_ID_EDITION / (DATEDIFF(NOW(), DTE_PARUTION) + 1)) DESC $limite");
                break;
        }

        $this->view->addCssFile('style/album-lists.css?v=20261118e');
        $this->view->addCssFile('style/leguide.css?v=20261118b');
        $this->view->addPhtmlFile('leguide/index', 'BODY');
        $this->view->set_var(array(
            'PAGETITLE' => self::RUBRIQUES[$mode][2] . " · $rayon · Le Guide BD",
            'mode' => $mode,
            'rubriques' => self::RUBRIQUES,
            'origine' => $origine,
            'rayons' => self::RAYONS,
            'genres' => $genres->a_dataQuery,
            'genresRetenus' => $genresRetenus,
            'resultats' => $resultats->a_dataQuery,
            'page' => $page,
        ));
        $this->view->render();
    }
}
