<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

if (extension_loaded('xdebug')) {
    xdebug_disable();
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('POSTIE_DEBUG')) {
    define('POSTIE_DEBUG', true);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
define('WP_DEBUG_LOG', true);
define('WPINC', 'wp-includes');

require_once 'wpstub.php';
require_once '../postie.php';

function config_GetDefaults() {
    $pconfig = new PostieConfig();
    return $pconfig->defaults();
}

