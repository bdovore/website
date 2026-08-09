<?php

// Safe defaults for the Para-BD MVP. Environment-specific constante.php files
// may define any value before this file is loaded.
if (!defined('BDO_PARABD_ENABLED')) define('BDO_PARABD_ENABLED', false);
if (!defined('BDO_PARABD_MIN_LEVEL')) define('BDO_PARABD_MIN_LEVEL', 1);
if (!defined('BDO_PARABD_CHARTER_VERSION')) define('BDO_PARABD_CHARTER_VERSION', '1');
if (!defined('BDO_PARABD_CREATIONS_PER_HOUR')) define('BDO_PARABD_CREATIONS_PER_HOUR', 10);
if (!defined('BDO_PARABD_UPLOADS_PER_HOUR')) define('BDO_PARABD_UPLOADS_PER_HOUR', 20);
if (!defined('BDO_PARABD_MAX_UPLOAD_BYTES')) define('BDO_PARABD_MAX_UPLOAD_BYTES', 5242880);
if (!defined('BDO_PARABD_MAX_IMAGE_PIXELS')) define('BDO_PARABD_MAX_IMAGE_PIXELS', 30000000);
if (!defined('BDO_DIR_PARABD')) define('BDO_DIR_PARABD', BDO_DIR_IMAGE . 'parabd' . DS);
if (!defined('BDO_URL_PARABD')) define('BDO_URL_PARABD', BDO_URL_IMAGE . 'parabd/');
