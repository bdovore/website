-- Recreate imported production triggers with the account running this script.
-- This avoids depending on the production-only `bdovore1prod`@`%` account.

SET @previous_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

DROP TRIGGER IF EXISTS `after_insert_bd_edition`;
DROP TRIGGER IF EXISTS `after_delete_bd_edition`;
DROP TRIGGER IF EXISTS `after_insert_users_album`;

DELIMITER $$

CREATE DEFINER = CURRENT_USER TRIGGER `after_insert_bd_edition`
AFTER INSERT ON `bd_edition`
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM `bd_edition_stat`
        WHERE `id_edition` = NEW.`id_edition`
    ) THEN
        INSERT INTO `bd_edition_stat` (
            `id_edition`,
            `id_tome`,
            `id_serie`,
            `id_genre`,
            `id_editeur`,
            `id_collection`
        )
        SELECT
            NEW.`id_edition`,
            NEW.`id_tome`,
            t.`id_serie`,
            t.`id_genre`,
            NEW.`id_editeur`,
            NEW.`id_collection`
        FROM `bd_tome` AS t
        WHERE t.`id_tome` = NEW.`id_tome`;
    END IF;
END$$

CREATE DEFINER = CURRENT_USER TRIGGER `after_delete_bd_edition`
AFTER DELETE ON `bd_edition`
FOR EACH ROW
BEGIN
    DELETE FROM `bd_edition_stat`
    WHERE `id_edition` = OLD.`id_edition`;
END$$

CREATE DEFINER = CURRENT_USER TRIGGER `after_insert_users_album`
AFTER INSERT ON `users_album`
FOR EACH ROW
BEGIN
    UPDATE `bd_edition_stat`
    SET `NBR_USER_ID_EDITION` = `NBR_USER_ID_EDITION` + 1
    WHERE `id_edition` = NEW.`id_edition`;

    UPDATE `bd_edition_stat`
    SET `NBR_USER_ID_TOME` = `NBR_USER_ID_TOME` + 1
    WHERE `id_tome` = (
        SELECT `id_tome`
        FROM `bd_edition`
        WHERE `id_edition` = NEW.`id_edition`
    );

    UPDATE `bd_edition_stat`
    SET `NBR_USER_ID_SERIE` = `NBR_USER_ID_SERIE` + 1
    WHERE `id_serie` = (
        SELECT t.`id_serie`
        FROM `bd_edition` AS e
        INNER JOIN `bd_tome` AS t ON t.`id_tome` = e.`id_tome`
        WHERE e.`id_edition` = NEW.`id_edition`
    );
END$$

DELIMITER ;

SET SESSION sql_mode = @previous_sql_mode;
