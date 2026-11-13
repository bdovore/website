# Authentification et profil natifs : déploiement en deux temps

Les changements PHP et React Native sont développés sur une branche `codex/auth-profil` dans chaque dépôt. Aucun merge ni déploiement n'est réalisé par cette livraison.

Le complément [connexion par e-mail, Google et Apple](connexion-google-apple.md) décrit les nouvelles pages du site, la migration sociale facultative et la configuration des fournisseurs. Le déploiement backend puis app reste inchangé.

## Temps 1 : backend compatible avec l'app actuelle

1. Sauvegarder la base et vérifier le schéma réel, en particulier `users.password_v2`, `users.API_TOKEN` et la clé unique de `users.username`.
2. Appliquer `sql/2026-11-12-mobile-account.sql` avec le processus habituel de migration. Le script peut être réappliqué : il crée seulement des tables absentes, sans supprimer ni convertir les données historiques.
3. Déployer les fichiers PHP, contrôleurs, layouts et vues ensemble. `config/mobile.php` désactive les nouvelles routes par défaut ; le site et `/auth/gettoken` restent disponibles sans les tables mobiles.
4. Après la migration, définir `BDO_MOBILE_API_ENABLED` à `true` dans la configuration d'environnement `config/constante.php`. Garder `config/mobile.php`, qui fournit les valeurs par défaut.
5. Vérifier que PHP reçoit l'en-tête `Authorization` derrière Apache/FastCGI ou le proxy. Le code lit `HTTP_AUTHORIZATION` et `REDIRECT_HTTP_AUTHORIZATION`. Ajouter si nécessaire la transmission de cet en-tête dans la configuration du serveur ; ne pas ajouter le jeton dans la query string.
6. Réaliser les vérifications ci-dessous avec une app actuelle non modifiée puis une build de test de la nouvelle app. Publier l'app seulement après leur réussite.

### Préconditions du schéma

Les comptes existants sont conservés dans `users`, y compris leurs identifiants. Le service respecte les longueurs réelles des colonnes e-mail, localisation et pseudo.

```sql
SHOW COLUMNS FROM users;
SHOW INDEX FROM users;
```

- `password_v2` doit exister et accepter au moins les 60 caractères d'un hash bcrypt ; la connexion actuelle du site utilisait déjà cette colonne.
- `password` doit accepter la sentinelle `migrated` ; `API_TOKEN` doit accepter les 32 caractères de l'empreinte MD5 historique. Le dump ancien du dépôt n'est pas une preuve du schéma de production.
- `username` doit avoir sa contrainte unique, afin de refuser deux inscriptions concurrentes au même pseudo.
- Les nouvelles tables sont en InnoDB et n'ont pas de clé étrangère vers `users`, qui peut encore être en MyISAM.
- Le cache de schéma du MVC doit décrire `password_v2` pour l'inscription web lorsque le nouveau service est désactivé. Le service mobile emploie directement des requêtes préparées et ne dépend pas de ce cache.

### Contrat historique préservé

`POST /auth/gettoken` conserve son corps form-urlencoded `user_login`, `user_password`, et sa réponse `Token`, `Timestamp`, `Error`.

Les jetons historiques déjà émis restent acceptés dans `API_TOKEN` ; aucune expiration générale n'est activée. Les nouveaux jetons de ce parcours gardent le format `user_id-hex`, mais la partie aléatoire est maintenant cryptographique. Leur empreinte reste dans `users.API_TOKEN`.

Les API `getjson`, collection, commentaires et préférences gardent leurs formats et timestamps. Elles acceptent également la nouvelle authentification Bearer, sans obliger l'ancienne app à changer son transport.

Une connexion moderne n'écrit jamais dans `users.API_TOKEN`. Une connexion historique ne révoque pas les sessions modernes. La déconnexion moderne révoque uniquement la session concernée.

### Corrections de sécurité appliquées aussi au site

- Le cookie `pass`, qui pouvait contenir le hash historique ou une sentinelle commune, n'authentifie plus personne. Les sessions PHP valides continuent à fonctionner ; certains utilisateurs devront se reconnecter dans leur navigateur. La mémorisation longue durée du site n'est pas remplacée dans ce lot.
- Les mutations du profil web passent par POST et un jeton CSRF. Changement de mot de passe et suppression redemandent le mot de passe actuel.
- Les nouveaux mots de passe utilisent bcrypt et une règle commune : au moins 6 caractères, au plus 72 octets ; espaces acceptés. Les mots de passe existants restent acceptés à la connexion.
- Lorsque la nouvelle API est activée, inscription, récupération et changement d'e-mail du site utilisent les mêmes codes que l'app. Les comptes existants ne sont pas obligés de confirmer rétroactivement leur e-mail.
- Les changements et réinitialisations de mot de passe révoquent les jetons historiques, les sessions modernes et les codes/liens de récupération. Les sessions web sont invalidées à leur prochaine requête par comparaison des identifiants de mot de passe.
- Un compte bloqué ou supprimé perd ses accès. Un Bearer invalide ne retombe jamais sur une session PHP valide.
- Les identités Bearer restent propres à la requête et ne créent pas de session web persistante. La configuration de débogage est désactivée pour les requêtes de compte ou contenant des accès API.

## Contrat de la nouvelle API

Corps JSON pour les POST ; réponses `{ "data": ..., "error": null }` ou `{ "data": null, "error": { "code": "...", "message": "..." } }`.

| Méthode et route | Entrée | Résultat |
| --- | --- | --- |
| POST `/apiauth/login` | `username`, `password`, `device_name` facultatif | `token`, `expires_at`, `user`, `collection_timestamp` |
| POST `/apiauth/register` | `username`, `email`, `password` | `challenge_id`, message |
| POST `/apiauth/verify` | `challenge_id`, `code` | Confirmation du compte ; connexion séparée |
| POST `/apiauth/forgotlogin` | `email` | Envoi par e-mail des pseudos actifs, réponse générique |
| POST `/apiauth/forgotpassword` | `username`, `email` | Réponse générique et `challenge_id`, même pour un compte inconnu |
| POST `/apiauth/resetpassword` | `challenge_id`, `code`, `password` | Mot de passe modifié ; reconnexion requise |
| POST `/apiauth/logout` | `{}` + Bearer | Session révoquée |
| GET `/apicompte/me` | Bearer | Profil explicite, aucun hash ni secret |
| POST `/apicompte/update` | Bearer + champs du profil | Profil enregistré |
| POST `/apicompte/requestemail` | Bearer, `email`, `current_password` | `challenge_id` envoyé à la nouvelle adresse |
| POST `/apicompte/confirmemail` | Bearer, `challenge_id`, `code` | Profil avec nouvelle adresse |
| POST `/apicompte/password` | Bearer, `current_password`, `password` | Révocation des accès et reconnexion |
| POST `/apicompte/delete` | Bearer, `current_password` | Suppression BDovore, compte forum indépendant |
| POST `/apiauth/webticket` | Bearer, destination structurée | URL temporaire d'ouverture web |
| GET `/apiauth/openweb?ticket=...` | Ticket | Session web et redirection 303 vers la destination autorisée |

Profil : `user_id`, `public_id`, `username`, `email`, `location`, `birthday`, `open_collection`, `explicit_content`, `newsletter`, `square_type`. Seuls localisation, anniversaire et préférences sont modifiables par `update`. Les booléens sont de vrais booléens JSON ; `square_type` vaut 0 ou 1 ; anniversaire vide ou `AAAA-MM-JJ`.

Sessions : jetons `bdo2_` suivis de 32 octets aléatoires en hexadécimal ; empreinte SHA-256 en base ; expiration après 30 jours d'inactivité et au plus 90 jours. Les durées sont surchargeables dans `config/constante.php` via `BDO_MOBILE_IDLE_SECONDS` et `BDO_MOBILE_ABSOLUTE_SECONDS`.

Codes e-mail : huit chiffres, durée dix minutes, cinq tentatives maximum et usage unique. Un nouveau code invalide le précédent pour ce compte et cette opération, y compris en cas de demandes simultanées ; le remplacement est transactionnel et protégé par un verrou MySQL court. L'envoi d'e-mail se fait après libération du verrou. La récupération garde le couple pseudo/e-mail : une même adresse peut appartenir à plusieurs comptes historiques. La confirmation d'une nouvelle adresse invalide les codes et anciens liens de récupération du compte ; une récupération vérifie aussi que l'adresse n'a pas changé avant de modifier le mot de passe.

Tickets web : 32 octets aléatoires, empreinte seule en base, usage unique, durée une minute, liés à une session encore valide. Destinations possibles : ajout (`type: album`, `keyword`), correction (`type: correction`, `id_edition`), nouvelle édition (`type: edition`, `id_tome`). Aucune URL de redirection libre n'est acceptée.

Les réponses de compte ne sont pas mises en cache. Les accès et codes doivent également être masqués dans les journaux du proxy/serveur HTTP et des outils de supervision.

### E-mails et forum

Le service utilise `bdovoreMail` et sa configuration SMTP existante. Tester l'envoi réel en préproduction ; les tests automatisés injectent un expéditeur factice et n'envoient aucun e-mail.

Après confirmation de l'inscription, la création du compte forum reste indépendante et ne doit pas invalider le compte BDovore si le forum est indisponible. Un mot de passe aléatoire indépendant est utilisé pour le forum : l'utilisateur demande sa réinitialisation sur le forum pour s'y connecter. La vérification TLS est désormais obligatoire.

## Temps 2 : publier la nouvelle app

Dans le dépôt React Native, installer les dépendances puis les Pods iOS. La nouvelle dépendance est `react-native-keychain` 10.0.0.

1. Vérifier les nouveaux parcours sur le backend activé en préproduction, puis sur la production avant publication générale.
2. Vérifier la migration d'une installation de l'app actuelle : suppression de `passwd` et de l'ancien `token` dans AsyncStorage, reconnexion demandée, collection locale conservée pour le même compte.
3. Vérifier le changement de compte : nettoyage de la collection locale avant d'activer la nouvelle identité ; conservation du cache du même compte.
4. Tester sur iOS et Android : inscription, codes e-mail, connexion, profil, changement d'e-mail, récupération, changement de mot de passe, suppression et contributions web.
5. Incrémenter les numéros de version/build selon le processus habituel des stores, puis publier progressivement.

Le profil et le jeton sont conservés dans le stockage sécurisé du système ; le mot de passe n'est jamais conservé après le formulaire. Hors ligne, le profil chargé est consultable et ses modifications sont désactivées. Une déconnexion sans réseau efface les accès locaux et indique que la session distante n'a pas pu être révoquée.

## Validation de coexistence et retour arrière

Avant publication mobile : connecter l'ancienne et la nouvelle app au même compte sur deux appareils. Vérifier lecture/écriture de la collection, commentaires, préférences et ouverture des contributions. Déconnecter la nouvelle app et vérifier que l'ancienne reste connectée. Une modification du mot de passe doit au contraire révoquer tous les accès.

Si le nouveau service doit être suspendu, passer `BDO_MOBILE_API_ENABLED` à `false` : les nouveaux accès répondent 503 et la nouvelle app conserve son cache et son jeton local. L'ancienne app continue à utiliser `/auth/gettoken` et ses API historiques. Ne pas supprimer les nouvelles tables ni revenir aux cookies de mot de passe vulnérables. Réactiver après correction.

Si la publication mobile est suspendue, garder le backend compatible : aucune restauration SQL n'est nécessaire. Le retrait du contrat historique est un chantier ultérieur, après observation de son utilisation.

Surveiller séparément erreurs du contrat historique et du nouveau, taux de 401/429/503 et échecs SMTP, sans journaliser corps, mots de passe, jetons ou codes. Purger périodiquement les sessions expirées/révoquées, tickets et codes anciens, et les buckets de limitation expirés ; ne pas supprimer les inscriptions en attente sans politique explicite de conservation.

## Tests reproductibles

```sh
php -d xdebug.mode=off tests/account_security_test.php
php -d xdebug.mode=off tests/mobile_api_contract_test.php
php -d xdebug.mode=off tests/mobile_account_mysql_test.php
php -d xdebug.mode=off tests/mobile_account_mysql_test.php --legacy-only
php -d xdebug.mode=off tests/admin_http_test.php
php -d xdebug.mode=off tests/adminproposition_http_test.php
```

Le test MySQL crée un nom de base aléatoire et ne supprime la base qu'après avoir lui-même réussi sa création. Il ne charge pas la configuration de production. Paramètres facultatifs : `AUTH_TEST_HOST`, `AUTH_TEST_USER`, `AUTH_TEST_PASSWORD`, `AUTH_TEST_PORT`, `AUTH_TEST_SOCKET`. Les valeurs locales par défaut correspondent au MySQL MAMP de développement.

Le test `parabd_cli_test.php` présente déjà deux échecs sur la visibilité du menu Para-BD ; ses fichiers et le helper concerné ne sont pas modifiés par ce chantier.
