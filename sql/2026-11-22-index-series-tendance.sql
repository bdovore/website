ALTER TABLE `users_album`
  ADD INDEX `idx_users_album_tendance` (`flg_achat`, `date_ajout`, `id_edition`, `user_id`);
