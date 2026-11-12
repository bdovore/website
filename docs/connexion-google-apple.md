# Connexion par e-mail, Google et Apple

Développé sur `codex/auth-profil`, sans merge de `master`. Ce lot complète `docs/authentification-mobile.md` et conserve le déploiement backend puis app.

## Comportement retenu

- Le pseudo reste obligatoire, unique et inchangé. L'e-mail devient un autre identifiant de connexion, pas un nouveau pseudo.
- Un pseudo littéral est prioritaire. Sinon, un e-mail associé à un seul compte permet la connexion avec le mot de passe de ce compte.
- Une adresse partagée demande une précision du pseudo. Une adresse inconnue reçoit le même message ; aucune liste de comptes n'est exposée avant authentification.
- Un premier essai par e-mail unique qui échoue reçoit aussi cette demande de précision, afin de ne pas révéler quelles adresses existent. Une fois le pseudo précisé, l'erreur d'identifiants reste générique.
- Une identité Google/Apple peut être associée à plusieurs pseudos. Un seul compte lié ouvre sa collection directement ; plusieurs comptes liés affichent le choix du pseudo.
- Le rattachement d'un compte existant demande son accès BDovore et déclenche une notification e-mail. Une adresse identique, même vérifiée par Google/Apple, ne suffit jamais à rattacher automatiquement un compte.
- Pour créer un compte social, l'utilisateur choisit explicitement un pseudo disponible et une adresse de contact. Les collections existantes ne sont ni fusionnées ni remplacées.
- Une adresse Gmail, Workspace vérifiée ou Apple vérifiée est acceptée comme contact. Pour une adresse Google tierce ou une autre adresse saisie, un code de confirmation reste obligatoire.
- Apple peut masquer l'adresse : la liaison utilise l'identifiant stable du fournisseur (`provider` + `sub`, après contrôle de l'émetteur), pas l'e-mail. Une modification d'e-mail Google/Apple ne remplace pas automatiquement l'e-mail BDovore.

Les nouvelles pages du site sont `/compte/connexion`, `/compte/inscription` et les parcours de confirmation/récupération. Les boutons sociaux quittent les éventuelles fenêtres iframe du site pour effectuer l'authentification au niveau principal du navigateur.

## Backend à déployer avant l'app

1. Installer les dépendances verrouillées avec le processus Composer habituel (`composer install --no-dev --prefer-dist --no-interaction`). Seule `firebase/php-jwt` 7.2.1 a été ajoutée ; les autres paquets n'ont pas été mis à jour.
2. Appliquer la migration du socle `sql/2026-11-12-mobile-account.sql` si elle ne l'est pas encore.
3. Appliquer `sql/2026-11-12-social-login.sql`. Elle crée les identités, associations et transactions OAuth, et élargit `users.email` à 254 caractères si nécessaire pour les adresses masquées Apple. Elle conserve les identifiants, la collation, le caractère nullable et la valeur par défaut de la colonne. Aucun index unique n'est ajouté sur l'e-mail.
4. Vérifier les index et la compatibilité de leur longueur avec le moteur/encodage réels avant l'élargissement, puis rafraîchir le cache de schéma MVC `*_schema.serial` selon la procédure du serveur.
5. Déployer les contrôleurs, vues, layout, CSS et configuration ensemble. Activer `BDO_MOBILE_API_ENABLED` après la migration du socle, comme décrit dans le guide précédent.
6. Configurer les fournisseurs puis activer `BDO_SOCIAL_ENABLED` dans `config/constante.php`. Cette configuration reste ignorée par Git. Les boutons sont cachés tant que leurs paramètres ne sont pas complets.

La connexion pseudo/e-mail ne dépend d'aucune table sociale et fonctionne aussi avec les fonctionnalités mobiles/sociales désactivées. `/auth/gettoken` garde ses noms de paramètres et de réponse. Un ancien client peut toujours saisir directement son pseudo ; le paramètre form-urlencoded facultatif `user_pseudo` permet également de préciser un e-mail partagé.

Pour `/apiauth/login`, le champ JSON `username` accepte désormais un pseudo ou un e-mail. En cas d'ambiguïté, l'erreur est `409 / pseudo_required` ; renvoyer le même identifiant avec le champ JSON `pseudo`. L'identité renvoyée contient toujours le vrai pseudo du compte.

La nouvelle app affiche ce champ de précision au besoin. Les boutons Google/Apple de ce lot sont réservés au site : aucune connexion sociale web n'est utilisée pour remplacer le login natif de l'app.

## Configuration Google

Dans Google Cloud, configurer l'écran de consentement et créer un client OAuth de type application Web. Déclarer l'URL de retour exacte :

```text
https://www.bdovore.com/authsocial/callback
```

Utiliser un client distinct pour une préproduction si elle a un autre domaine. Définir côté serveur :

```php
define('BDO_GOOGLE_CLIENT_ID', 'IDENTIFIANT_DU_CLIENT_WEB');
define('BDO_GOOGLE_CLIENT_SECRET', 'SECRET_DU_CLIENT_WEB');
```

Le parcours utilise le code d'autorisation, un `state`, un `nonce` et PKCE S256. Les seules informations demandées sont l'identité et l'e-mail (`openid email`). Les jetons sont vérifiés avec les clés publiques Google, l'émetteur, l'audience et les dates. Aucun jeton d'accès ou de rafraîchissement Google n'est conservé.

Référence : [OpenID Connect Google](https://developers.google.com/identity/openid-connect/openid-connect).

## Configuration Apple

Dans Apple Developer, associer le site à un App ID principal compatible Sign in with Apple, créer un Services ID, enregistrer le domaine et la même URL HTTPS de retour, puis créer une clé privée Sign in with Apple.

```php
define('BDO_APPLE_CLIENT_ID', 'SERVICES_ID_DU_SITE');
define('BDO_APPLE_TEAM_ID', 'TEAM_ID');
define('BDO_APPLE_KEY_ID', 'KEY_ID');
define('BDO_APPLE_PRIVATE_KEY_FILE', '/chemin/hors/public/AuthKey.p8');
```

La clé `.p8` doit être lisible uniquement par les opérateurs/PHP autorisés et rester hors du répertoire public. Le code refuse une clé située dans `public/`. Ne pas la commiter ni la placer dans une ressource de l'app.

Déclarer aussi l'adresse ou le domaine expéditeur utilisé par `bdovoreMail` pour le relais e-mail privé Apple, avec SPF/DKIM adéquats. Tester la réception d'un message sur une vraie adresse masquée avant activation publique.

Apple renvoie le code par POST (`form_post`). Une transaction OAuth stockée côté serveur et un cookie temporaire dédié `Secure`, `HttpOnly`, `SameSite=None` lient ce retour au navigateur initiateur, sans assouplir le cookie PHP du site (`SameSite=Lax`). Le code d'autorisation est échangé côté serveur ; la signature, l'émetteur, l'audience, l'expiration et le nonce sont vérifiés. Le secret client Apple est signé à la demande pour cinq minutes ; les jetons Apple ne sont pas persistés.

Références : [configuration Apple Web](https://developer.apple.com/help/account/capabilities/configure-sign-in-with-apple-for-the-web), [relais e-mail privé](https://developer.apple.com/documentation/signinwithapple/communicating-using-the-private-email-relay-service).

## Accès à l'app et récupération

Un compte créé via Google/Apple n'a pas de mot de passe BDovore connu. Après une connexion sociale datant de moins de cinq minutes, son profil permet de définir un premier mot de passe ; cette opération conserve les associations Google/Apple. Il peut alors se connecter dans l'app actuelle ou nouvelle avec son pseudo et ce mot de passe.

Une récupération de mot de passe par e-mail est différente : elle révoque les sessions et dissocie les fournisseurs du pseudo concerné. Cela évite qu'une association ajoutée à l'insu du propriétaire survive à sa récupération. Les autres pseudos liés au même Google/Apple restent associés et leurs collections sont conservées. Les fournisseurs peuvent ensuite être rattachés depuis le profil après nouvelle preuve d'accès.

La suppression d'un compte nettoie ses associations et supprime l'identité fournisseur si elle n'est plus utilisée par un autre pseudo. Les anciens retours OpenID 2.0 sont retirés : un profil contenant seulement un e-mail ou un pseudo ne constitue plus une authentification. Les anciennes pages `.php` n'acceptent plus de cookie de mot de passe et héritent uniquement d'une session moderne encore valide.

## Vérification et retour arrière

Les tests automatisés ne contactent pas Google, Apple, le forum ni SMTP. Ils utilisent de vraies signatures RSA/EC et des réponses contrôlées, ainsi que des bases MySQL aléatoires qu'ils créent et suppriment eux-mêmes.

```sh
php -d xdebug.mode=off tests/social_oidc_test.php
php -d xdebug.mode=off tests/social_account_mysql_test.php
php -d xdebug.mode=off tests/legacy_account_bridge_test.php
php -d xdebug.mode=off tests/account_pages_test.php
php -d xdebug.mode=off tests/mobile_account_mysql_test.php --legacy-only
php -d xdebug.mode=off tests/mobile_account_mysql_test.php
```

Avant publication : vérifier en préproduction les deux fournisseurs réels, l'annulation du consentement, les adresses masquées, l'association de deux collections, la récupération et l'accès dans l'app actuelle. Masquer `code`, `state`, cookies et jetons dans les journaux du serveur/proxy. Purger périodiquement les transactions OAuth expirées/consommées.

Pour suspendre uniquement les connexions sociales : désactiver `BDO_SOCIAL_ENABLED`, en conservant les tables et les paramètres. Les connexions pseudo/e-mail continuent. Aucun retour arrière destructif du schéma n'est requis. Ne pas réactiver les anciens retours OpenID ou les cookies de mot de passe.
