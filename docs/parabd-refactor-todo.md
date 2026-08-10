# Refonte interne Para-BD

Objectif : aligner le domaine Para-BD sur les conventions de persistance de
BDovore sans perdre les transactions, les contrôles de concurrence ni les
contrats HTTP du MVP.

## Principes

- Conserver l’API publique actuelle de `ParabdService` pendant la refonte.
- Garder les contrôleurs limités à HTTP, droits, CSRF et rendu.
- Représenter chaque table Para-BD par un modèle héritant de `Bdo_Db_Line`.
- Placer les requêtes spécialisées dans le modèle de la table concernée.
- Conserver dans le service les cas d’usage impliquant plusieurs tables.
- Garder les écritures atomiques spécialisées (`FOR UPDATE`, révision
  optimiste, fusion en masse) hors du CRUD générique de `Bdo_Db_Line`.
- Isoler les règles pures et le stockage des images de la persistance.
- Ne modifier ni les routes ni le format des réponses JSON.

## Étapes

- [x] Établir la référence des tests CLI et MySQL.
- [x] Extraire `ParabdException`.
- [x] Extraire `ParabdRules` : normalisation, identifiants, dates, doublons,
      confiance et validation des valeurs simples.
- [x] Extraire `ParabdImageStorage` : validation MIME, EXIF, GD et fichiers.
- [x] Ajouter les modèles `Bdo_Db_Line` pour les quatorze tables Para-BD.
- [x] Déplacer le catalogue et la fiche détaillée dans `Parabditem`.
- [x] Déplacer la collection et la page guest dans `Userparabd`.
- [x] Déplacer les opérations propres aux identifiants, médias, sources,
      révisions, votes, doublons, signalements et profils dans leurs modèles.
- [x] Réduire `ParabdService` aux transactions et règles de cas d’usage.
- [x] Adapter les scripts de seed pour ne plus charger le service monolithique.
- [x] Vérifier que les modifications utilisateur préexistantes sur les votes et
      l’affichage des propositions sont conservées.
- [x] Exécuter les tests CLI, MySQL et HTTP, puis `php -l` sur tous les PHP
      concernés.
- [x] Vérifier le cache `bdovore_db_schema.serial` ; le supprimer uniquement si
      le schéma a changé.

Validation effectuée le 10 août 2026 : 30 contrôles CLI, 35 contrôles MySQL
sur base jetable, puis catalogue, fiche, collection, guest et autocomplétion
sur `bdovore_db` via `localhost:8888`. Le schéma n’ayant pas changé pendant
cette refonte, le cache réel a été conservé ; son bon chargement est confirmé
par les pages HTTP qui instancient les nouveaux modèles.

## Critères de fin

- `ParabdService` ne contient plus de mini-couche générique `query/one/rows`.
- Aucun contrôleur ne porte de règle métier Para-BD supplémentaire.
- Les transactions de création, contribution, vote, média, édition admin et
  fusion restent atomiques.
- Les tests existants passent sans modification du contrat fonctionnel.
