# Header et connexion flottante

Le layout principal conserve le logo, le fond, les rubriques et le contenu des pages. La première ligne regroupe le logo, la recherche et le compte ; les rubriques et les liens d'aide occupent la seconde ligne.

Sur les écrans de 760 px ou moins, le logo reste visible, la recherche passe sous la première ligne et un bouton ouvre les rubriques. Le panneau de connexion devient un volet en bas de l'écran, défilable lorsque sa hauteur disponible diminue. La fermeture remet le focus sur le compte ; Échap ferme le panneau ou le menu. Le volet mobile garde la navigation au clavier dans le dialogue.

Le formulaire flottant utilise toujours `/compte/connexion`, la session PHP, la validation CSRF et les contrôles d'authentification du modèle `User`. En POST avec `account_login_form=floating` et l'en-tête `X-Requested-With: XMLHttpRequest`, la réponse JSON indique uniquement `authenticated`, `needs_pseudo` et `message`. Le navigateur affiche les erreurs dans le panneau et révèle le champ de pseudo au besoin, sans effacer les champs. Après succès, il recharge la page courante.

Les formulaires classiques et les API de l'app conservent leur comportement. Sans JavaScript, le bouton de compte mène à la page de connexion et les rubriques mobiles restent accessibles. Google et Apple utilisent le même fragment que la page de connexion et restent masqués jusqu'à configuration des fournisseurs. Aucun fournisseur n'est activé par ce changement.

## Vérifications

- `php -d xdebug.mode=off tests/floating_login_test.php` : réponses du contrôleur, états invité/connecté, échappement, CSRF et repli classique.
- `php -d xdebug.mode=off tests/mobile_account_mysql_test.php --legacy-only` : vrai modèle `User` et vrai contrôleur, succès et refus CSRF du formulaire flottant sans tables mobiles.
- `php -d xdebug.mode=off tests/mobile_account_mysql_test.php` : compatibilité des sessions et de l'API mobile.
- Vérification dans le navigateur sur le vrai layout avec des données fictives et sans base : ordinateur, 390 × 844, 320 × 568 et 320 × 350 ; menu, recherche avec suggestions, saisie partagée, erreur, succès, état connecté et fermeture au clavier. L'affichage des boutons sociaux a été contrôlé avec une configuration factice réservée à l'aperçu ; aucune authentification externe n'a été déclenchée.

Le clavier logiciel réel et les fournisseurs réels restent à vérifier sur les appareils/en préproduction avant publication.

Les rubriques utilisent une police de 16 px, y compris aux largeurs intermédiaires. « Mon profil » s'ouvre dans Fancybox sur ordinateur, avec fermeture préalable du panneau du compte. Le rendu autonome de `/compte` utilisé par l'app est conservé ; le chemin existant `?mobile=T` garde le layout du site dans le navigateur mobile, avec un cadre de profil responsive et un bouton de compte visible.

La rubrique courante est déterminée par `inc/navigation.php` à partir du contrôleur résolu par le routeur, indépendamment de son action et de l'URL réécrite. Les fiches album, auteur, série et collection éditoriale appartiennent à Browser ; toutes les actions de `Leguide` au Guide BD ; les contrôleurs `Admin*` et les outils d'administration à Admin. Le profil et les pages de collection restent dans Ma Collection. Le header indique une seule rubrique avec `aria-current="true"`, dès le HTML initial et sans JavaScript. Les conditions d'affichage de l'onglet Admin restent inchangées. Vérification : `php tests/navigation_test.php`.
