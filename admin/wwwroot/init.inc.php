<?php

ini_set('date.timezone', 'Asia/Shanghai');

use Framework\Loader\Autoloader;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Utils\Http;

/**
 * filesystem constants
 */
define('ROOT_DIR',      dirname(__DIR__)  . '/');
define('WWW_DIR',       ROOT_DIR . 'wwwroot/');
define('APP_DIR',       ROOT_DIR . 'approot/');
define('CONFIG_DIR',    APP_DIR  . 'config/');
define('TPL_DIR',       APP_DIR  . 'template/');
define('JS_DIR',        WWW_DIR . 'js/');
define('CSS_DIR',       WWW_DIR . 'css/');
define('IMG_DIR',       WWW_DIR . 'images/');

/**
 * upload director
 */
define('UPLOAD_DIR',    ROOT_DIR  . '../upload/');
define('SYS_DIR', UPLOAD_DIR . 'sys/');
define('USER_DIR', UPLOAD_DIR . 'user/');
define('VIO_DIR', UPLOAD_DIR . 'video/');

/**
 * load the private configure
 */
if (file_exists(CONFIG_DIR . 'config.private.php')) {
	include CONFIG_DIR . "config.private.php";
}

/**
 * load common function
 */
include CONFIG_DIR . 'common.php';

/**
 * load permission config
 */
include CONFIG_DIR . 'perms.inc.php';

/**
 * Run enviorment
 */
!defined('ENV_PRODUCTION') && define('ENV_PRODUCTION', false);
if (!ENV_PRODUCTION) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL | E_STRICT);
} else {
	error_reporting(false);
}

/**
 * Specially for CLI
 */
!isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] = 'cli';
!isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] = '127.0.0.1';

/**
 * library
 */
!defined('LIB_DIR') && define('LIB_DIR', ROOT_DIR . '/../library/');


/**#@+
 * database information
 */
!defined('DB_HOST') && define('DB_HOST', '127.0.0.1');
!defined('DB_PORT') && define('DB_PORT', 3306);
!defined('DB_USERNAME') && define('DB_USERNAME', 'root');
!defined('DB_PASSWORD') && define('DB_PASSWORD', 'root');
!defined('DB_DATABASE') && define('DB_DATABASE', 'db_bl');

!defined('DB2_HOST') && define('DB2_HOST', '');
!defined('DB2_PORT') && define('DB2_PORT', 3306);
!defined('DB2_USERNAME') && define('DB2_USERNAME', 'root');
!defined('DB2_PASSWORD') && define('DB2_PASSWORD', '');
!defined('DB2_DATABASE') && define('DB2_DATABASE', '');

/**#@-*/

/**
 * the base path of urls
 */
!defined('BASE_PATH') && define('BASE_PATH', '/admin/');

/**#@+
 * configuration
 */

const PLUGIN_MANAGER    = 'Framework\Controller\PluginManager';
const HELPER_MANAGER    = 'Framework\View\HelperManager';
const MODEL_MANAGER     = 'Framework\Model\ModelManager';
/**#@-*/

/**
 * register autoload
 */
require LIB_DIR . 'Framework/Loader/Autoloader.php';
Autoloader::setNamespaces(array(
    'Framework' => LIB_DIR,
    'App' => APP_DIR . 'class/',
));
Autoloader::register();

/* @var $locator ServiceLocator */
$GLOBALS['locator'] = new ServiceLocator(include CONFIG_DIR . 'services.php');

// render page via utf8 charset
Http::mimeType('html', 'utf-8');
