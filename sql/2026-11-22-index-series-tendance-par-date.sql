-- Après 2026-11-22-index-series-tendance.sql, remplacer l'index du classement.
ALTER TABLE `users_album`
  DROP INDEX `idx_users_album_tendance`,
  ADD INDEX `idx_users_album_tendance_date` (`date_ajout`, `id_edition`, `user_id`);
