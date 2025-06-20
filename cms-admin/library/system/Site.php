<?php

/**
 *
 * Class Site
 *
 */
class Site
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

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
     * Session class instance
     * @var object $Session
     */
    public $Session;

    /**
     * All Tables of Compiler Class
     * @var array $tables
     */
    public $tables;

    /**
     * Current language of website as uppercase
     * @var string $lang
     */
    public $lang;

    /**
     * Current language of website as lowercase
     * @var string $lang_small
     */
    public $lang_small;

    /**
     * Module name of Url
     * @var string $moduleName
     */
    public $moduleName;

    /**
     * Record Information
     * @var array $recordInfo
     */
    public $recordInfo;

    /**
     * All labels of Site
     * @var string $labels
     */
    public $labels;

    /**
     * All Settings of Site
     * @var string $settings
     */
    public $settings;

    /**
     * Url array of current page
     * @var array $url
     */
    public $url;

    /**
     * Full Url of current page
     * @var string $fullUrl
     */
    public $full_url;

    /**
     * Site constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        // Set Loader Variables to Site
        $this->Loader = $Loader;
        $this->Db = $Loader->Db;
        $this->Helper = $Loader->Helper;
        $this->Session = $Loader->Session;
        $this->tables = $this->Loader->Compiler->compiledTables;

        // Set Site Language
        $this->getUrl();

        // Set Site Language
        $this->setSiteLanguage();

        // Get Record Information
        $this->recordInfo = $this->getRecordInformations();

        // Get all labels
        $this->labels = $this->getAllLabels();

        // Get all settings
        $this->settings = $this->getAllSettings();

        // Set module name
        $this->moduleName = $this->getModuleName();

        // Content
        if ($this->isContent()) {
            $this->content = $this->getContent();
        }

        // define BASEURL_LANG & HOMEURL
        if ($this->lang == $this->settings['default_lang']) {
            define('BASEURL_LANG', BASEURL);
            define('HOMEURL', SITE_URL . rtrim(BASEURL, '/'));
            $this->Loader->Compiler->baseurl_lang = BASEURL_LANG;
        } else {
            define('BASEURL_LANG', BASEURL . $this->lang_small . '/');
            define('HOMEURL', SITE_URL . BASEURL . $this->lang_small);
            $this->Loader->Compiler->baseurl_lang = BASEURL_LANG;
        }

        // Template Settings
        $this->twigLoader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . JAMBI_THEME_VIEW);
        $this->twig = new Twig_Environment($this->twigLoader, array('cache' => $_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_CONTROLLER . 'twig-cache/content', 'auto_reload' => true, 'autoescape' => false));
        $unserializeFilter = new Twig_SimpleFilter('unserialize', 'unserialize');
        $this->twig->addFilter($unserializeFilter);
        $this->twig->addGlobal('SITE_URL', SITE_URL);
        $this->twig->addGlobal('BASEURL', BASEURL);
        $this->twig->addGlobal('BASEURL_LANG', BASEURL_LANG);
        $this->twig->addGlobal('HOMEURL', HOMEURL);
        $this->twig->addGlobal('JAMBI_CONTENT', JAMBI_CONTENT);
        $this->twig->addGlobal('JAMBI_THEME_CONTENT', JAMBI_THEME_CONTENT);
        $this->twig->addGlobal('JAMBI_THEME_CONTROLLER', JAMBI_THEME_CONTROLLER);
        $this->twig->addGlobal('JAMBI_THEME_VIEW', JAMBI_THEME_VIEW);
        $this->twig->addGlobal('JAMBI_UPLOADS', JAMBI_UPLOADS);
        $this->twig->addGlobal('label', $this->labels);
        $this->twig->addGlobal('languageList', $this->getLanguageList());
        $this->twig->addGlobal('Site', $this);
    }

    /**
     * Get current url and store an array of parsed urls in class.
     * $this->url array include site language(lang), full_url and exploded urls of full_url.
     *
     * @access private
     */
    private function getUrl()
    {
        if ($_GET['url']) {
            $lang = null;
            $this->full_url = $_GET['url'];
            $this->url = explode('/', $this->full_url);

            if ($this->Loader->languages['list'][strtoupper($this->url[0])]) {
                // Sub Language From Full Url
                $this->full_url = substr($this->full_url, -1) == '/' ? substr($this->full_url, -1) : $this->full_url;
                $this->full_url = substr($this->full_url, strlen($this->url[0]) + 1);

                // Sub Language From Url Array
                $lang = strtoupper($this->url[0]);
                array_shift($this->url);
            }

            $this->url = count($this->url) > 0 ? array_combine(range(1, count($this->url)), $this->url) : $this->url;
            $this->url['lang'] = $lang;

            // Url Count
            $this->url['count'] = count(explode('/', $this->full_url));
        } else {
            $this->url['count'] = 0;
        }
    }

    /**
     * Check if first url is set and is not empty, then return false, it means it is in main page.
     * If it is not set or data is empty then return true, it means it is in sub content page.
     *
     * @access public
     * @return bool False on in sub content page, True on in main page
     */
    public function isHome()
    {
        return isset($this->url[1]) && !is_null($this->url[1]) ? false : true;
    }

    /**
     * Content Page Control
     *
     * @access public
     * @return bool True on in sub content page, False it is not content page.
     */
    public function isContent()
    {
        return $this->recordInfo['status'] == 404 ? false : true;
    }

    /**
     * 404 Page Control
     *
     * @access public
     * @return bool True is 404 content page, False it is not 404 page.
     */
    public function is404Page()
    {
        return $this->recordInfo['status'] == 404 ? true : false;
    }

    /**
     * Set language session, $this->lang and $this->lang_small variables.
     * It takes one optional parameter as preferred language. Decide mechanism:
     *  - Firstly it looks url for language, if it's exist, sets language as it.
     *  - If it's not set, looks to the session, if it's set, sets language as it.
     *  - If session is not set too, looks at the parameter and if it's set, sets language as it.
     *  - At last if it's not set too, sets the first language in $this->Loader->languages['list'] array.
     *
     * @access public
     */
    public function setSiteLanguage()
    {
        $defaultLang = $this->getSetting('default_lang');

        if (isset($_GET['siteLang']) && array_key_exists($_GET['siteLang'], $this->Loader->languages['list'])) {
            $lang = $_GET['siteLang'];
        } else if (!isset($this->url['lang'])) {
            $lang = $defaultLang;
        } else {
            if (array_key_exists($this->url['lang'], $this->Loader->languages['list'])) {
                $lang = $this->url['lang'];

                if ($this->url['lang'] == $defaultLang) {
                    header($_SERVER["SERVER_PROTOCOL"] . ' 301 Moved Permanently');
                    header('Location: ' . BASEURL . $this->full_url);
                }
            } else {
                $lang = $defaultLang;
            }
        }

        $this->lang = $lang;
        $this->lang_small = strtolower($this->lang);
    }

    /**
     *
     */
    public function getLanguageList()
    {
        foreach ($this->Loader->languages['list'] as $key => $value) {
            $language['href'] = $this->changeLang($key);
            $language['key'] = $key;
            $language['label'] = $value;
            $language['active'] = $this->lang == $key ? true : false;
            $result[] = $language;
        }

        return $result;
    }

    /**
     * Change language of the url by given language parameter and return it.
     *
     * @access public
     * @param string $lang Name of the language such as TR or EN
     * @return string Url in given language
     */
    public function changeLang($lang)
    {
        if ($this->lang != $lang) {
            if ($lang == $this->settings['default_lang']) {
                $result = BASEURL;
                $slash = null;
            } else {
                $result = BASEURL . strtolower($lang);
                $slash = '/';
            }

            if (!$this->isHome()) {
                $fullUrl = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_table` = :rec_table AND `rec_id` = :rec_id AND `lang` = :lang", array('rec_table' => $this->recordInfo['rec_table'], 'rec_id' => $this->recordInfo['rec_id'], 'lang' => $lang));
                $result .= $slash . $fullUrl['full_url'];
            }

            return $result;
        } else {
            return 'javascript:void(0)';
        }
    }

    /**
     * Return the meta array by looking the $this->url array.
     * If $titleType is 1, then return title as '$meta['title'] - $generalTitle'.
     * Otherwise, return just $generalTitle.
     *
     * @access public
     * @param int $titleType Title type parameter
     * @param string $separator Separator type parameter
     * @return array Meta array of current page
     */
    public function getMeta($titleType = 2, $separator = '-')
    {
        $result = array();

        if ($this->isHome()) {
            $result['title'] = $this->settings['meta_title'];
            $result['desc'] = $this->settings['meta_desc'];
            $result['image'] = JAMBI_UPLOADS . $this->settings['meta_image'];
            $result['robots'] = 'index, follow';

            if (count($this->Loader->languages['list']) > 1) {
                foreach ($this->Loader->languages['list'] as $key => $value) {
                    if ($key == $this->settings['default_lang']) {
                        $result['hreflang'][strtolower($key)] = rtrim(SITE_URL . BASEURL, '/');
                    } else {
                        $result['hreflang'][strtolower($key)] = SITE_URL . BASEURL . strtolower($key);
                    }
                }

                $result['hreflang']['x-default'] = $result['hreflang'][strtolower($this->settings['default_lang'])];
                krsort($result['hreflang']);
            }
        } else {
            $title = !empty($this->recordInfo['meta_title']) ? $this->recordInfo['meta_title'] : $this->recordInfo['title'];

            switch ($titleType) {
                case 1:
                    $result['title'] = $title;
                    break;
                case 2:
                    $result['title'] = $title . ' ' . $separator . ' ' . $this->settings['meta_title'];
                    break;
                default:
                    $result['title'] = $title . ' ' . $separator . ' ' . $this->settings['meta_title'];
            }

            if ($this->isContent()) {
                $result['desc'] = $this->recordInfo['meta_desc'];
                $result['image'] = $this->recordInfo['meta_image'] != '' ? JAMBI_UPLOADS . $this->recordInfo['meta_image'] : JAMBI_UPLOADS . $this->settings['meta_image'];

                $urlList = $this->Loader->Db->select("SELECT `full_url`, LOWER(`lang`) as `lang` FROM `system_meta` WHERE `lang` IN ('" . join("','", $this->Loader->languages['keys']) . "') AND `rec_table` = :rec_table AND `rec_id` = :rec_id", array('rec_table' => $this->recordInfo['rec_table'], 'rec_id' => $this->recordInfo['rec_id']));

                if (count($urlList) > 1) {
                    foreach ($urlList as $key => $value) {
                        if ($value['lang'] == strtolower($this->settings['default_lang'])) {
                            $result['hreflang'][$value['lang']] = SITE_URL . BASEURL . $value['full_url'];
                        } else {
                            $result['hreflang'][$value['lang']] = SITE_URL . BASEURL . $value['lang'] . '/' . $value['full_url'];
                        }
                    }

                    $result['hreflang']['x-default'] = $result['hreflang'][strtolower($this->settings['default_lang'])];
                    krsort($result['hreflang']);
                }
            }

            $result['sitemap'] = $this->recordInfo['sitemap'];
            $result['canonical'] = SITE_URL . $this->full_url;
        }

        return $result;
    }

    /**
     * Return menu records of given label from system_pages table.
     *
     * @access public
     * @param string
     * @param array $menuTemplate Menu Template to render
     * @param array $specialField Get menu by Field
     * @return string|array Html string on given $menuTemplate, Menu array on empty $menuTemplate
     */
    public function getMenu($label = null, $menuTemplate = array(), $specialField = array('field' => null, 'value' => null))
    {
        // Display Settings
        if (empty($label)) {
            $where = null;
        } else {
            if (is_array($label)) {
                $labelSqlArray = '';
                foreach ($label as $value) {
                    $labelSqlArray .= "'$value',";
                }

                $labelSqlArray = rtrim($labelSqlArray, ',');
                $labelID = $this->Loader->Db->select("SELECT `id` FROM `system_menu` WHERE `label` IN ($labelSqlArray)");

                $whereArray = '';
                foreach ($labelID as $id) {
                    $serializedDisplay = serialize((string)$id['id']);
                    $whereArray .= " OR display LIKE '%$serializedDisplay%'";
                }

                $whereArray = ltrim($whereArray, ' OR ');
                $where = 'WHERE ' . $whereArray;
            } else {
                $labelID = $this->Loader->Db->selectOne("SELECT `id` FROM `system_menu` WHERE `label` = :label", array('label' => $label));
                $serializedDisplay = serialize((string)$labelID['id']);
                $where = "WHERE display LIKE '%$serializedDisplay%'";
            }
        }

        // Select all system_pages rows
        $select = $this->Loader->Db->select("SELECT `system_pages`.*, `system_meta`.full_url FROM `system_pages` INNER JOIN `system_meta` ON `system_pages`.rec_status = :rec_status AND `system_pages`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table $where ORDER BY `sequence`", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => 'system_pages'));

        // Render Raw Fields
        $menu = $this->Loader->Compiler->renderRawFields('system_pages', $select, $this->lang);

        // Parent ID of Tree
        if (is_array($specialField) && $specialField['field'] != '' && $specialField['value'] != '') {
            $field = $specialField['field'];
            $value = $specialField['value'];
            $select = $this->Loader->Db->selectOne("SELECT `id` FROM `system_pages` WHERE `$field` = :value AND `rec_status` = :rec_status", array('value' => $value, 'rec_status' => 0));
            $specialID = $select['id'];
            $parentID = $specialID === '' ? 0 : $specialID;
        } else {
            $parentID = 0;
        }

        // Label of 'Category' field
        $categoryField = $this->Loader->Compiler->getFieldByType('system_pages', 'category');
        $categoryFieldLabel = $categoryField['key'];

        // Creating Tree List
        $menu = !is_null($categoryFieldLabel) ? $this->Loader->Helper->createTreeBranch($menu, $categoryFieldLabel, $parentID) : $menu;

        if (empty($menuTemplate)) {
            return $menu;
        } else {
            return $this->renderMenuTemplate($menu, $menuTemplate);
        }
    }

    /**
     * Return sub menu of given module from system_pages table.
     *
     * @access public
     * @param string $module Module name to get sub menu
     * @param string|array $label Display setting for getting specific location
     * @param array $menuTemplate Menu Template to render
     * @return string|array Html string on given $menuTemplate, sub menu array on empty $menuTemplate
     */
    public function getMenuByModule($module, $label = null, $menuTemplate = array())
    {
        return !empty($module) ? $this->getMenu($label, $menuTemplate, array('field' => 'module', 'value' => $module)) : false;
    }

    /**
     * Get single menu by given module.
     *
     * @param string $module Module name to get menu element
     * @param array $menuTemplate Menu Template to render html
     * @return array|string Html string on given $menuTemplate, sub menu array on empty $menuTemplate
     */
    public function getSingleMenuByModule($module, $menuTemplate = array())
    {
        $select = $this->Loader->Db->select("SELECT `system_pages`.*, `system_meta`.full_url FROM `system_pages` INNER JOIN `system_meta` ON `system_pages`.rec_status = :rec_status AND `system_pages`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table WHERE `system_pages`.module = :module ORDER BY `sequence`", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => 'system_pages', 'module' => $module));

        // Render Raw Fields
        $menu = $this->Loader->Compiler->renderRawFields('system_pages', $select, $this->lang);

        // Return menu as array or with rendered string.
        return empty($menuTemplate) ? $menu : $this->renderMenuTemplate($menu, $menuTemplate);
    }

    /**
     * Render Menu Template by given menu and template.
     * If template is empty array, then use default render template.
     * Template array contains 3 elements:
     *  - $template['root']: Default list li html type for menu.
     *  - $template['parent']: Parent list li html type. Used by that has subMenu array.
     *  - $template['menu']: Sub Menu's html type.
     *
     * @access public
     * @param array $menu Menu to render with template
     * @param array $template Template to render given menu
     * @return string|bool $result Html result as string on success, false on failure
     */
    public function renderMenuTemplate($menu, $template)
    {
        if (!empty($menu)) {
            $result = null;

            foreach ($menu as $value) {
                $singleTemplate = null;
                $parentTemplate = null;
                $subMenuTemplate = null;
                $i = empty($value['level']) ? 1 : $value['level'];
                $value['title'] = empty($value['menu_title']) ? $value['title'] : $value['menu_title'];

                // Active Link
                $fullUrlLength = strlen($value['full_url']);
                $lastChar = isset($this->full_url[$fullUrlLength]) ? $this->full_url[$fullUrlLength] : '';
                $value['activeClass'] = $fullUrlLength > 0 && substr($this->full_url, 0, $fullUrlLength) == $value['full_url'] && ($lastChar == '/' || $lastChar == '') ? 'active' : null;

                // Record Type
                if ($value['url'] != 'javascript:void(0)') {
                    if ($value['recordType'] == 'link') {
                        $value['full_url'] = !$this->Loader->Helper->isUrl($value['url']) ? BASEURL . $value['url'] : $value['url'];
                    } else {
                        $value['full_url'] = BASEURL_LANG . $value['full_url'];
                    }
                }

                // Single Template
                if (empty($value['subMenu'])) {
                    if (!empty($template[$value['level']]['single'])) {
                        $singleTemplate = $template[$value['level']]['single'];
                    } else {
                        while ($i > 0) {
                            if (!empty($template[$i]['single'])) {
                                $singleTemplate = $template[$i]['single'];
                                break;
                            }

                            $i--;
                        }
                    }

                    if (!is_null($singleTemplate)) {
                        $renderingTemplate = $singleTemplate;
                    } else {
                        return false;
                    }
                } // Parent Template
                else {
                    if (!empty($template[$value['level']]['parent'])) {
                        $parentTemplate = $template[$value['level']]['parent'];
                    } else {
                        while ($i > 0) {
                            if (!empty($template[$i]['parent'])) {
                                $parentTemplate = $template[$i]['parent'];
                                break;
                            }

                            $i--;
                        }
                    }

                    if (!is_null($parentTemplate)) {
                        $renderingTemplate = $parentTemplate;
                    } else {
                        return false;
                    }
                }

                // Sub Menu Template
                if (!empty($template[$value['level']]['subMenu'])) {
                    $subMenuTemplate = $template[$value['level']]['subMenu'];
                } else {
                    while ($i > 0) {
                        if (!empty($template[$i]['subMenu'])) {
                            $subMenuTemplate = $template[$i]['subMenu'];
                            break;
                        }

                        $i--;
                    }
                }

                if (!is_null($subMenuTemplate)) {
                    $subTemplate = $subMenuTemplate;
                } else {
                    return false;
                }

                $subTemplateSplit = explode('{subMenu}', $subTemplate);

                // Template Rendering
                preg_match_all('/{(.*?)}/', $renderingTemplate, $replaceList);
                $replaceList = array_unique($replaceList[1]);

                foreach ($replaceList as $field) {
                    $renderingTemplate = str_replace("{" . $field . "}", $value[$field], $renderingTemplate);
                }

                $listType = $renderingTemplate;
                $result .= $listType;

                if (!empty($value['subMenu'])) {
                    $result .= $subTemplateSplit[0];
                    $result .= $this->renderMenuTemplate($value['subMenu'], $template);
                    $result .= $subTemplateSplit[1];
                }

                $result .= '</li>';
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Return content of the current page or specific page.
     * if $where is not null, then get the content with given table, and field
     * $where array includes 3 keys:
     *  - table: table name of the content
     *  - row: table's source field
     *  - value: given field's value
     *
     * @access public
     * @param array $where Specific page for getting the content, optional
     * @return array|bool Content array
     */
    public function getContent($where = array())
    {
        // Content of Custom Request
        if (!empty($where) && is_array($where)) {
            if (array_key_exists('table', $where) && array_key_exists('row', $where) && array_key_exists('value', $where)) {
                $table = $where['table'];
                $row = $where['row'];
                $value = $where['value'];
                $lang = $where['lang'] != '' ? $where['lang'] : $this->lang;
            } else {
                return false;
            }
        } else {
            // Content of Current Full Url
            $table = $this->recordInfo['rec_table'];
            $row = 'id';
            $value = $this->recordInfo['rec_id'];
            $lang = $this->lang;
        }

        $select = $this->Loader->Db->select("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table WHERE `$table`.`$row` = :value", array('rec_status' => 0, 'lang' => $lang, 'value' => $value, 'rec_table' => $table));

        // Render Raw Fields
        $result = $this->Loader->Compiler->renderRawFields($table, $select, $this->lang);

        return $result[0];
    }

    /**
     * Return list of content of current module or by given module name.
     * Use $options to add extra parameters to select query:
     *  - $options['sort']: Add to to give sort condition to sql (Example: cat DESC)
     *  - $options['where']: Add to give where condition to sql (Example: cat > 10)
     *  - $options['limit']: Add to give limit condition to sql (Example: 10)
     *  - $options['tree']: Make the resulting data a tree (Example: true)
     *
     * @access public
     * @param string $table Module name, optional
     * @param array $options Options for data, optional
     * @return array List of contents
     */
    public function getContentList($table = null, $options = null)
    {
        // Module
        $table = is_null($table) ? $this->moduleName : $table;
        $treeOption = false;
        $paginationResult = array();

        // Defaults
        $where = null;
        $bindParameters = array();
        $sort = $this->tables[$table]['sort']['field'] . ' ' . $this->tables[$table]['sort']['direction'];
        $limit = null;
        $lang = $this->lang;

        if (is_array($options)) {
            // Where
            $where = array_key_exists('where', $options) ? " AND " . $options['where'] : null;

            // Bind Param
            $bindParameters = array_key_exists('bind', $options) && is_array($options['bind']) ? $options['bind'] : array();

            // Sort
            $sort = array_key_exists('sort', $options) ? $options['sort'] : $this->tables[$table]['sort']['field'] . ' ' . $this->tables[$table]['sort']['direction'];

            // Limit
            $limit = array_key_exists('limit', $options) ? " LIMIT " . $options['limit'] : null;

            // Tree
            $treeOption = array_key_exists('tree', $options) ? $options['tree'] : $treeOption;

            // Lang
            $lang = array_key_exists('lang', $options) ? $options['lang'] : $this->lang;
        }

        // Pagination
        if (is_array($options) && $options['pagination'] == true) {
            $selectTotal = $this->Loader->Db->selectOne("SELECT COUNT(*) as total FROM `$table` WHERE `rec_status` = :rec_status $where ORDER BY $sort $limit", array_merge(array('rec_status' => 0), $bindParameters));
            $each = empty($options['pagination']['each']) ? 10 : $options['pagination']['each'];
            $view = empty($options['pagination']['view']) ? 11 : $options['pagination']['view'];
            $parameter = empty($options['pagination']['parameter']) ? 'page' : $options['pagination']['parameter'];
            $total = ceil($selectTotal['total'] / $each);
            $page = isset($_GET[$parameter]) ? $_GET[$parameter] : 1;
            $paginationResult['prev'] = null;
            $paginationResult['next'] = null;

            if ($page < 1) {
                $currPage = 1;
                $start = 0;
            } else if ($page > $total) {
                $currPage = $total;
                $start = ($total - 1) * $each;
            } else {
                $currPage = $page;
                $start = ($page - 1) * $each;
            }

            $pageLink = $this->Loader->Helper->handleGetUrlParameters(array('add' => array($parameter)));

            if ($total > 1) {
                $center = $page;
                $minimumCenter = ceil($view / 2);
                $maximumCenter = ($total + 1) - $minimumCenter;

                if ($center < $minimumCenter) {
                    $center = $minimumCenter;
                } else if ($center > $maximumCenter) {
                    $center = $maximumCenter;
                }

                $leftPages = round($center - (($view - 1) / 2));
                $leftPages = $leftPages < 1 ? 1 : $leftPages;

                $rightPages = round($center + (($view - 1) / 2));
                $rightPages = $rightPages > $total ? $total : $rightPages;

                $i = 0;
                for ($p = $leftPages; $p <= $rightPages; $p++) {
                    $paginationResult['pages'][$i]['link'] = $pageLink . $p;
                    $paginationResult['pages'][$i]['number'] = $p;
                    $paginationResult['pages'][$i]['class'] = ($page == $p) || ($page == '' && $p == 0) ? 'active' : null;
                    $i++;
                }

                if ($currPage != 1) {
                    $paginationResult['prev'] .= $pageLink . ($page - 1);
                }

                if ($currPage != $total) {
                    $paginationResult['next'] .= $pageLink . ($currPage + 1);
                }
            }

            $limit = " LIMIT " . $start . "," . $each;
        }

        // Get Data
        if ($this->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
            $select = $this->Loader->Db->select("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table $where ORDER BY $sort $limit", array_merge(array('rec_status' => 0, 'lang' => $lang, 'rec_table' => $table), $bindParameters));
        } else {
            $select = $this->Loader->Db->select("SELECT * FROM `$table` WHERE rec_status = :rec_status $where ORDER BY $sort $limit", array_merge(array('rec_status' => 0), $bindParameters));
        }

        // Render Raw Fields
        $recordsResult = $this->Loader->Compiler->renderRawFields($table, $select, $lang);

        // Tree Option
        if ($treeOption) {
            // Isset 'Category' field
            $categoryField = $this->Loader->Compiler->getFieldByType($table, 'category');

            if ($categoryField !== false && $categoryField['options']['lookup']['table'] == $table) {
                $recordsResult = $this->Loader->Helper->createTreeBranch($recordsResult, $categoryField['key']);
            }
        }

        if (!empty($paginationResult)) {
            return array('records' => $recordsResult, 'pagination' => $paginationResult);
        } else {
            return $recordsResult;
        }
    }

    /**
     *
     * @param string $table
     * @return array $result
     */
    public function getOptionList($table)
    {
        $options = $this->Loader->Compiler->get($table, 'options');
        $result = array();

        foreach ($options as $option) {
            if ($option['lang'] == $this->lang || $option['lang'] == null) {
                if ($option['lang']) {
                    $data = $this->Loader->Db->selectOne("SELECT `option_key`, `option_value` FROM `system_options` WHERE `rec_table` = :rec_table AND `option_key` = :option_key AND `lang` =:lang", array('rec_table' => $table, 'option_key' => $option['key'], 'lang' => $option['lang']));
                } else {
                    $data = $this->Loader->Db->selectOne("SELECT `option_key`, `option_value` FROM `system_options` WHERE `rec_table` = :rec_table AND `option_key` = :option_key AND `lang` IS NULL", array('rec_table' => $table, 'option_key' => $option['key']));
                }

                $unserialized = unserialize($data['option_value']);
                $result[$data['option_key']] = $unserialized == false ? $data['option_value'] : $unserialized;
            }
        }

        return $result;
    }

    /**
     * Get To Url
     *
     * @param int $to
     * @return string $url
     */
    public function getToUrl($to)
    {
        $url = null;

        for ($i = 1; $i <= $to; $i++) {
            if (isset($this->url[$i])) {
                $url .= $this->url[$i] . '/';
            } else {
                break;
            }
        }

        return $url;
    }

    /**
     * Return record information
     *
     * @access public
     * @param String $url Full Url of the record
     * @param String $lang Language of the record
     * @return array | bool Table Info or 404 Info
     */
    public function getRecordInformations($url = null, $lang = null)
    {
        $result = array();

        if (!$this->isHome
        ()) {
            $full_url = is_null($url) ? $this->full_url : $url;
            $lang = is_null($lang) ? $this->lang : $lang;
            $result = $this->Loader->Db->selectOne("SELECT * FROM `system_meta` WHERE `lang` = :lang AND `full_url` = :full_url AND `rec_status` = :rec_status", array('lang' => $lang, 'full_url' => $full_url, 'rec_status' => 0));

            if (empty($result)) {
                $id = $this->Loader->Db->selectOne("SELECT `id` FROM `system_pages` WHERE `module` = :module AND `rec_status` = :rec_status", array('module' => '404', 'rec_status' => 0));

                if (!empty($id)) {
                    $result = $this->Loader->Db->selectOne("SELECT * FROM `system_meta` WHERE `lang` = :lang AND `rec_id` = :rec_id AND `rec_status` = :rec_status AND `rec_table` = :rec_table", array('lang' => $lang, 'rec_id' => $id['id'], 'rec_status' => 0, 'rec_table' => 'system_pages'));
                } else {
                    $result['title'] = 'Sayfa Bulunamadı!';
                }

                $result['status'] = 404;
            } else {
                $result['status'] = 200;
            }
        }

        return $result;
    }

    /**
     * Return module name by given url.
     *
     * @access public
     * @param String $url Url of the page
     * @param Bool $recursively Recursive option
     * @return string Module name
     */
    public function getModuleName($url = null, $recursively = true)
    {
        $url = empty($url) ? $this->full_url : $url;

        if(!empty($url)) {
            $record = $this->Loader->Db->selectOne("SELECT `rec_id` FROM `system_meta` WHERE `lang` = :lang AND `full_url` = :full_url", array('lang' => $this->lang, 'full_url' => $url));

            if (empty($record)) {
                return '404';
            } else {
                $record = $this->Loader->Db->selectOne("SELECT `rec_id` FROM `system_meta` WHERE `lang` = :lang AND `rec_table` :rec_table AND `full_url` = :full_url", array('lang' => $this->lang, 'rec_table' => 'system_pages', 'full_url' => $url));
                $select = $this->Loader->Db->selectOne("SELECT `module` FROM `system_pages` WHERE `id` = :id AND `rec_status` = :rec_status", array('id' => $record['rec_id'], 'rec_status' => 0));

                if ($recursively) {
                    if (!empty($select['module'])) {
                        return $select['module'];
                    } else {
                        $urlExploded = explode('/', $url);

                        for ($i = count($urlExploded); $i >= 0; $i--) {
                            $reducedUrl = implode('/', array_slice($urlExploded, 0, $i));
                            $record = $this->Loader->Db->selectOne("SELECT `rec_id` FROM `system_meta` WHERE `lang` = :lang AND `rec_table` = :rec_table AND `full_url` = :full_url", array('lang' => $this->lang, 'rec_table' => 'system_pages', 'full_url' => $reducedUrl));
                            $select = $this->Loader->Db->selectOne("SELECT `module` FROM `system_pages` WHERE `id` = :id AND `rec_status` = :rec_status", array('id' => $record['rec_id'], 'rec_status' => 0));

                            if (!empty($select['module'])) {
                                return $select['module'];
                            }
                        }

                        return false;
                    }
                } else {
                    if (!empty($select['module'])) {
                        return $select['module'];
                    } else {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Check $this->url array and if there is a module with same name in cms-content/controller/modules/ directory.
     * If there is, return module file content.
     * Else return false.
     *
     * @param Bool $recursively Recursive option
     * @return string module file on module exist, false on module not exist
     */
    public function getModulePath($recursively = true)
    {
        $default = $_SERVER['DOCUMENT_ROOT'] . JAMBI_THEME_CONTROLLER . 'modules/_default.php';
        $page = $_SERVER['DOCUMENT_ROOT'] . JAMBI_THEME_CONTROLLER . 'modules/' . $this->moduleName . '.php';

        if (file_exists($page)) {
            return $page;
        }

        if ($recursively) {
            for ($i = $this->url['count']; $i >= 0; $i--) {
                $reducedUrl = implode('/', array_slice($this->url, 0, $i));
                $module = $this->getModuleName($reducedUrl, false);
                $page = $_SERVER['DOCUMENT_ROOT'] . JAMBI_THEME_CONTROLLER . 'modules/' . $module . '.php';

                if (file_exists($page)) {
                    return $page;
                }
            }
        }

        if (file_exists($default)) {
            return $default;
        } else {
            return false;
        }
    }

    /**
     * Return full url by given module.
     *
     * @access public
     * @param string $module Module name in system_pages
     * @param string $lang Language of Url
     * @return string Full url of the module
     */
    public function getUrlByModule($module, $lang = null)
    {
        $lang = is_null($lang) ? $this->lang : $lang;
        $rec_id = $this->getFieldByField('id', 'module', $module);
        $result = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `lang` = :lang", array('rec_id' => $rec_id, 'lang' => $lang));
        return $result['full_url'];
    }

    /**
     * Return first sub element's full url by given module.
     *
     * @access public
     * @param string $module Module name in system_pages
     * @param string $lang Language of Url
     * @return string First sub element's full url
     */
    public function getFirstUrlByModule($module, $lang = null)
    {
        $lang = is_null($lang) ? $this->lang : $lang;
        $catID = $this->getFieldByField('id', 'module', $module);
        $rec_id = $this->Loader->Db->selectOne("SELECT `id` FROM `system_pages` WHERE `cat` = :id AND `rec_status` = :rec_status ORDER BY `sequence` ASC", array('id' => $catID, 'rec_status' => 0));
        $result = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $rec_id['id'], 'rec_table' => 'system_pages', 'lang' => $lang));
        return $result['full_url'];
    }

    /**
     * Return full url by given field of one data.
     *
     * @access public
     * @param string $sourceField Field name that known for data
     * @param string $sourceValue Value of known data, $sourceField
     * @param string $table Table name to get data
     * @param string $lang Language of Url
     * @return string Full url of the module
     */
    public function getFullUrlByField($sourceField, $sourceValue, $table = 'system_pages', $lang = null)
    {
        $lang = is_null($lang) ? $this->lang : $lang;
        $rec_id = $this->Loader->Db->selectOne("SELECT `id` FROM `$table` WHERE `$sourceField` = :sourceValue AND `rec_status` = :rec_status", array('sourceValue' => $sourceValue, 'rec_status' => 0));
        $result = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `lang` = :lang AND `rec_table` = :rec_table", array('rec_id' => $rec_id['id'], 'lang' => $lang, 'rec_table' => $table));
        return $result['full_url'];
    }

    /**
     * Return specific field from full url field.
     * Default table is system_pages.
     *
     * @access public
     * @param string $destinyField Field name that wanted to return
     * @param string $fullUrl Value of full url
     * @param string $table Table name to get data
     * @param string $lang Language of Url
     * @return string Wanted field's data
     */
    public function getFieldbyFullUrl($destinyField, $fullUrl, $table = 'system_pages', $lang = null)
    {
        $lang = is_null($lang) ? $this->lang : $lang;
        $id = $this->Loader->Db->selectOne("SELECT `rec_id` FROM `system_meta` WHERE `rec_table` = :rec_table AND `lang` = :lang AND `full_url` = :full_url", array('rec_table' => $table, 'lang' => $lang, 'full_url' => $fullUrl));
        $result = $this->Loader->Db->selectOne("SELECT `$destinyField` FROM `$table` WHERE `id` = :id AND `rec_status` = :rec_status", array('id' => $id['rec_id'], 'rec_status' => 0));
        return $result[$destinyField];
    }

    /**
     * Return specific field from any given field of one data.
     * Default table is system_pages.
     *
     * @access public
     * @param string $destinyField Field name that wanted to return
     * @param string $sourceField Field name that known for data
     * @param string $sourceValue Value of known data, $sourceField
     * @param string $table Table name to get data
     * @return string Wanted field's data
     */
    public function getFieldByField($destinyField, $sourceField, $sourceValue, $table = 'system_pages')
    {
        $result = $this->Loader->Db->selectOne("SELECT `$destinyField` FROM `$table` WHERE `$sourceField` = :sourceValue AND `rec_status` = :rec_status", array('sourceValue' => $sourceValue, 'rec_status' => 0));
        return stripslashes($result[$destinyField]);
    }

    /**
     * Return a field from a table by url recursively.
     * It looks the bottom of the hierarchy and goes to top until it finds anything not empty.
     *
     * @access public
     * @param string $targetField Target field for recursively
     * @param String $module Field's table name
     * @return string Field's value or empty string
     */
    public function getFieldRecursively($targetField, $module = 'system_pages')
    {
        $field = '';
        $urlList = array();
        $urlCounter = $this->url['count'];

        for ($i = 1; $i < $urlCounter + 1; $i++) {
            $temp = '';

            for ($j = 1; $j <= $i; $j++) {
                $temp .= $this->url[$j] . '/';
            }

            $temp = substr($temp, 0, -1);
            $urlList[$i] = $temp;
        }

        if ((!isset($field) || empty($field)) && ($urlCounter >= 0)) {
            while ($urlCounter >= 0 && empty($field)) {
                $parentField = $this->getFieldbyFullUrl($targetField, $urlList[$urlCounter--], $module);
                $field = $field == '' ? $parentField : $field;
            }
        }

        return $field;
    }

    /**
     * Return sibling records of the current record.
     * Return an array of links. Elements are below:
     *  - first: First record's link on table.
     *  - last: Last record's link on table.
     *  - next: Next record's link on table.
     *  - previous: Previous record's link on table.
     *
     * if $where is not null, then get the related records with given table, and field
     * $where array includes 3 keys:
     *  - table: table name of the content
     *  - row: table's source field
     *  - value: given field's value
     *
     * @access public
     * @param array $where Specific page for getting the related records, optional
     * @param bool $dependsCat
     * @return array|bool
     */
    public function getSiblingRecords($where = array(), $dependsCat = false)
    {
        if (empty($where)) {
            // Content of Current Page
            if (!$this->isHome()) {
                $table = $this->recordInfo['rec_table'];
                $rec_id = $this->recordInfo['rec_id'];
            } else {
                return false;
            }
        } else {
            // Content of Custom Request
            if (array_key_exists('table', $where) && array_key_exists('row', $where) && array_key_exists('value', $where)) {
                $table = $where['table'];
                $row = $where['row'];
                $select = $this->Loader->Db->selectOne("SELECT `id` FROM `$table` WHERE `$row` = :value AND `rec_status` = :rec_status", array('value' => $where['value'], 'rec_status' => 0));
                $rec_id = $select['id'];
            } else {
                return false;
            }
        }

        // Check Has Category
        if ($dependsCat) {
            $categoryField = $this->Loader->Compiler->getFieldByType($table, 'category');

            if ($categoryField !== false) {
                $categoryKey = $categoryField['key'];
                $categoryValue = $this->getFieldByField($categoryKey, 'id', $rec_id, $table);
            }
        }

        $data = array();
        $sequence = $this->Loader->Db->selectOne("SELECT `sequence` FROM `$table` WHERE `id` = :rec_id AND `rec_status` = :rec_status", array('rec_id' => $rec_id, 'rec_status' => 0));

        if ($this->tables[$table]['sort']['direction'] == 'ASC') {
            if ($this->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
                if (!$dependsCat) {
                    $data['first'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` ASC", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['last'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` DESC", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['next'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `sequence` > :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'sequence' => $sequence['sequence']));
                    $data['previous'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `sequence` < :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'sequence' => $sequence['sequence']));
                } else {
                    $data['first'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.$categoryKey = :category_value AND `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` ASC", array('category_value' => $categoryValue, 'rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['last'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.$categoryKey = :category_value AND `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` DESC", array('category_value' => $categoryValue, 'rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['next'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `$categoryKey` = :category_value AND `sequence` > :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'category_value' => $categoryValue, 'sequence' => $sequence['sequence']));
                    $data['previous'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `$categoryKey` = :category_value AND `sequence` < :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'category_value' => $categoryValue, 'sequence' => $sequence['sequence']));
                }
            } else {
                $data['first'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status ORDER BY `sequence` ASC", array('rec_status' => 0));
                $data['last'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status ORDER BY `sequence` DESC", array('rec_status' => 0));
                $data['next'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `sequence` > :sequence)", array('rec_status' => 0, 'sequence' => $sequence['sequence']));
                $data['previous'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `sequence` < :sequence)", array('rec_status' => 0, 'sequence' => $sequence['sequence']));
            }
        } else {
            if ($this->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
                if (!$dependsCat) {
                    $data['first'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` DESC", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['last'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` ASC", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['next'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `sequence` < :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'sequence' => $sequence['sequence']));
                    $data['previous'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `sequence` > :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'sequence' => $sequence['sequence']));
                } else {
                    $data['first'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.$categoryKey = :category_value AND `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` DESC", array('category_value' => $categoryValue, 'rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['last'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.$categoryKey = :category_value AND `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table ORDER BY `sequence` ASC", array('category_value' => $categoryValue, 'rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table));
                    $data['next'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `$categoryKey` = :category_value AND `sequence` < :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'category_value' => $categoryValue, 'sequence' => $sequence['sequence']));
                    $data['previous'] = $this->Loader->Db->selectOne("SELECT `$table`.*, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = :rec_status AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `$categoryKey` = :category_value AND `sequence` > :sequence)", array('rec_status' => 0, 'lang' => $this->lang, 'rec_table' => $table, 'category_value' => $categoryValue, 'sequence' => $sequence['sequence']));
                }
            } else {
                $data['first'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status ORDER BY `sequence` DESC", array('rec_status' => 0));
                $data['last'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status ORDER BY `sequence` ASC", array('rec_status' => 0));
                $data['next'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status AND `sequence` = (SELECT max(sequence) FROM `$table` WHERE `sequence` < :sequence)", array('rec_status' => 0, 'sequence' => $sequence['sequence']));
                $data['previous'] = $this->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `rec_status` = :rec_status AND `sequence` = (SELECT min(sequence) FROM `$table` WHERE `sequence` > :sequence)", array('rec_status' => 0, 'sequence' => $sequence['sequence']));
            }
        }

        return $this->Loader->Compiler->renderRawFields($table, $data, $this->lang, true);
    }

    /**
     * Return site navigation by current url.
     * Starts from home page and downs to current page.
     *
     * @access public
     * @return array $result Navigation array
     */
    public function getSiteNavigation()
    {
        $result = array();
        $url = null;
        $i = 1;

        if ($this->lang == $this->settings['default_lang']) {
            $lastUrl = rtrim(BASEURL, '/');
        } else {
            $lastUrl = BASEURL . $this->lang_small;
        }

        $result[0]['url'] = HOMEURL;
        $result[0]['title'] = $this->Loader->Helper->convertStr(stripslashes($this->labels['homePage']), 'capitalize');
        $result[0]['class'] = 'home';

        while (isset($this->url[$i]) && ($this->recordInfo['status'] != 404 || $i == 1)) {
            $url .= $this->url[$i];
            $query = $this->getRecordInformations($url);
            $url .= '/';
            $lastUrl .= '/' . $query['url'];
            $result[$i]['url'] = $lastUrl;
            $result[$i]['title'] = $this->Loader->Helper->convertStr(stripslashes($query['title']), 'capitalize');
            $i++;
        }

        return $result;
    }

    /**
     * Return search results by keyword
     * Default search column is title.
     *
     * @access public
     * @param string $keyword Searched keyword
     * @param array $extraCol Extra column to search on tables
     * @param bool $useDefaultCols true on use default columns, false on don't use.
     * @param string $table Specific table name
     * @return array $result Array of found results.
     */
    public function getSearchResult($keyword, $extraCol = null, $useDefaultCols = true, $table = null)
    {
        // Extra Data
        $extra = null;
        $keyword = strtolower($keyword);

        if ($this->Loader->Helper->checkArray($extraCol)) {
            foreach ($extraCol as $key => $value) {
                $extra .= " OR lower(" . $value . ") like :keyword";
            }

            if (!$useDefaultCols) {
                $extra = substr($extra, 3);
            }
        }

        // Where Options
        $extraOptions['where'] = $useDefaultCols ? "(lower(title$this->lang) like :keyword" . $extra . ")" : "(" . $extra . ")";
        $extraOptions['bind'] = ['keyword' => "%$keyword%"];

        $result = array();

        if (is_null($table)) {
            $tables = $this->Loader->Db->select("SELECT DISTINCT `rec_table` FROM `system_meta`");

            foreach ($tables as $value) {
                $result = array_merge($result, $this->getContentList($value['rec_table'], $extraOptions));
            }
        } else {
            $result = $this->getContentList($table, $extraOptions);
        }

        return $result;
    }

    /**
     * Return a label from the system_labels table by given label name.
     * It can be either file or content.
     *
     * @access public
     * @param string $label Label name that we want to return value
     * @return string Label's value
     */
    public function getLabel($label)
    {
        $select = $this->Loader->Db->selectOne("SELECT * FROM `system_labels` WHERE `label` = :label AND `rec_status` = :rec_status", array('label' => $label, 'rec_status' => 0));
        return stripslashes($select[$select['type'] . $this->lang]);
    }

    /**
     * Return all labels from the system_labels table.
     * Labels can be either file or content. Merge it while return.
     *
     * @access private
     * @return array|bool Array of all labels.
     */
    private function getAllLabels()
    {
        $select = $this->Loader->Db->select("SELECT `label`, `type`, `content$this->lang` AS content, `file$this->lang` AS file FROM `system_labels` WHERE `rec_status` = :rec_status ORDER BY `id` ASC", array('rec_status' => 0));

        if (!empty($select)) {
            $result = array();

            foreach ($select as $key => $value) {
                $result[$value['label']] = stripslashes($value[$value['type']]);
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Return a setting from the system_settings table by setting name parameter.
     *
     * @access public
     * @param String $settingName Name of the setting to return
     * @return string Setting's value
     */
    public function getSetting($settingName)
    {
        $lang = $this->lang == '' ? $this->Loader->languages['first'] : $this->lang;
        $select = $this->Loader->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = :option_key AND (`lang` = :lang OR `lang` IS NULL)", array('option_key' => $settingName, 'lang' => $lang));
        return stripslashes($select['option_value']);
    }

    /**
     * Return all settings from the system_settings table by current language.
     *
     * @access private
     * @return array|bool Array of all settings on success, false on empty settings array
     */
    private function getAllSettings()
    {
        $settings = $this->Loader->Db->select("SELECT `option_key`, `option_value` FROM `system_settings` WHERE `lang` = :lang OR lang IS NULL ORDER BY id ASC", array('lang' => $this->lang));

        if (!empty($settings)) {
            $result = array();

            foreach ($settings as $key => $value) {
                $result[$value['option_key']] = stripslashes($value['option_value']);
            }

            return $result;
        } else {
            return false;
        }
    }
}