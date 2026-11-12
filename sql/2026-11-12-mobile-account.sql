-- Additive migration: no change to users.API_TOKEN, DATE_TOKEN or existing data.
-- users.password_v2 (already used by the website) must exist before activation.
-- Preserve an existing reset table; provide it if this environment never enabled recovery.
CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id MEDIUMINT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY user_id (user_id), KEY token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mobile_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id MEDIUMINT UNSIGNED NOT NULL,
  token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  credential_stamp CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  device_name VARCHAR(80) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  last_used_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  UNIQUE KEY token_hash (token_hash), KEY user_id (user_id), KEY expires_at (expires_at)
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

CREATE TABLE IF NOT EXISTS mobile_web_tickets (
  token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  destination VARCHAR(512) NOT NULL,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mobile_rate_limits (
  bucket CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
