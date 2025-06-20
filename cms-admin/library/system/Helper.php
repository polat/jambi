<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Helper
 *
 * Helper class for the system.
 */
class Helper
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Helper constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
    }

    /**
     * Get a variable and checks it if it's really an array and array count > 0.
     * If $array is an array and count > 0 then return true,
     * Else return false.
     *
     * @param mixed $array Variable to check
     * @return bool True on array, false on not array or empty array.
     */
    public function checkArray($array)
    {
        return is_array($array) && count($array) > 0 ? true : false;
    }

    /**
     * Create alert div and print it by given type.
     * - $type parameter can be 3 string:
     *   'success' : green success box
     *   'warning' : yellow warning box
     *   'danger' : red danger box
     *
     * @access public
     * @param String $text Text of the alert div
     * @param String $type Type of the alert div
     */
    public function alert($text, $type)
    {
        echo '<div class="alert alert-' . $type . '">' . $text . '</div>';
    }

    /**
     *
     */
    public function convertStr($str, $type)
    {
        switch ($type) {
            case 'capitalize':
                $type = MB_CASE_TITLE;
                break;
            case 'uppercase':
                $type = MB_CASE_UPPER;
                break;
            case 'lowercase':
                $type = MB_CASE_LOWER;
                break;
            case 'default':
                return $str;
        }

        return mb_convert_case($str, $type, "UTF-8");
    }

    /**
     * Check $date parameter for valid date string.
     * If it's valid, return as 'd.m.Y'(e.g. '21.11.2015') format.
     *
     * @access public
     * @param string $date Date as string
     * @param string $pattern Pattern of date
     * @return string Formatted date as string
     */
    public function dateFormat($date, $pattern = null)
    {
        if (!empty($date)) {
            $pattern = is_null($pattern) ? JAMBI_CONTENT_DATE_FORMAT : $pattern;
            return date($pattern, strtotime($date));
        }
    }

    /**
     * Get date parameter as Mysql format and return as proper Turkish string
     * Example parameter : 2014-11-21
     * Example return value: 'day' => 21 , 'month' => 11 , 'year' => 2014
     *
     * @param String $date Date as Mysql string format
     * @param Bool $setDate Set Date
     * @return array Date as Turkish string format in case of day, month, year
     */
    public function dateToStr($date, $setDate = true)
    {
        if ($setDate) {
            $date = date('d.m.Y', strtotime($date));
        }

        $str = explode('.', $date);
        $result = array();

        switch ($str[1]) {
            case 1:
                $str[1] = _('Ocak');
                break;
            case 2:
                $str[1] = _('Şubat');
                break;
            case 3:
                $str[1] = _('Mart');
                break;
            case 4:
                $str[1] = _('Nisan');
                break;
            case 5:
                $str[1] = _('Mayıs');
                break;
            case 6:
                $str[1] = _('Haziran');
                break;
            case 7:
                $str[1] = _('Temmuz');
                break;
            case 8:
                $str[1] = _('Ağustos');
                break;
            case 9:
                $str[1] = _('Eylül');
                break;
            case 10:
                $str[1] = _('Ekim');
                break;
            case 11:
                $str[1] = _('Kasım');
                break;
            case 12:
                $str[1] = _('Aralık');
                break;
        }

        $result['day'] = $str[0];
        $result['month'] = $str[1];
        $result['year'] = $str[2];

        return $result;
    }

    /**
     *
     *
     * @access public
     * @param String $name Name of the $_GET parameter
     * @return String | bool
     */
    public function get($name)
    {
        return isset($_GET[$name]) ? htmlspecialchars(filter_var($_GET[$name], FILTER_SANITIZE_STRING)) : false;
    }

    /**
     *
     *
     * @access public
     * @param String $name Name of the $_POST parameter
     * @return String | bool
     */
    public function post($name)
    {
        return isset($_POST[$name]) ? htmlspecialchars(filter_var($_POST[$name], FILTER_SANITIZE_STRING)) : false;
    }

    /**
     *
     *
     * @access public
     * @param array $parameters Name of the $_GET parameters
     * @param bool $existingUrl
     * @return string
     */
    public function handleGetUrlParameters($parameters, $existingUrl = true)
    {
        $questionMarkExp = explode('?', urldecode($_SERVER['REQUEST_URI']));
        $urlList = explode('&', $questionMarkExp[1]);
        $returnUrl = $questionMarkExp[0];
        $questionMarkExp[0] = htmlspecialchars(filter_var($questionMarkExp[0], FILTER_SANITIZE_STRING));
        $questionMarkExp[1] = htmlspecialchars(filter_var($questionMarkExp[1], FILTER_SANITIZE_STRING));
        $returnGet = '';

        if (isset($parameters['change']) && count($parameters['change']) > 0) {
            foreach ($parameters['change'] as $key => $parameter) {
                if (is_numeric($key)) {
                    // Decrease Parameter
                    if (isset($_GET[$parameter])) {
                        foreach ($urlList as $key2 => $url) {
                            if ($url == $parameter . '=' . $_GET[$parameter])
                                unset($urlList[$key2]);
                        }
                    }
                } else {
                    // Change Parameter
                    if (isset($_GET[$key])) {
                        foreach ($urlList as $key2 => $url) {
                            if ($url == $key . '=' . $_GET[$key])
                                $urlList[$key2] = $key . '=' . $parameter;
                        }
                    } else {
                        $urlList[] = $key . '=' . $parameter;
                    }
                }
            }
        }

        if (isset($parameters['add']) && count($parameters['add']) > 0) {
            foreach ($parameters['add'] as $key => $parameter) {
                if (is_numeric($key)) {
                    // Add Parameter Without Value
                    if (isset($_GET[$parameter])) {
                        foreach ($urlList as $key2 => $url) {
                            if ($url == $parameter . '=' . $_GET[$parameter])
                                unset($urlList[$key2]);
                        }
                    }

                    $urlList[] = $parameter . '=';
                } else {
                    // Add Parameter With Value
                    if (isset($_GET[$key])) {
                        foreach ($urlList as $key2 => $url) {
                            if ($url == $key . '=' . $_GET[$key])
                                $urlList[$key2] = $key . '=' . $parameter;
                        }
                    } else {
                        $urlList[] = $key . '=' . $parameter;
                    }
                }
            }
        }

        $urlList = array_values($urlList);

        foreach ($urlList as $key => $value) {
            if ($key < sizeof($urlList) && $returnGet !== '')
                $returnGet .= '&';
            $returnGet .= $value;
        }

        if (!empty($returnGet)) {
            return $existingUrl ? $returnUrl . '?' . $returnGet : '?' . $returnGet;
        } else {
            return $returnUrl;
        }
    }

    /**
     * Get string parameter and check if it's a string or not.
     * Return true, if it's a url, otherwise return false.
     *
     * @param String $string String to control if it's a url.
     * @return bool True on it's a url, false on it's not.
     */
    public function isUrl($string)
    {
        return preg_match("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $string) ? true : false;
    }

    /**
     * Create a web friendly URL slug from a string.
     *
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string $str
     * @param array $options
     * @return string
     */
    public function urlSlug($str, $options = array())
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',

            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    /**
     * Return given list as tree.
     * Recursively look for category field and append its subMenu array.
     * Initial $parentID is 0.
     *
     * @access public
     * @param array $list Raw data
     * @param string $categoryFieldName Name of Category Field
     * @param int $parentID Start Level
     * @param int $level Level of Branch
     * @return array Rearranged tree list
     */
    public function createTreeBranch($list, $categoryFieldName, $parentID = 0, $level = 0)
    {
        $tree = array();

        foreach ($list as $key => $item) {
            if ($item[$categoryFieldName] == $parentID) {
                $tree[$item['id']] = $item;
                $tree[$item['id']]['level'] = $level;
                $tree[$item['id']]['subMenu'] = $this->createTreeBranch($list, $categoryFieldName, $item['id'], $level + 1);
            }
        }

        return $tree;
    }

    /**
     * Return given html's image and link paths as proper upload url path.
     *  - if $type is true then Sent to the database
     *  - if $type is false then Calling the database
     * @access public
     * @param string $html Data to be processed
     * @param bool $type Replace type parameter
     * @return string Replaced html
     */
    public function htmlPathReplace($html, $type = true)
    {
        if (!empty($html)) {
            // Replace Type
            if ($type) {
                $uploadUrlLength = strlen(JAMBI_UPLOADS);
                $requestedPath = JAMBI_UPLOADS;
                $insteadPath = '/' . UPLOADURL;
            } else {
                $uploadUrlLength = strlen('/' . UPLOADURL);
                $requestedPath = '/' . UPLOADURL;
                $insteadPath = JAMBI_UPLOADS;
            }

            // Images
            preg_match_all('/src="([^"]+)"/', $html, $images);

            foreach ($images[1] as $key => $value) {
                if (substr($value, 0, $uploadUrlLength) == $requestedPath) {
                    $imgPath = substr(strstr($value, $requestedPath), $uploadUrlLength);
                    $html = str_replace('src="' . $images[1][$key] . '"', 'src="' . $insteadPath . $imgPath . '"', $html);
                }
            }

            // Links
            preg_match_all('/href="([^"]+)"/', $html, $links);

            foreach ($links[1] as $key => $value) {
                if (substr($value, 0, $uploadUrlLength) == $requestedPath) {
                    $linkPath = substr(strstr($value, $requestedPath), $uploadUrlLength);
                    $html = str_replace('href="' . $links[1][$key] . '"', 'href="' . $insteadPath . $linkPath . '"', $html);
                }
            }

            return $html;
        }
    }

    /**
     * Create sitemap files and robots.txt file. Overwrite old ones.
     *
     * @access public
     */
    public function createSitemapFiles()
    {
        if (DYNAMIC_SITEMAP) {
            $baseurl = rtrim(SITE_URL . BASEURL, '/');

            // Robot.txt
            $robotFile = ABSPATH . 'robots.txt';
            $robot = fopen($robotFile, 'w+');
            $robotContent =
                'User-agent: *' . PHP_EOL .
                'Disallow: /cms-admin/' . PHP_EOL .
                'Disallow: /cms-content/' . PHP_EOL .
                'Disallow: /cms-uploads/crop/' . PHP_EOL .
                'Disallow: /cms-uploads/thumbnail/' . PHP_EOL .
                'Disallow: /install/' . PHP_EOL .
                'Disallow: /test/' . PHP_EOL .
                'Allow: /cms-content/themes/default/content/*.css' . PHP_EOL .
                'Allow: /cms-content/themes/default/content/*.js' . PHP_EOL .
                'Sitemap: ' . $baseurl . '/sitemap-index.xml';
            fwrite($robot, $robotContent);
            fclose($robot);

            // Sitemap.xml
            $sitemap = new Sitemap($baseurl);
            $sitemap->setFilename('sitemap');
            $sitemap->setPath(ABSPATH);

            // Home Page Link
            $sitemap->addItem('', '1.0');

            foreach ($this->Loader->languages['list'] as $lang => $value) {
                $lowLang = strtolower($lang);
                $select = $this->Loader->Db->select("SELECT DISTINCT `rec_table` FROM `system_meta` WHERE `lang` = :lang", array('lang' => $lang));

                if ($this->checkArray($select)) {
                    $links = array();

                    foreach ($select as $s) {
                        $table = $s['rec_table'];
                        $links = array_merge($links, $this->Loader->Db->select("SELECT `$table`.created, `$table`.updated, `system_meta`.full_url FROM `$table` INNER JOIN `system_meta` ON `$table`.rec_status = 0 AND `$table`.id = `system_meta`.rec_id AND `system_meta`.lang = :lang AND `system_meta`.rec_table = :rec_table AND `system_meta`.sitemap = 1", array('lang' => $lang, 'rec_table' => $table)));
                    }

                    foreach ($links as $link) {
                        $lastmod = empty($link['updated']) ? $link['created'] : $link['updated'];
                        $defaultLang = $this->Loader->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = 'default_lang'");

                        if (!$this->isUrl($link['full_url'])) {
                            if ($lowLang == strtolower($defaultLang['option_value'])) {
                                $sitemap->addItem('/' . $link['full_url'], '0.9', 'monthly', $lastmod);
                            } else {
                                $sitemap->addItem('/' . $lowLang . '/' . $link['full_url'], '0.9', 'monthly', $lastmod);
                            }
                        }
                    }
                }
            }

            $sitemap->createSitemapIndex($baseurl . '/', 'Today');
        }
    }

    /**
     *
     * @access public
     */
    public function createTranslateFile($sourceLang, array $destinationLangs)
    {
        $fieldList = array();
        $sqlCode = array();
        $repetitiveListControl = array();

        foreach ($this->Loader->Compiler->compiledTables as $table => $compiledTable) {
            if ($compiledTable['view'] === true || $table == 'system_pages' || $table == 'system_labels') {
                foreach ($compiledTable['fields'] as $fieldKey => $fieldValue) {
                    if (isset($fieldValue['lang']) && in_array($fieldValue['type'], array('text', 'textarea', 'editor'))) {
                        $fieldList[$table]['fields'][$fieldValue['key']] = $fieldValue['title'];
                        $sqlCode[$fieldValue['lang']] = $sqlCode[$fieldValue['lang']] . ',`' . $fieldValue['label'] . '`';
                    }
                }

                if (!empty($fieldList[$table]['fields'])) {
                    $fieldList[$table]['table_title'] = $compiledTable['title'];

                    foreach ($this->Loader->languages['list'] as $langKey => $langValue) {
                        if($langKey == $sourceLang) {
                            $sql = '`id`' . $sqlCode[$langKey];
                            $tempData = $this->Loader->Db->select("SELECT $sql FROM `$table` WHERE `rec_status` = 0 OR `rec_status` = 1 ORDER BY rec_status ASC, sequence ASC");
                            $fieldList[$table]['data'][$langKey] = $this->Loader->Compiler->renderRawFields($table, $tempData, $langKey);

                            foreach ($fieldList[$table]['data'][$langKey] as $key => $value) {
                                unset($fieldList[$table]['data'][$langKey][$key]['id']);
                                unset($value['id']);

                                foreach ($value as $k => $v) {
                                    if(!empty($fieldList[$table]['data'][$langKey][$key][$k])) {
                                        $fieldList[$table]['data'][$langKey][$key][$k] = strip_tags($fieldList[$table]['data'][$langKey][$key][$k]);
                                    }
                                }
                            }
                        }
                    }
                }

                $sql = null;
                $sqlCode = array();
            }
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Tahoma');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(13);

        $titleStyles = [
            'font' => [
                'size' => 14,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $index = 0;
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        foreach ($fieldList as $key => $value) {
            $cell = 1;
            $spreadsheet->setActiveSheetIndex($index)->setCellValue('A' . 1, $this->Loader->languages['list'][$sourceLang]);
            $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($titleStyles);
            //$spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(24);

            foreach ($value['data'][$sourceLang] as $k => $datum) {
                foreach ($value['fields'] as $f => $c) {
                    if(!empty($datum[$f])) {
                        $cell++;
                        $spreadsheet->setActiveSheetIndex($index)->setCellValue('A' . $cell, $datum[$f]);
                        $spreadsheet->getActiveSheet()->getStyle('A' . $cell)->getAlignment()->setWrapText(true);

                        if(in_array($datum[$f], $repetitiveListControl)) {
                            $spreadsheet->getActiveSheet()->getStyle('A' . $cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        } else {
                            $repetitiveListControl[] = $datum[$f];
                        }
                    }
                }
            }

            foreach ($destinationLangs as $dk => $dv) {
                $spreadsheet->setActiveSheetIndex($index)->setCellValue($letters[$dk+1] . 1, $this->Loader->languages['list'][$dv]);
                $spreadsheet->getActiveSheet()->getStyle($letters[$dk+1] . 1)->applyFromArray($titleStyles);
                $spreadsheet->getActiveSheet()->getStyle($letters[$dk+1] . 1)->getAlignment()->setWrapText(true);
                $spreadsheet->getActiveSheet()->getColumnDimension($letters[$dk+1])->setWidth(50);
            }

            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(50);
            $spreadsheet->getActiveSheet()->setTitle($value['table_title']);
            $spreadsheet->createSheet();
            $index++;
        }

        $spreadsheet->removeSheetByIndex($index);
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->save('translate.xlsx');
    }
}