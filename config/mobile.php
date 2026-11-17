<?php

// Enable only after applying sql/2026-11-12-mobile-account.sql and
// sql/2026-11-17-email-verification.sql. Website email checks remain mandatory
// when this API flag is disabled.
// constante.php may override these defaults for each environment.
if (!defined('BDO_MOBILE_API_ENABLED')) define('BDO_MOBILE_API_ENABLED', false);
if (!defined('BDO_MOBILE_IDLE_SECONDS')) define('BDO_MOBILE_IDLE_SECONDS', 30 * 86400);
if (!defined('BDO_MOBILE_ABSOLUTE_SECONDS')) define('BDO_MOBILE_ABSOLUTE_SECONDS', 90 * 86400);
