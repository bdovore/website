<?php

// Enable only after applying sql/2026-11-12-mobile-account.sql and
// sql/2026-11-17-email-verification.sql. Website email checks remain mandatory
// when this API flag is disabled.
// constante.php may override these defaults for each environment.
if (!defined('BDO_MOBILE_API_ENABLED')) define('BDO_MOBILE_API_ENABLED', false);
if (!defined('BDO_MOBILE_IDLE_SECONDS')) define('BDO_MOBILE_IDLE_SECONDS', 30 * 86400);
if (!defined('BDO_MOBILE_ABSOLUTE_SECONDS')) define('BDO_MOBILE_ABSOLUTE_SECONDS', 90 * 86400);
// Cookie web « Rester connecté » : réutilise mobile_sessions (empreinte sha256, révocation,
// stamp d'identifiants). Nécessite sql/2026-11-12-mobile-account.sql ; sinon définir à false
// dans constante.php et le code dégrade silencieusement (pas de cookie posé).
if (!defined('BDO_REMEMBER_ME_ENABLED')) define('BDO_REMEMBER_ME_ENABLED', true);
