<?php
/*
 * ***************************************************** Class QueryAlbum Cette
 * class permet de construire une requete générique, afin de ne pas mettre du
 * code select partout dans les pages La fonction n'execute pas le code, mais
 * renvoie le SQL à executer. TODO : - faire un objet plus générique qui execute
 * le SQL et renvoie un tableau de valeur directement Utilisation : 1.
 * Initialiser un objet QueryAlbum (on peut faire des class static en php ?) 2.
 * Par défaut, la requete se fait sur la base album. On peut la faire sur la
 * base utilisateur via la fonction setUserMode qui prend en entrée
 * l'identifiant de l'utilisateur. 3. Définir le mode de recherche pour les
 * filtres via la fonction setSearchMode 4. Définir les filtres : - filterAuteur
 * : filtre sur les auteur - filterTitle : filtre sur le titre de la série -
 * etc... 5. Définir le résultat attendu : liste de valeur ou simple comptage
 * via setMode
 */
class QueryAlbum {

    // variable qui va contenir le filtre de la clause where
    var $filter = "";

    // variable qui donne le nombre de filtre
    var $nb_filter = 0;

    // variable qui va contenir la clause FROM
    var $from = "
    FROM
    bd_tome t
    INNER JOIN bd_edition e ON t.id_edition=e.id_edition
    INNER JOIN bd_collection col ON e.id_collection=col.id_collection
    INNER JOIN bd_editeur ed ON col.id_editeur=ed.id_editeur
    INNER JOIN bd_serie ser ON t.id_serie=ser.id_serie
    INNER JOIN bd_auteur s ON t.id_scenar=s.id_auteur
    INNER JOIN bd_auteur d ON t.id_dessin=d.id_auteur
    ";

    // variable qui va contenir la clause WHERE
    var $where = "
    WHERE 1
    ";

    // variable qui va contenir la clause ORDER BY
    var $order = "
    ORDER BY ser.nom, t.num_tome, t.titre
    ";
    var $user_mode = false;
    var $and_or = "or";
    var $prim_filter = "";

    // $search_mode : définit le mode pour les filtres :
    // 1 (par défaut ) "contient" : les filtres fonctionnent sur le mode like
    // '%filtre%'
    // 2 "commence par": 'filtre%'
    // 3 : expression exact : = filtre
    // Note : ne fonctinne que pour les recherches sur les titres et auteurs
    var $search_mode = 1;
    function setSearchMode($mode) {
        $this->search_mode = $mode;
    }
    function setAndOr($and) {
        $this->and_or = $and;
    }
    function filterTitle($title) {
        if ($this->nb_filter > 0) {
            $and = ' ' . $this->and_or . ' ';
        } else {
            $and = ' and (';
        }
        switch ($this->search_mode) {
            case 1 :
                $this->filter .= $and . "( LCASE(t.titre) like LCASE('%" . $title . "%') )";
                break;
            case 2 :
                $this->filter .= $and . "( LCASE(t.titre) like LCASE('" . $title . "%') )";
                break;
            case 3 :
                $this->filter .= $and . "( LCASE(t.titre) like LCASE('" . $title . "') )";
                break;
            default :
                $this->filter .= $and . "( LCASE(t.titre) like LCASE('%" . $title . "%') )";
                break;
        }
        $this->nb_filter ++;
    }

    // filtre sur les auteurs (scenario ou dessin)
    function filterAuteur($auteur) {
        if ($this->nb_filter > 0) {
            $and = ' ' . $this->and_or . ' ';
        } else {
            $and = ' and (';
        }
        switch ($this->search_mode) {
            case 1 :
                $this->filter .= $and . " ( s.pseudo like '%" . $auteur . "%' or d.pseudo like '%" . $auteur . "%' )";
                break;
            case 2 :
                $this->filter .= $and . " ( s.pseudo like '" . $auteur . "%' or d.pseudo like '" . $auteur . "%' )";
                break;
            case 3 :
                $this->filter .= $and . " ( s.pseudo like '" . $auteur . "' or d.pseudo like '" . $auteur . "' )";
                break;
            default :
                $this->filter .= $and . " ( s.pseudo like '%" . $auteur . "%' or d.pseudo like '%" . $auteur . "%' )";
                break;
        }
        $this->nb_filter ++;
    }

    // filtre sur l'ISBN
    function filterISBN($isbn) {
        $this->where = "
        WHERE
        e.ISBN = '" . $isbn . "'
        ";
    }

    // Filtre sur le titre de la série
    function filterSerie($serie) {
        if ($this->nb_filter > 0) {
            $and = ' ' . $this->and_or . ' ';
        } else {
            $and = ' and (';
        }
        switch ($this->search_mode) {
            case 1 :
                $this->filter .= $and . " ( LCASE(ser.nom) like LCASE('%" . $serie . "%') )";
                break;
            case 2 :
                $this->filter .= $and . " ( LCASE(ser.nom) like LCASE('" . $serie . "%') )";
                break;
            case 3 :
                $this->filter .= $and . " ( LCASE(ser.nom) like LCASE('" . $serie . "') )";
                break;
            default :
                $this->filter .= $and . " ( LCASE(ser.nom) like LCASE('%" . $serie . "%') )";
                break;
        }
        $this->nb_filter ++;
    }
    function setUserMode($user_id) {
        $this->user_mode = true;
        $this->from = "
        FROM
            users_album u
            INNER JOIN bd_edition e ON u.id_edition=e.id_edition
            INNER JOIN bd_tome t ON e.id_tome=t.id_tome
            INNER JOIN bd_collection col ON e.id_collection=col.id_collection
            INNER JOIN bd_editeur ed ON col.id_editeur=ed.id_editeur
            INNER JOIN bd_serie ser ON t.id_serie=ser.id_serie
            INNER JOIN bd_auteur s ON t.id_scenar=s.id_auteur
            INNER JOIN bd_auteur d ON t.id_dessin=d.id_auteur
            LEFT OUTER JOIN users_comment c ON u.user_id=c.user_id AND t.id_tome=c.id_tome
        ";
        $this->where .= "
        and u.user_id = " . $user_id;
    }
    function setOrder($colonne) {
        $this->order = " ORDER BY " . $colonne;
    }
    function filterAchat($bool) {
        $this->prim_filter .= "  and ( u.flg_achat='" . $bool . "' )";
    }
    function filterNonComment() {
        if ($this->user_mode) {
            $this->where .= " and c.comment is null";
        }
    }
    function filterNonNote() {
        if ($this->user_mode) {
            $this->where .= " and (c.note is null or c.note=0)";
        }
    }
    function filterDedicace($bool) {
        if ($this->user_mode) {
            $this->where .= " and (flg_dedicace='$bool')";
        }
    }
    function filterPret() {
        $this->prim_filter .= " and ( u.flg_pret='O' )";
    }
    function getQuery($mode, $startRow, $maxRows) {
        // donne le corp de la requete de récupération des albums
        // $ mode :
        // - 1 : count(*)
        // - 2 : liste des champs
        $select = "select ";
        if ($mode == 1) {
            $select .= " count(*) total";
        } else {
            $select .= "
            t.id_tome,
            e.dte_parution,
            t.titre,
            s.pseudo p_scenar,
            t.id_scenar,
            d.pseudo p_dessin,
            t.id_dessin,
            ed.nom editeur,
            ser.nom serie,
            ser.id_serie,
            t.moyenne,
            t.num_tome,
            ser.flg_fini,
            t.flg_int,
            t.flg_type
            ";
            // mode utilisateur : on ajoute le id_edition
            if ($this->user_mode) {
                $select .= ", u.id_edition";
            }
        }
        $ret = $select . $this->from . $this->where;
        if ($this->nb_filter > 0) {
            $ret .= $this->filter . ")";
        }
        $ret .= $this->prim_filter;
        $ret .= $this->order;
        if ($maxRows > 0) {
            $ret = sprintf ( "%s LIMIT %d, %d", $ret, $startRow, $maxRows );
        }
        return $ret;
    }
}
