-- Apply after 2026-11-12-mobile-account.sql. No account or collection is merged.
CREATE TABLE IF NOT EXISTS account_social_identities (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(8) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  subject VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  email VARCHAR(254) NOT NULL DEFAULT '',
  email_trusted TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  UNIQUE KEY provider_subject (provider, subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS account_social_links (
  identity_id BIGINT UNSIGNED NOT NULL,
  user_id MEDIUMINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (identity_id, user_id), KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS account_social_flows (
  state_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  cookie_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  provider VARCHAR(8) NOT NULL,
  intent VARCHAR(8) NOT NULL DEFAULT 'login',
  nonce CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  verifier VARCHAR(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Apple relay addresses can exceed the historical 40-character email column.
-- Widen only when needed, preserving the existing character set/collation.
SET @account_email_length = (SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @account_email_charset = (SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @account_email_collation = (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @account_email_nullable = (SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @account_email_default = (SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @account_email_sql = IF(@account_email_length < 254, CONCAT('ALTER TABLE users MODIFY email VARCHAR(254) CHARACTER SET ', @account_email_charset, ' COLLATE ', @account_email_collation, IF(@account_email_nullable = 'YES', ' NULL', ' NOT NULL'), IF(@account_email_default IS NULL, IF(@account_email_nullable = 'YES', ' DEFAULT NULL', ''), CONCAT(' DEFAULT ', QUOTE(@account_email_default)))), 'SELECT 1');
PREPARE account_email_migration FROM @account_email_sql;
EXECUTE account_email_migration;
DEALLOCATE PREPARE account_email_migration;
