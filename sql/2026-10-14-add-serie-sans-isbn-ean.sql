-- Réglage de série : valeur par défaut des nouvelles éditions.
-- Les éditions existantes sont marquées uniquement après confirmation admin.
ALTER TABLE `bd_serie`
  ADD COLUMN `FLAG_SANS_ISBN_EAN` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;
