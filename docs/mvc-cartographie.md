# Cartographie MVC BDovore

Ce document cartographie le répertoire `mvc/` du projet BDovore. Il complète `AGENTS.md` avec une lecture fonctionnelle du code applicatif.

## Vue D'Ensemble

`mvc/` contient l'application web principale:

- `controllers/`: orchestration HTTP, lecture des paramètres, droits, appels aux modèles, choix du layout.
- `models/`: accès aux tables et requêtes métier, principalement via `Bdo_Db_Line`.
- `views/layout/`: enveloppes de rendu (`default`, `iframe`, `ajax`, `json`, `rss`, `sitemap`, `admin`, `fiche`).
- `views/views_controllers/`: templates par contrôleur/action.
- `views/helpers/`: helpers de rendu pour fiches album, série, auteur, pagination et jQuery.

Le code représente environ 33k lignes sous `mvc/`. Les plus grosses zones sont l'administration, la collection utilisateur, le catalogue album/série, les propositions et les imports/exports.

## Domaines Fonctionnels

### Accueil, Guide Et Découverte

- `Accueil.php`: page d'accueil, dernières sorties, prochaines sorties, news et derniers commentaires.
- `Index.php`: variante historique/proche de l'accueil.
- `Leguide.php`: pages éditoriales de guide (`Avis`, `Sorties_bd`, `Actualites_bd`, `Top_bd`, `Incontournables`, etc.).
- `Discovery.php`: découverte d'albums à partir du modèle `Tome`.
- `Presentation.php`, `Tutoriel.php`, `Bdtheque.php`, `Mentionslegales.php`, `Contacts.php`: pages statiques ou semi-statiques.

Modèles principaux: `Actus`, `Tome`, `News`, `Comment`, `Genre`, `Serie`, `Edition`, `Statistique`.

### Catalogue BD

- `Album.php`: fiche album/édition, rendu iframe/fiche, données JSON album.
- `Seriebd.php`: fiche série, albums de la série, séries liées, état de collection pour utilisateur connecté.
- `Auteurbd.php`: fiche auteur et albums par rôle.
- `Collectionbd.php`: fiche collection éditeur et albums associés.
- `Browser.php`: navigation/recherche par genre, série, auteur, éditeur, collection, album.
- `Liste_auteur_bd.php` et `Liste_serie_bd.php`: annuaires alphabétiques et vues sitemap.
- `Images.php`: rendu/accès image de couverture.
- `Simil.php`: albums similaires.
- `Wikipediainfo.php`: proxy JSON vers Wikipedia.

Modèles principaux: `Tome`, `Edition`, `Serie`, `Auteur`, `Editeur`, `Collection`, `Genre`, `Groupeserie`, `Tome_simil`.

### Collection Utilisateur

- `Macollection.php`: coeur de la collection personnelle. Gère tableau de bord, ajout/suppression d'albums, ajout d'une série, étagères, auteurs, séries, futurs achats, activité, exclusions, séries à compléter, addition/valorisation, propositions et statistiques.
- `Guest.php`: consultation publique d'une collection, wishlist et avis d'un autre utilisateur.
- `Paramcarre.php`: paramétrage du carré/liste mise en avant.
- `Auteurfavoris.php`: liste d'auteurs favoris.
- `Import.php`: import dans la collection.
- `Export.php`: export de collection, dont génération de fichiers et PDF.

Modèles principaux: `Useralbum`, `User`, `Users_exclusions`, `Users_list_carre`, `Users_list_aut`, `User_album_prop`, `Tome`, `Edition`, `Auteur`, `Comment`.

### Comptes, Auth Et Accès

- `Compte.php`: profil, inscription, mot de passe oublié, reset password, identifiant oublié.
- `Auth.php`: authentification externe et token API (`Provider`, `Google`, `Twitter`, `Gettoken`).
- `Acl.php`: écrans ACL.

Modèles principaux: `User`, `PasswordResetToken`.

Les contrôleurs utilisent souvent `User::minAccesslevel(...)` ou `$_SESSION['userConnect']`. Les pages compte et certaines pages admin utilisent le layout `iframe`.

### Commentaires Et Social

- `Albumcomment.php`: lecture/écriture d'avis, confidentialité du commentaire, likes, signalements/review.

Modèles principaux: `Comment`, `Notetome`, `Useralbum`, `Social`, `Socialreview`.

### Propositions Et Contributions

- `Proposition.php`: formulaire utilisateur pour proposer un ajout ou une correction, recherche d'éléments, suivi/listing.
- `Adminproposition.php`: modération des propositions, validation des ajouts/corrections, fusion, commentaires de modération.
- Une partie du suivi utilisateur est aussi dans `Macollection::Proposition`.

Modèles principaux: `User_album_prop`, `Edition`, `Tome`, `Serie`, `Useralbum`, `Statistique`.

### Administration Du Référentiel

- `Admin.php`: contrôleur massif pour le référentiel BD. Gère ajout/édition d'albums, éditions, séries, auteurs, genres, éditeurs, collections, fusions, suppressions, renommages multi-utilisateurs, déplacement/couvertures/split d'édition.
- `Adminauteur.php`: recherche/liste admin auteur.
- `Adminnews.php`: news et types de news.
- `Adminproposition.php`: modération des contributions.
- `Controle.php`: contrôles de cohérence de la base.
- `Cache.php`: recalculs/cache de similarité et statistiques utilisateurs par tome/édition/série.

Modèles principaux: presque tout le référentiel (`Tome`, `Edition`, `Serie`, `Auteur`, `Genre`, `Editeur`, `Collection`) plus `Useralbum`, `User_album_prop`, `Users_exclusions`, `Users_list_carre`, `Comment`.

### API, JSON, Recherche Et Flux

- `Search.php`: autocomplete global pour séries, albums, auteurs, collections et membres.
- `Getjson.php`: endpoints JSON variés pour auteur, album, genre, éditeur, collection, série, collection utilisateur, exclusions, actualité, stats.
- `Sponsors.php`: définitions sponsor en JSON.
- `Rss.php`: flux RSS derniers albums, dernières sorties, sorties à venir.
- `Sitemapalbum.php`, `Sitemapindex.php`, `Liste_auteur_bd::SiteMap`, `Liste_serie_bd::SiteMap`: sitemaps XML.

Layouts principaux: `ajax.phtml`, `json.phtml`, `rss.phtml`, `sitemap.phtml`, `sitemapindex.phtml`.

## Modèles Et Tables

### Référentiel BD

- `Tome` -> `bd_tome`: albums/tomes, recherche, actualités utilisateur, albums à compléter, albums par auteur, renommage.
- `Edition` -> `bd_edition`: éditions, ISBN/couverture, recherche, rattachement collection/éditeur, remplacements après fusion.
- `Serie` -> `bd_serie`: séries, listes alphabétiques, séries du même auteur.
- `Auteur` -> `bd_auteur`: auteurs, recherche, JSON autocomplete, remplacement/fusion.
- `Editeur` -> `bd_editeur`: éditeurs.
- `Collection` -> `bd_collection`: collections éditoriales.
- `Genre` -> `bd_genre`: genres BD/manga/comics et stats liées.
- `Groupeserie` -> `groupe_serie`: liens entre séries.
- `Tome_simil`: similarités d'albums, sans `Bdo_Db_Line`.

### Utilisateurs Et Collection

- `User` -> `users`: compte, recherche utilisateurs, auth token, forum, suppression de données, droits.
- `Useralbum` -> `users_album`: table pivot collection utilisateur/édition, stats, valorisation, séries/auteurs possédés, ajouts, achats futurs.
- `Users_exclusions` -> `users_exclusions`: séries ou tomes exclus des complétions.
- `Users_list_carre` -> `users_list_carre`: liste d'albums mise en avant.
- `Users_list_aut` -> `users_list_aut`: auteurs favoris.
- `User_album_prop` -> `users_alb_prop`: propositions d'ajout/correction et stats de contribution.

### Avis, Notes, Social

- `Comment` -> `users_comment`: commentaires/avis.
- `Notetome` -> `note_tome`: notes de tome et recalcul de stats.
- `Social` -> `social_action`: likes sur commentaires.
- `Socialreview` -> `social_review`: commentaires à relire/signaler.

### Contenu Et Outillage

- `News` -> `news`: actualités.
- `Newstype` -> `news_type`: types de news.
- `PasswordResetToken` -> `password_reset_tokens`: reset password.
- `Actus`: agrégateur d'actualités sans `Bdo_Db_Line`.
- `Statistique`: statistiques et contrôles transverses sans `Bdo_Db_Line`.

## Rendu Et Conventions Observées

- Les templates sont généralement résolus par `controller/action` en minuscules dans `views/views_controllers`.
- Les actions n'utilisent pas toutes le suffixe `Action`; elles suivent plutôt le nom routé (`Index`, `majCollection`, `serieComplete`, etc.).
- Les pages HTML standard utilisent `default.phtml`.
- Les popups/iframes admin ou compte utilisent souvent `iframe.phtml`.
- Les endpoints AJAX historiques utilisent souvent `layout = "ajax"` avec une vue vide ou une vue qui affiche `$view->json`.
- `json.phtml` existe pour les réponses JSON avec `Content-Type: application/json`.
- `rss.phtml`, `sitemap.phtml` et `sitemapindex.phtml` enveloppent les flux XML.

## Points D'Entrée À Connaître

- Modifier la fiche album: commencer par `Album.php`, `Tome.php`, `Edition.php`, `views/views_controllers/album/index.phtml`, `views/helpers/fichealbum.php`.
- Modifier la fiche série: `Seriebd.php`, `Serie.php`, `Tome.php`, `Groupeserie.php`, `views/views_controllers/seriebd/index.phtml`, `views/helpers/ficheserie.php`.
- Modifier la collection personnelle: `Macollection.php`, `Useralbum.php`, `Users_exclusions.php`, vues `views/views_controllers/macollection/`, et JS `public/script/collection.js`.
- Modifier l'autocomplete: `Search.php`, `views/views_controllers/search/auto.phtml`, et le script inline du layout `default.phtml`.
- Modifier les endpoints JSON: `Getjson.php` ou le contrôleur spécialisé concerné, puis vérifier le layout `ajax`/`json` et la vue associée.
- Modifier les propositions utilisateur: `Proposition.php`, `Adminproposition.php`, `User_album_prop.php`, vues `proposition/` et `adminproposition/`.
- Modifier l'administration du référentiel: `Admin.php` et les modèles de référentiel concernés.
- Modifier imports/exports: `Import.php`, `Export.php`, `Useralbum.php`, `Tome.php`.

## Zones À Risque

- `Admin.php`, `Macollection.php`, `Getjson.php`, `Export.php`, `Import.php` et `Adminproposition.php` concentrent beaucoup de logique et ont un fort couplage aux modèles et aux vues.
- Plusieurs vues vides sont normales: elles servent de support à des réponses AJAX dont le contenu est produit par le layout ou par variables.
- Les noms de classes/fichiers peuvent varier en casse (`SerieBD`, `AuteurBD`, `GetJSON`, `CollectionBd`) alors que le routage normalise les segments d'URL.
- Le code mélange conventions anciennes (`addVar`, `flush`) et plus récentes (`set_var`, `render`).
- Certains modèles ne passent pas par `Bdo_Db_Line` (`Actus`, `Statistique`, `Tome_simil`).
- Le JavaScript applicatif est partagé et ancien; vérifier les effets de bord avant de modifier des fonctions globales.
