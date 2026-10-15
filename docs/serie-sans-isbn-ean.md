# Séries sans EAN / ISBN

Appliquer `sql/2026-10-14-add-serie-sans-isbn-ean.sql` avant de déployer le code.
La migration ajoute `bd_serie.FLAG_SANS_ISBN_EAN`, désactivé par défaut ; elle ne modifie aucune édition.

La case « Sans ean / isbn » est accessible uniquement sur la fiche série de l’administration.
À son activation, le formulaire de confirmation admin indique les nombres d’albums et d’éditions concernés.
La confirmation enregistre les champs de la fiche et ses liens, active le réglage et marque toutes les éditions
rattachées aux albums de la série, y compris les éditions secondaires et en attente. Les codes existants sont conservés.

Les formulaires d’ajout d’édition admin et membre, d’ajout d’album dans une série et de validation d’une proposition
reprennent ce réglage par défaut. Les formulaires de création recalculent le défaut quand une série ou un album
existant est choisi. La case de l’édition reste modifiable ; les règles habituelles de saisie ISBN/EAN restent applicables.

Un enregistrement ultérieur de la série déjà cochée ne relance pas la propagation et conserve les exceptions manuelles.
Décocher le réglage de la série désactive uniquement le défaut des futurs ajouts, sans modifier les éditions existantes.

## Vérifications

- `php tests/admin_http_test.php`
- `php tests/adminproposition_http_test.php`
- `node tests/isbn_ean_defaults_test.js`
- `ADMIN_TEST_DATABASE=admin_test_serie_sans_isbn_ean php tests/serie_sans_isbn_ean_test.php`

Le test MySQL utilise les variables `ADMIN_TEST_HOST`, `ADMIN_TEST_PORT`, `ADMIN_TEST_USER`,
`ADMIN_TEST_PASSWORD` et `ADMIN_TEST_SOCKET`. Il crée et supprime sa propre base jetable, et refuse d’écraser
une base existante. Il couvre l’absence d’écriture avant confirmation, la conservation des champs et liens,
la propagation limitée à la bonne série, la conservation des codes, les séries vides et la désactivation du défaut.
