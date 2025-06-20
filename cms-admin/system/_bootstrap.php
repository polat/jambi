<?php


/**
 * Jambi version
 * @var String JAMBI_VERSION
 */
define('JAMBI_VERSION', "1.8.0");


/**
 * Load Settings
 */
$config = json_decode(file_get_contents(dirname(__FILE__) . "/../../config.json"), true);


/**
 * Database information
 * @var String DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS
 */
define('DB_TYPE', $config['database']['DB_TYPE']);
define('DB_HOST', $config['database']['DB_HOST']);
define('DB_NAME', $config['database']['DB_NAME']);
define('DB_USER', $config['database']['DB_USER']);
define('DB_PASS', $config['database']['DB_PASS']);
define('DB_CHARSET', $config['database']['DB_CHARSET']);
define('DB_COLLATE', $config['database']['DB_COLLATE']);
define('DB_PREFIX', $config['database']['DB_PREFIX'] . '_');


/**
 * Url of Site
 * @var String SITE_URL
 */
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https://" : "http://";
define('SITE_URL', $protocol . $_SERVER['HTTP_HOST']);


/**
 * Absolute Path
 * @var String ABSPATH
 */
define('ABSPATH', dirname(__FILE__) . '/../../');


/**
 * Url of root folder
 * @var String BASEURL
 */
define('BASEURL', '/' . str_replace($_SERVER['DOCUMENT_ROOT'] . '/', null, realpath(ABSPATH) . '/'));


/**
 * Url of cms-admin folder
 * @var String JAMBI_ADMIN
 */
define('JAMBI_ADMIN', BASEURL . 'cms-admin/');


/**
 * Url of cms-admin/content
 * @var String JAMBI_ADMIN_CONTENT
 */
define('JAMBI_ADMIN_CONTENT', JAMBI_ADMIN . 'content/');

/**
 * Url of cms-admin/controller
 * @var String JAMBI_ADMIN_CONTROLLER
 */
define('JAMBI_ADMIN_CONTROLLER', JAMBI_ADMIN . 'controller/');


/**
 * Url of cms-admin/library
 * @var String JAMBI_ADMIN_LIBRARY
 */
define('JAMBI_ADMIN_LIBRARY', JAMBI_ADMIN . 'library/');


/**
 * Url of cms-admin/system
 * @var String JAMBI_ADMIN_SYSTEM
 */
define('JAMBI_ADMIN_SYSTEM', JAMBI_ADMIN . 'system/');


/**
 * Url of cms-admin/view
 * @var String JAMBI_ADMIN_VIEW
 */
define('JAMBI_ADMIN_VIEW', JAMBI_ADMIN . 'view/');


/**
 * Date Format for Admin Panel
 */
define('JAMBI_ADMIN_DATE_FORMAT', 'Y-m-d H:i:s');


/**
 * Url of cms-content folder
 * @var String JAMBI_CONTENT
 */
define('JAMBI_CONTENT', BASEURL . 'cms-content/');


/**
 * Url of cms-content/themes folder
 * @var String JAMBI_CONTENT_THEMES
 */
define('JAMBI_CONTENT_THEMES', JAMBI_CONTENT . 'themes/');


/**
 * Url of Active Theme
 * @var String ACTIVE_THEME
 */
define('ACTIVE_THEME', JAMBI_CONTENT_THEMES . 'default/');


/**
 * Url of content folder with Active Theme Path
 * @var String JAMBI_THEME_CONTENT
 */
define('JAMBI_THEME_CONTENT', ACTIVE_THEME . 'content/');

/**
 * Url of controller folder with Active Theme Path
 * @var String JAMBI_THEME_CONTROLLER
 */
define('JAMBI_THEME_CONTROLLER', ACTIVE_THEME . 'controller/');


/**
 * Url of library folder with Active Theme Path
 * @var String JAMBI_THEME_LIBRARY
 */
define('JAMBI_THEME_LIBRARY', ACTIVE_THEME . 'library/');


/**
 * Url of view folder with Active Theme Path
 * @var String JAMBI_THEME_VIEW
 */
define('JAMBI_THEME_VIEW', ACTIVE_THEME . 'view/');


/**
 * Date Format for Jambi Content
 */
define('JAMBI_CONTENT_DATE_FORMAT', $config['dateFormat']);


/**
 * Url of upload files
 * @var String UPLOADURL
 */
define('UPLOADURL', 'cms-uploads/');


/**
 * BASEURL & UPLOADURL together
 * @var String JAMBI_UPLOADS
 */
define('JAMBI_UPLOADS', BASEURL . UPLOADURL);


/**
 * Dynamic Sitemap Option
 * @var String DYNAMIC_SITEMAP
 */
define('DYNAMIC_SITEMAP', $config['dynamicSitemap']);


/**
 * Default Time Zone
 */
date_default_timezone_set($config['timeZone']);


/**
 * Memory Limit Option
 */
ini_set('memory_limit', '512M');


/**
 * Error Reporting
 */
error_reporting(E_ERROR);
ini_set('display_errors', '1');


/**
 * Require __autoloader
 */
require_once ABSPATH . 'cms-admin/library/__autoloader.php';


/**
 * Create new instance of Loader class.
 */
$Loader = new Loader($config['lang']);


/**
 * Create new instance of Jambi class.
 */
$Jambi = new Jambi($Loader);


/**
 * Create new instance of Site class.
 */
$Site = new Site($Loader);


/**
 * Require Functions
 */
require_once ABSPATH . 'cms-content/themes/default/functions.php';