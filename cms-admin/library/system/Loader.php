<?php

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 *
 * Class Loader
 *
 */
class Loader
{
    /**
     * Database class instance
     * @var object $Db
     */
    public $Db;

    /**
     * Helper class instance
     * @var object $Helper
     */
    public $Helper;

    /**
     * Hooks class instance
     * @var object $Hooks
     */
    public $Hooks;

    /**
     * Garbage class instance
     * @var object $Garbage
     */
    public $Garbage;

    /**
     * Session class instance
     * @var object $Session
     */
    public $Session;

    /**
     * Compiler Class instance
     * @var object $Compiler
     */
    public $Compiler;

    /**
     * Language variables of project
     * @var array $languages
     */
    public $languages;

    /**
     * Language of the admin panel
     * @var string $adminLanguage
     */
    public $adminLanguage;

    /**
     * @var array $locale
     */
    public $locale;

    /**
     * @var array $tableStructure
     */
    public $tableStructure;

    /**
     * Loader constructor.
     * Generate instances of Database, Functions and Session classes.
     * Establish database connection.
     *
     * @param array $languages Language list.
     */
    public function __construct($languages)
    {
        // SQL Injection & XSS Protection
        if ($_GET['url']) {
            $_GET['url'] = htmlspecialchars(filter_var(trim($_GET['url'], '/'), FILTER_SANITIZE_STRING));
        }

        // Create new instance of Database class
        $this->Db = new Database(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS);

        if (!$this->Db) {
            die(_('Veritabanı bağlantısı sağlanamadı!'));
        } else {
            // Default Language
            $defaultLang = $this->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = 'default_lang'");

            // Sorting by Default Language
            $defaultLangArray[$defaultLang['option_value']] = $languages[$defaultLang['option_value']];
            unset($languages[$defaultLang['option_value']]);
            $languages = $defaultLangArray + $languages;

            // Set Site languages variables
            $this->languages['list'] = $languages;
            $this->languages['keys'] = array_keys($languages);
            $this->languages['first'] = $this->languages['keys'][0];
            $this->languages['default_lang'] = $defaultLang['option_value'];

            // Set panel language
            $this->setLocaleLanguage();

            // Get locale datas
            $this->locale = $this->getAllLocales();

            // Table Structure
            $this->tableStructure = $this->getTableStructure();

            // Get Instances
            $this->getInstances();
        }
    }

    /**
     *
     * @access private
     */
    private function getInstances()
    {
        // Session instance
        $sessionStorage = new NativeSessionStorage(['cookie_lifetime' => 2592000], new NativeFileSessionHandler());
        $this->Session = new Session($sessionStorage);
        $this->Session->start();
        ob_start();

        // Create new instance of Cookies class
        $this->Cookies = new Cookies();

        // Helper instance
        $this->Helper = new Helper($this);

        // Hooks instance
        $this->Hooks = new Hooks();

        // Garbage instance
        $this->Garbage = new Garbage($this);

        // Create new instance of Compiler class
        $this->Compiler = new Compiler($this);
    }

    /**
     * Detect default language and set as project language.
     *
     * @access public
     * @return string Current language
     */
    public function setLocaleLanguage()
    {
        // Localization
        $panelLanguage = $this->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = 'default_admin_lang'");
        $panelLanguage = $panelLanguage['option_value'];
        $projectName = 'cms-admin';

        switch ($panelLanguage) {
            case 'TR':
                $language = 'tr_TR.utf8';
                break;
            case 'EN':
                $language = 'en_US.utf8';
                break;
            case 'RU':
                $language = 'ru_RU.utf8';
                break;
            default:
                $language = 'tr_TR.utf8';
        }

        // Set language
        putenv('LC_ALL=' . $language);
        setlocale(LC_ALL, $language);

        // Specify location of translation tables and choose domain
        bindtextdomain($projectName, './system/locale');
        textdomain($projectName);

        $this->adminLanguage = $panelLanguage;
    }

    /**
     * Return all table locale from the locale.json file by current language.
     *
     * @access private
     * @return array|bool Array of all locale on success, false on empty locale file
     */
    private function getAllLocales()
    {
        $locale = json_decode(file_get_contents(ABSPATH . 'cms-content/themes/default/config/locale.json'), true);
        $lang = strtolower($this->adminLanguage);

        if (count($locale) > 0) {
            $result = array();

            foreach ($locale as $key => $value) {
                $result[$key] = stripslashes($value[$lang]);
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @access private
     */
    private function getTableStructure()
    {
        // Get System Tables
        $system = null;
        $_locale = $this->locale;
        require ABSPATH . 'cms-admin/system/_system_tables.php';

        // Get Custom Tables
        $tables = null;
        require ABSPATH . 'cms-content/themes/default/config/tables.php';

        $result = array_merge($system, $tables);
        return $result;
    }
}