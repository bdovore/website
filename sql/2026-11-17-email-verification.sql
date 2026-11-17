-- Deploy before the PHP changes. Independent of BDO_MOBILE_API_ENABLED.
-- Existing addresses remain unverified; do not infer ownership from historical data.
-- Store the verified address too, so any other writer changing users.email cannot
-- silently reuse a verification belonging to the previous address.
SET @email_verification_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_at'),
  'SELECT 1', 'ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL DEFAULT NULL');
PREPARE email_verification_migration FROM @email_verification_sql;
EXECUTE email_verification_migration;
DEALLOCATE PREPARE email_verification_migration;

SET @email_verification_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_address'),
  'SELECT 1', 'ALTER TABLE users ADD COLUMN email_verified_address VARCHAR(254) NULL DEFAULT NULL');
PREPARE email_verification_migration FROM @email_verification_sql;
EXECUTE email_verification_migration;
DEALLOCATE PREPARE email_verification_migration;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id MEDIUMINT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY user_id (user_id), KEY token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mobile_challenges (
  id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
  user_id MEDIUMINT UNSIGNED NOT NULL,
  purpose VARCHAR(20) NOT NULL,
  code_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  credential_stamp CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  target_email VARCHAR(254) NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  KEY user_purpose (user_id, purpose), KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mobile_rate_limits (
  bucket CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

