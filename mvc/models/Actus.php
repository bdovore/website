<?php

/**
 *
 * @author laurent
 *        
 */
class Actus
{

    public function lastNews ($limit=10)
    {
        $requete = "SELECT * FROM news WHERE news_level>=5  ORDER BY News_id DESC LIMIT 0, " . intval($limit);
        $resultat = Db_query($requete);
        
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function lastCommentaires ($limit=5)
    {
        $requete = "
	    select
	    t.TITRE as TITRE_TOME,
	    t.ID_TOME,
	    max(c.DTE_POST) as dte,
	    en.IMG_COUV
	    from
	    bd_tome t INNER JOIN bd_edition en ON en.id_edition = t.id_edition,
	    users_comment c
	    where
	    t.id_tome = c.id_tome
	    and c.comment <> ''
	    group by titre, t.id_tome
	    order by dte desc limit 0," . intval($limit);
        $resultat = Db_query($requete);
        
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function lastSorties ($limit=5)
    {
        $requete = "
	    select
	    t.TITRE as TITRE_TOME,
	    t.ID_TOME,
	    en.IMG_COUV ,
	    en.DTE_PARUTION as DTE_PARUTION_EDITION
	    from
	    bd_tome t
	    INNER JOIN bd_edition en ON en.id_edition = t.id_edition
	    where
	    en.dte_parution <= CURDATE()
	    order by en.dte_parution desc
	    limit 0," . intval($limit);
        $resultat = Db_query($requete);
        
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function futurSorties ($limit=5)
    {
        $requete = "
	    select
	    t.TITRE as TITRE_TOME,
	    t.ID_TOME,
	    en.IMG_COUV ,
	    en.DTE_PARUTION as DTE_PARUTION_EDITION
	    from
	    bd_tome t
	    INNER JOIN bd_edition en ON en.id_edition = t.id_edition
	    where
	    en.dte_parution > CURDATE()
	    order by en.dte_parution
	    limit 0," . intval($limit);
        $resultat = Db_query($requete);
        
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function actuAir ()
    {
        // dernier topactu recharger si cree avant 0h00
        $file = BDO_DIR . "cache/actualite.html";
        if (! file_exists($file) or @filemtime($file) < mktime(0, 0, 0, date("m"), date("d"), date("Y")) or (@filesize($file) == 0)) {
            $size = $this->setTopActu($file);
        }
        // $this->set_var("ACTUAIR",file_get_contents($file));
        return file_get_contents($file);
    }

    public function lastAjout ()
    {
        // dernier ajout recharger toutes les 10min
        $file = BDO_DIR . "cache/lastajout.html";
        if (! file_exists($file) or (@filemtime($file) < (time() - 600)) or (@filesize($file) == 0)) {
            $size = $this->setLastAjout($file);
        }
        // $this->set_var("LASTAVIS",file_get_contents($file));
        return file_get_contents($file);
    }

    function setTopActu ($file)
    {
        $a_genre = array(
                'BD',
                'Mangas',
                'Comics'
        );
        // requete select de base pour les top 1 et 2
        
        $select_topactu = "
	SELECT
		t.TITRE as TITRE_TOME,
		t.ID_TOME,
		t.MOYENNE as MOYENNE_TOME,
		en.IMG_COUV,
		t.NB_VOTE as NB_VOTE_TOME,
		s.NOM as NOM_SERIE,
		s.ID_SERIE,
		g.ORIGINE as ORIGINE_GENRE
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
		t.TITRE as TITRE_TOME,
		t.ID_TOME,
		s.NOM as NOM_SERIE,
		s.ID_SERIE,
		g.ORIGINE as ORIGINE_GENRE,
		t.MOYENNE as MOYENNE_TOME,
		en.IMG_COUV,
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
	<div id="actu" class="right fond">
	<div class="middle_title">
	<h3>Actualité</h3>
	</div>
	';
        
        foreach ($a_genre as $genre) {
            $html .= '<div class="right"><div class="middle_content">' . $genre . '</div><br />';
            $filter = " and g.origine = '" . $genre . "' ";
            
            // actu
            $requete = $select_topactu . $filter . " " . $order_actu;
            $resultat = Db_query($requete);
            if ($obj = Db_fetch_object($resultat)) {
                 $html .= urlAlbum ($obj,'couvMedium');
                 $filter .= " and t.ID_TOME <> '" . $obj->ID_TOME . "' ";
                  
            }
            
            // air du temps
            $requete = $select_topair . $filter . $order_air;
            $resultat = Db_query($requete);
            if ($obj = Db_fetch_object($resultat)) {
                $html .= '&nbsp;' . urlAlbum ($obj,'couvMedium');
            }
            $html .= '
		</div>
		';
        }
        
        $html .= '</div>';

        //le cache généré pour le moment contient l'URL complète avec http(s)://
        //ça cause des problèmes pour les utilisateurs (~ 99%) qui sont en HTTP
        //et qui n'ont pas accepté le certificat de OVH pour bdovore (pas d'image).
        //Pour résoudre ça, il suffit de toujours avoir le cache en HTTP. 
        //Au pire, ceux qui utilisent HTTPS auront un simple avertissement du genre 
        //"attention il y a du contenu non-sécurisé sur cette page".
        $html = preg_replace("/https:/i", "http:", $html);

        return file_put_contents($file, $html);
    }

    function setLastAjout ($file)
    {
        
        // insertion des 10 derniers albums ajoutés
        $html = '
	<div id="last" class="right fond">
	<div class="middle_title">
	<h3><a href="' . BDO_URL . 'leguide?rb_mode=6&rb_list=album&submitGuide=Envoyer">Derniers ajouts</a>
	<a href="' . BDO_URL . 'rss.php">
	<img src="' . BDO_URL_IMAGE . 'site/feed.png" style="border: 0;" alt="logo fil rss" title="Suivez l\'actualité des ajouts d\'albums sur le site grace à ce fil rss" />
	</a></h3>
	</div>
		<div class="cadre1" style="margin:3px 3px 3px 3px ;">
';
        $requete = "SELECT
                bd_tome.TITRE as TITRE_TOME, 
                bd_tome.ID_TOME, 
                bd_tome.MOYENNE 
                from bd_tome 
INNER JOIN (SELECT 
MAX(ID_TOME) ID_TOME
from bd_tome 
GROUP BY ID_SERIE
ORDER BY id_tome DESC LIMIT 0,10) lasttome USING (ID_TOME)";
        $resultat = Db_query($requete);
        while ($obj = Db_fetch_object($resultat)) {
            $obj->TITRE_TOME = ' - '.$obj->TITRE_TOME;
            $html .=urlAlbum ($obj,'albTitle').'<br />';
        }
        $html .= '</div></div>';

        //cf. setTopActu()
        $html = preg_replace("/https:/i", "http:", $html);

        return file_put_contents($file, $html);
    }
}
