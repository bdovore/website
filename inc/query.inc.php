<?php

// liste de requetes courante

// retourne l'id edition par defaut d'un tome
function idEditionByIdTome($id_tome) {
    global $DB;

    $DB->query ( "select id_edition from bd_tome WHERE id_tome=" . $DB->escape ( $id_tome ) );
    $DB->next_record ();

    return $DB->f ( "id_edition" );
}
// retourne l'id_tome d'une edition
function idTomeByIdEdition($id_edition) {
    global $DB;

    $DB->query ( "select id_tome from bd_edition WHERE id_edition=" . $DB->escape ( $id_edition ) );
    $DB->next_record ();

    return $DB->f ( "id_tome" );
}

// edition par defaut par id_tome
function q_editionDefaultByIdTome($id_tome) {
    return "
    SELECT
        t.id_tome,
        t.id_scenar,
        t.id_dessin,

        s.id_serie,
        s.id_genre,

        en.id_edition,
        en.dte_parution,
        en.flag_dte_parution,
        en.ean,
        en.isbn,
        en.img_couv,

        c.id_collection,
        c.id_editeur
    FROM
    bd_tome t
    INNER JOIN bd_serie s ON t.id_serie = s.id_serie

    INNER JOIN bd_edition en ON t.id_edition = en.id_edition
    INNER JOIN bd_collection c ON en.id_collection = c.id_collection
    WHERE
        t.id_tome = " . $id_tome;
}

// toutes editions par id_tome
function q_AllEditionByIdTome($id_tome, $addWhere = '') {
    return "
    SELECT
        en.id_edition,
        en.dte_parution,
        en.flag_dte_parution,
        en.flg_eo,
        en.flg_tt,
        en.img_couv,

        en.prop_status,

        c.id_collection,
        c.nom cnom,

        er.id_editeur,
        er.nom enom
    FROM
        bd_edition en
        INNER JOIN bd_collection c ON en.id_collection = c.id_collection
        INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
    WHERE
        en.id_tome = " . $id_tome . " " . $addWhere;
}

// recupere les donnees principales
function q_tome($a_where, $orderBy = '') {
    if (is_array ( $a_where )) {
        $where = "where " . implode ( " AND ", $a_where );
    } else {
        $where = "where " . $a_where;
    }

    return "
SELECT
    t.id_tome,
    t.titre,
    t.num_tome,
    t.prix_bdnet,
    t.flg_int,
    t.flg_type,
    t.histoire,
    t.nb_vote,
    t.moyenne,

    s.id_serie,
    s.nom s_nom,
    s.tri,
    s.flg_fini,
    s.nb_tome,
    s.nb_note,
    s.note,

    s.histoire histoire_serie,

    g.id_genre,
    g.libelle,

    en.id_edition,
    en.img_couv,
    en.ean,
    en.isbn,
    en.dte_parution,
    en.flag_dte_parution,

    en.comment,

    c.id_collection,
    c.nom cnom,

    er.id_editeur,
    er.nom enom,

    t.id_scenar,
    sc.pseudo as scpseudo,
    t.id_dessin,
    de.pseudo as depseudo,
    t.id_color,
    co.pseudo as copseudo,
    t.id_scenar_alt,
    sca.pseudo as scapseudo,
    t.id_dessin_alt,
    dea.pseudo as deapseudo,
    t.id_color_alt,
    coa.pseudo as coapseudo

from
    bd_tome t
    INNER JOIN bd_serie s ON t.id_serie = s.id_serie
    INNER JOIN bd_genre g ON s.id_genre = g.id_genre

    INNER JOIN bd_edition en ON t.id_edition = en.id_edition
    INNER JOIN bd_collection c ON en.id_collection = c.id_collection
    INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur

    LEFT JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
    LEFT JOIN bd_auteur de ON t.id_dessin = de.id_auteur
    LEFT JOIN bd_auteur co ON t.id_color = co.id_auteur
    LEFT JOIN bd_auteur sca ON t.id_scenar_alt = sca.id_auteur
    LEFT JOIN bd_auteur dea ON t.id_dessin_alt = dea.id_auteur
    LEFT JOIN bd_auteur coa ON t.id_color_alt = coa.id_auteur
    " . $where . "
    " . $orderBy;
}

//
// recupere les donnees principales d'une edition
function q_edition($a_where, $orderBy = '') {
    if (is_array ( $a_where )) {
        $where = "where " . implode ( " AND ", $a_where );
    } else {
        $where = "where " . $a_where;
    }

    return "
SELECT
    t.id_tome,
    t.id_edition as id_edition_default,
    t.titre,
    t.num_tome,
    t.prix_bdnet,
    t.flg_int,
    t.flg_type,
    t.histoire,
    t.nb_vote,
    t.moyenne,

    s.id_serie,
    s.nom s_nom,
    s.tri,
    s.flg_fini,
    s.nb_tome,
    s.nb_note,
    s.note,
    s.histoire histoire_serie,

    g.id_genre,
    g.libelle,

    en.id_edition,
    en.img_couv,
    en.ean,
    en.isbn,
    en.dte_parution,
    en.flag_dte_parution,

    en.comment,

    c.id_collection,
    c.nom cnom,

    er.id_editeur,
    er.nom enom,

    t.id_scenar,
    sc.pseudo as scpseudo,
    t.id_dessin,
    de.pseudo as depseudo,
    t.id_color,
    co.pseudo as copseudo,
    t.id_scenar_alt,
    sca.pseudo as scapseudo,
    t.id_dessin_alt,
    dea.pseudo as deapseudo,
    t.id_color_alt,
    coa.pseudo as coapseudo
from
    bd_edition en

    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
    INNER JOIN bd_serie s ON t.id_serie = s.id_serie
    INNER JOIN bd_genre g ON s.id_genre = g.id_genre

    INNER JOIN bd_collection c ON en.id_collection = c.id_collection
    INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur

    LEFT JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
    LEFT JOIN bd_auteur de ON t.id_dessin = de.id_auteur
    LEFT JOIN bd_auteur co ON t.id_color = co.id_auteur
    LEFT JOIN bd_auteur sca ON t.id_scenar_alt = sca.id_auteur
    LEFT JOIN bd_auteur dea ON t.id_dessin_alt = dea.id_auteur
    LEFT JOIN bd_auteur coa ON t.id_color_alt = coa.id_auteur

    " . $where . "
    " . $orderBy;
}
function countUserBy($type, $id) {
    global $DB;

    $query = '';
    switch ($type) {
        case "edition" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
            WHERE
                ua.id_edition=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "tome" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
            WHERE
                en.id_tome=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "collection" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
            WHERE
                en.id_collection=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "editeur" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                INNER JOIN bd_collection c ON en.id_collection = c.id_collection

            WHERE
                c.id_editeur=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "serie" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            WHERE
                t.id_serie=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "genre" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                INNER JOIN bd_serie s ON t.id_serie = s.id_serie
            WHERE
                s.id_genre=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "auteur" :
            {
                $query = "
            SELECT COUNT(DISTINCT(ua.user_id)) as nbr
            FROM
                users_album ua
                INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            WHERE
                t.id_scenar = " . $DB->escape ( $id ) . "
                or t.id_dessin = " . $DB->escape ( $id ) . "
                or t.id_scenar_alt = " . $DB->escape ( $id ) . "
                or t.id_dessin_alt = " . $DB->escape ( $id ) . "
                or t.id_color = " . $DB->escape ( $id ) . "
                or t.id_color_alt = " . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "tomeComment" :
            {
                $query = "
            SELECT COUNT(DISTINCT(uc.user_id)) as nbr
            FROM
                users_comment uc
            WHERE
                uc.id_tome=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "serieComment" :
            {
                $query = "
            SELECT COUNT(DISTINCT(sc.user_id)) as nbr
            FROM
                serie_comment sc
            WHERE
                sc.id_serie=" . $DB->escape ( $id ) . "
            ";
                break;
            }
    }
    if (! empty ( $query )) {
        $DB->query ( $query );
        $DB->next_record ();
        return $DB->f ( "nbr" );
    }
    return false;
}
function countTomeBy($type, $id) {
    global $DB;

    $query = '';
    switch ($type) {
        case "collection" :
            {
                $query = "
            SELECT COUNT(DISTINCT(t.id_tome)) as nbr
            FROM
                bd_edition en
                INNER JOIN bd_tome t ON en.ID_TOME=t.ID_TOME
            WHERE
                en.id_collection=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "editeur" :
            {
                $query = "
            SELECT COUNT(DISTINCT(t.id_tome)) as nbr
            FROM
                bd_edition en
                INNER JOIN bd_collection c ON en.id_collection = c.id_collection
                INNER JOIN bd_tome t ON en.ID_TOME=t.ID_TOME
            WHERE
                c.id_editeur=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "serie" :
            {
                $query = "
            SELECT COUNT(DISTINCT(t.id_tome)) as nbr
            FROM
                bd_tome t
            WHERE
                t.id_serie=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "genre" :
            {
                $query = "
            SELECT COUNT(DISTINCT(t.id_tome)) as nbr
            FROM
                bd_tome t
                INNER JOIN bd_serie s ON t.id_serie = s.id_serie
            WHERE
                s.id_genre=" . $DB->escape ( $id ) . "
            ";
                break;
            }
        case "auteur" :
            {
                $query = "
            SELECT COUNT(DISTINCT(t.id_tome)) as nbr
            FROM
                bd_tome t
            WHERE
                t.id_scenar = " . $DB->escape ( $id ) . "
                or t.id_dessin = " . $DB->escape ( $id ) . "
                or t.id_scenar_alt = " . $DB->escape ( $id ) . "
                or t.id_dessin_alt = " . $DB->escape ( $id ) . "
                or t.id_color = " . $DB->escape ( $id ) . "
                or t.id_color_alt = " . $DB->escape ( $id ) . "
            ";
                break;
            }
    }
    if (! empty ( $query )) {
        $DB->query ( $query );
        $DB->next_record ();
        return $DB->f ( "nbr" );
    }
    return false;
}