# Déploiement du MVP Para-BD

Le module est livré désactivé par défaut. L’ordre de déploiement recommandé est le suivant :

1. déployer le code et sauvegarder la base ;
2. appliquer `sql/2026-08-09-create-parabd.sql` pour créer et initialiser les tables de la fonctionnalité ;
3. créer le répertoire défini par `BDO_DIR_PARABD`, accessible en écriture par PHP ;
4. supprimer les fichiers de cache de schéma `cache/*_schema.serial` ;
5. définir `BDO_PARABD_ENABLED` à `true` dans la configuration d’environnement ;
6. conserver `BDO_PARABD_MIN_LEVEL` à `1` pour le pilote administrateurs/modérateurs ;
7. passer `BDO_PARABD_MIN_LEVEL` à `2` pour ouvrir le module aux membres.

Les constantes documentées dans `config/constante.php.sample` permettent aussi d’ajuster la charte, les limites horaires et les contraintes d’image.

En cas d’incident, remettre `BDO_PARABD_ENABLED` à `false`. Les tables et fichiers Para-BD sont alors laissés en place afin de permettre une réouverture sans perte de données.

## Vérifications

```sh
php tests/parabd_cli_test.php
```

Le test d’intégration MySQL 5.7 peut être lancé uniquement sur une base jetable dont le nom commence par `parabd_test` :

```sh
PARABD_TEST_DATABASE=parabd_test_mvp \
PARABD_TEST_SOCKET=/chemin/mysql.sock \
php tests/parabd_mysql_test.php
```

Le script crée et détruit les seules tables de cette base de test. Il ne doit jamais viser une base de développement ou de production.
