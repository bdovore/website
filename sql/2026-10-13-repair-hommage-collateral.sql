-- Hommage collatéral : réparation conditionnelle après fusion d'éditions.
-- Hypothèse à confirmer avec les SELECT ci-dessous sur la production :
-- l'album conservé existe toujours, mais son édition par défaut a été supprimée.
-- La copie locale du 21/09/2026 contient l'édition initiale 323494
-- (EAN 9782960039399), alors rattachée au tome 306839 / série 43577.
-- L'identifiant du tome conservé en série 63861 est lu dans la base cible.
-- Aucune restauration d'album, d'édition ou de collection n'est effectuée.
-- Exécuter d'abord la partie DIAGNOSTIC et vérifier les résultats.
-- Les tables historiques sont MyISAM : ROLLBACK ne protège pas cette écriture.
-- Conserver le résultat AVANT avant d'exécuter la partie REPARATION.

-- DIAGNOSTIC (lecture seule)
SELECT t.ID_TOME, t.ID_SERIE, t.TITRE,
       t.ID_EDITION AS edition_par_defaut,
       d.ID_EDITION AS edition_par_defaut_existante,
       d.ID_TOME AS tome_de_edition_par_defaut,
       e.ID_EDITION AS edition_disponible, e.EAN, e.IMG_COUV
FROM bd_tome AS t
LEFT JOIN bd_edition AS d ON d.ID_EDITION = t.ID_EDITION
LEFT JOIN bd_edition AS e ON e.ID_TOME = t.ID_TOME
WHERE t.ID_SERIE IN (63861, 43577)
ORDER BY t.ID_TOME, e.ID_EDITION;

-- Lire aussi l'édition indépendamment de l'existence de son tome.
SELECT ID_EDITION, ID_TOME, EAN, IMG_COUV
FROM bd_edition
WHERE ID_EDITION = 323494;

-- REPARATION : une seule référence, uniquement dans le cas attendu.
-- Exécuter ce bloc dans une même connexion SQL.
SET @hc_tome = NULL;
SET @hc_ancienne_edition = NULL;
SET @hc_candidats = 0;

SELECT COUNT(*), MAX(t.ID_TOME), MAX(t.ID_EDITION)
INTO @hc_candidats, @hc_tome, @hc_ancienne_edition
FROM bd_tome AS t
INNER JOIN bd_serie AS s ON s.ID_SERIE = t.ID_SERIE
INNER JOIN bd_edition AS e ON e.ID_TOME = t.ID_TOME
LEFT JOIN bd_edition AS d ON d.ID_EDITION = t.ID_EDITION
WHERE t.ID_SERIE = 63861
  AND e.ID_EDITION = 323494
  AND e.EAN = '9782960039399'
  AND d.ID_EDITION IS NULL;

SELECT 'AVANT' AS etape, @hc_candidats AS candidats,
       @hc_tome AS tome_a_reparer,
       @hc_ancienne_edition AS ancienne_edition_par_defaut,
       323494 AS nouvelle_edition_par_defaut;

UPDATE bd_tome AS t
INNER JOIN bd_serie AS s ON s.ID_SERIE = t.ID_SERIE
INNER JOIN bd_edition AS e ON e.ID_TOME = t.ID_TOME
LEFT JOIN bd_edition AS d ON d.ID_EDITION = t.ID_EDITION
SET t.ID_EDITION = e.ID_EDITION
WHERE @hc_candidats = 1
  AND t.ID_TOME = @hc_tome
  AND t.ID_SERIE = 63861
  AND t.ID_EDITION <=> @hc_ancienne_edition
  AND e.ID_EDITION = 323494
  AND e.EAN = '9782960039399'
  AND d.ID_EDITION IS NULL;

-- Attendu : 1 lors de la première réparation, 0 si déjà corrigé
-- ou si la base ne présente pas exactement le cas prévu.
SELECT ROW_COUNT() AS albums_corriges;

-- APRES : contrôle de la même jointure que les vues applicatives.
-- Attendu : le tome de la série 63861, avec l'édition 323494.
SELECT t.ID_TOME, t.ID_SERIE, s.NOM AS serie, t.TITRE,
       t.ID_EDITION, e.EAN, e.IMG_COUV
FROM bd_tome AS t
INNER JOIN bd_serie AS s ON s.ID_SERIE = t.ID_SERIE
INNER JOIN bd_genre AS g ON g.ID_GENRE = s.ID_GENRE
INNER JOIN bd_edition AS e
        ON e.ID_EDITION = t.ID_EDITION AND e.ID_TOME = t.ID_TOME
WHERE t.ID_SERIE = 63861 AND e.ID_EDITION = 323494;

-- Contrôles complémentaires : pas de modification automatique des collections
-- ni des statistiques tant que l'état de production n'a pas été examiné.
-- L'ancien UPDATE IGNORE pouvait laisser des références à l'édition supprimée
-- lorsqu'un membre possédait déjà l'édition conservée.
SELECT id_edition, COUNT(*) AS references_collection
FROM users_album
WHERE id_edition = 323494 OR id_edition = @hc_ancienne_edition
GROUP BY id_edition;

SELECT st.*, e.ID_TOME AS tome_reel, t.ID_SERIE AS serie_reelle
FROM bd_edition_stat AS st
INNER JOIN bd_edition AS e ON e.ID_EDITION = st.ID_EDITION
LEFT JOIN bd_tome AS t ON t.ID_TOME = e.ID_TOME
WHERE st.ID_EDITION = 323494;
