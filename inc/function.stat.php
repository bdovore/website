<?php
function setTopActu($DB, $file) {
    $a_genre = array (
            'BD',
            'Mangas',
            'Comics'
    );
    // requete select de base pour les top 1 et 2

    $select_topactu = "
    SELECT
        t.titre,
        t.id_tome,
        t.moyenne,
        en.img_couv,
        t.moyenne,
        t.nb_vote,
        s.nom,
        s.id_serie,
        g.origine
    FROM
        bd_tome t
        INNER JOIN bd_edition en ON en.id_edition = t.id_edition
        INNER JOIN bd_serie s ON s.id_serie = t.id_serie
        INNER JOIN bd_genre g ON s.id_genre = g.id_genre
    WHERE
        en.dte_parution >= DATE_SUB(NOW(),INTERVAL 2 MONTH)
    ";

    $order_actu = " ORDER BY t.moyenne desc, en.dte_parution DESC LIMIT 0,1";

    // requete select de base pour dans l'air
    $select_topair = "
    SELECT
        t.titre,
        t.id_tome,
        s.nom,
        s.id_serie,
        g.origine,
        t.moyenne,
        en.img_couv,
        count(*) nb
    FROM
        users_album ua
        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON t.id_serie=s.id_serie
        INNER JOIN bd_genre g ON s.id_genre=g.id_genre
    WHERE
        ua.date_ajout >= DATE_SUB(NOW(),INTERVAL 1 MONTH)
        and en.dte_parution >= DATE_SUB(NOW(),INTERVAL 3 MONTH)
        ";

    $order_air = "
    GROUP BY t.id_tome
    ORDER BY nb DESC, IFNULL(ua.date_achat,ua.date_ajout) DESC
    LIMIT 0,1";

    $html = '
<div id="actu" class="right" style="position: relative; z-index: 10;">
   <div class="fond"></div>
    <div class="middle_title">
    <h3>Actualité</h3>
    </div>

    ';

    foreach ( $a_genre as $genre ) {
        $html .= '<div class="right"><div class="middle_content">' . $genre . '<br />';
        $filter = " and g.origine = '" . $genre . "' ";

        // actu
        $select = $select_topactu . $filter . " AND t.id_tome<>'" . $DB->f ( "id_tome" ) . "' " . $order_actu;
        $DB->query ( $select );
        if ($DB->next_record ()) {
            $html .= '
            <a href="' . getAlbumUrl ( $DB->f ( "id_tome" ) ) . '" title="' . stripslashes ( $DB->f ( "nom" ) ) . ' - ' . stripslashes ( $DB->f ( "titre" ) ) . '">
            <img src="' . BDO_URL_COUV . $DB->f ( "img_couv" ) . '" alt="' . stripslashes ( $DB->f ( "titre" ) ) . '" class="couv-actu" />
            </a>
            ';
        }

        // air du temps
        $select = $select_topair . $filter . $order_air;
        $DB->query ( $select );
        if ($DB->next_record ()) {

            $html .= '&nbsp;
            <a href="' . getAlbumUrl ( $DB->f ( "id_tome" ) ) . '" title="' . stripslashes ( $DB->f ( "nom" ) ) . ' - ' . stripslashes ( $DB->f ( "titre" ) ) . '">
            <img src="' . BDO_URL_COUV . $DB->f ( "img_couv" ) . '" alt="' . stripslashes ( $DB->f ( "titre" ) ) . '" class="couv-actu" />
            </a>
            ';
        }
        $html .= '
        </div>
        </div>
        ';
    }

    $html .= '
    </div>
    ';

    return file_put_contents ( $file, $html );
}
