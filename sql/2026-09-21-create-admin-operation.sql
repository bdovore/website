-- Journal des opérations sensibles de l'admin du catalogue - Lot B - migration additive MySQL 5.7.
-- Conserve l'état des fusions, splits et suppressions multi-étapes (spécification section 7).
CREATE TABLE IF NOT EXISTS `admin_operation` (
    `ID_ADMIN_OPERATION` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `OP_TYPE` VARCHAR(30) NOT NULL COMMENT 'code autorise : MERGE_ALBUM, MERGE_EDITION, SPLIT_EDITION',
    `SOURCE_ENTITY` VARCHAR(30) NOT NULL COMMENT 'type d entite source, ex : album',
    `SOURCE_ID` INT UNSIGNED NOT NULL,
    `TARGET_ENTITY` VARCHAR(30) NOT NULL COMMENT 'type d entite cible, ex : album',
    `TARGET_ID` INT UNSIGNED NOT NULL,
    `IDEMPOTENCY_KEY` VARCHAR(191) NOT NULL COMMENT 'une seule execution par confirmation',
    `STATUS` ENUM('PREPARED','APPLIED','VERIFIED','FAILED') NOT NULL DEFAULT 'PREPARED',
    `CURRENT_STEP` VARCHAR(60) NULL COMMENT 'code de la derniere etape terminee',
    `SNAPSHOT` MEDIUMTEXT NULL COMMENT 'donnees avant traitement, utiles au diagnostic et a la reprise',
    `PROGRESSION` MEDIUMTEXT NULL COMMENT 'compteurs et resultats des etapes appliquees',
    `ERROR_CODE` VARCHAR(60) NULL COMMENT 'code interne, non affiche directement',
    `ERROR_MESSAGE` VARCHAR(255) NULL COMMENT 'message technique, non affiche directement',
    `ID_USER` INT UNSIGNED NOT NULL COMMENT 'compte ayant confirme l operation',
    `CREATED_DTE` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `STARTED_DTE` DATETIME NULL,
    `UPDATED_DTE` DATETIME NULL,
    `FINISHED_DTE` DATETIME NULL,
    PRIMARY KEY (`ID_ADMIN_OPERATION`),
    UNIQUE KEY `uniq_admin_operation_idempotency` (`IDEMPOTENCY_KEY`),
    KEY `idx_admin_operation_source` (`SOURCE_ENTITY`, `SOURCE_ID`, `STATUS`),
    KEY `idx_admin_operation_target` (`TARGET_ENTITY`, `TARGET_ID`, `STATUS`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
