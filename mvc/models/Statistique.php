<?php

/**
 *
 * @author laurent
 *        
 */
class Statistique
{

    public $a_tableStatus = array();

    public $nbajout = 0;

    public $nbcorrect = 0;

    public $nbVisite = 0;

    public $nbEditionAttente = 0;

    public $nbNoteAlbum = 0;

    public $nbCommentAlbum = 0;

    public $nbNoteSerie = 0;

    public $nbCommentSerie = 0;

    public function __construct ()
    {}

    /**
     * requete rapide de statistique tables
     * evite l'utilisation du count(*)
     *
     * colonnes les plus interessantes
     * Rows = nombre de lignes
     * Update_time = derniere modification
     * Comment = commentaire de table (peut servir de libelle)
     *
     * @return multitype:array:stdClass
     */
    public function showTables ()
    {
        $resultat = Db_query("SHOW TABLE STATUS");
        $a_tableStatus = array();
        while ($obj = Db_fetch_object($resultat)) {
            $this->a_tableStatus[$obj->Name] = $obj;
        }
        return $a_tableStatus;
    }

    /**
     * Nombre d'éditions d'album en attente d'ajout
     *
     * @return number
     */
    public function editionAttente ()
    {
        $this->nbEditionAttente = Db_CountRow("select * from users_album u 
                INNER JOIN bd_edition en ON en.id_edition = u.id_edition 
                where en.prop_status = 0");
        return $this->nbEditionAttente;
    }

    /**
     * Nombre d'albums en attente d'ajout
     * Nombre d'albums en attente de correction
     *
     * @return stdClass
     */
    public function ajoutCorrection ()
    {
        // AJOUT & CORRECTION
        $resultat = Db_query("
        	select
        		sum(case when PROP_TYPE = 'AJOUT' then 1 else 0 end) nbajout,
        		sum(case when PROP_TYPE = 'CORRECTION' then 1 else 0 end) nbcorrect
        	from
        		users_alb_prop
        	where
        		status = 0
        	");
        if ($obj = Db_fetch_object($resultat)) {
            $this->nbajout = $obj->nbajout;
            $this->nbcorrect = $obj->nbcorrect;
        }
        return $obj;
    }

    /**
     * Nombre de visites de Bdovores
     *
     * @return stdClass
     */
    public function visites ()
    {
        // visites globales
        $resultat = Db_query("select sum(nb_connect) visite from users");
        
        if ($obj = Db_fetch_object($resultat)) {
            $this->nbVisite = $obj->visite;
        }
        return $obj;
    }

    /**
     * Nombre de notes d'albums
     * Nombre de commentaires d'albums
     *
     * @return stdClass
     */
    public function tomeComment ()
    {
        $resultat = Db_query("
        	select
        		sum(case when note is not null and note > 0 then 1 else 0 end) nbnote,
        		sum(case when comment is not null and comment <> '' then 1 else 0 end) nbcomment
        	from
        		users_comment
        	");
        if ($obj = Db_fetch_object($resultat)) {
            $this->nbNoteAlbum = $obj->nbnote;
            $this->nbCommentAlbum = $obj->nbcomment;
        }
        return $obj;
    }

    /**
     * Nombre de notes de séries
     * Nombre de commentaires de séries
     *
     * @return stdClass
     */
    function serieComment ()
    {
        $resultat = Db_query("
            select
        		sum(case when note is not null and note > 0 then 1 else 0 end) nbnote,
        		sum(case when comment is not null and comment <> '' then 1 else 0 end) nbcomment
        	from
        		serie_comment
        		");
        if ($obj = Db_fetch_object($resultat)) {
            $this->nbNoteSerie = $obj->nbnote;
            $this->nbCommentSerie = $obj->nbcomment;
        }
        return $obj;
    }
}
