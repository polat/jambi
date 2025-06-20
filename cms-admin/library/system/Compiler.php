<?php

/**
 *
 * Class Compiler
 *
 */
class Compiler
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Modules array
     * @var array $tables
     */
    private $tables;

    /**
     * Languages of project
     * @var array $languages
     */
    private $languages;

    /**
     * Compiled Modules array
     * @var array $compiledTables
     */
    public $compiledTables;

    /**
     * Connected table list
     * @var array $connectedTables
     */
    public $connectedTables;

    /**
     * BASEURL_LANG Const
     * @var array $baseurl_lang
     */
    public $baseurl_lang;

    /**
     * Compiler constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
        $this->tables = $this->Loader->tableStructure;
        $this->languages = $this->Loader->languages;
        $this->compiledTables = $this->compileTables();
    }

    /**
     * Get table name and check it.
     * If it is exist, return true.
     *
     * @access public
     * @param string $table Name of table
     * @return bool true | false
     */
    public function tableExist($table)
    {
        return !empty($table) && array_key_exists($table, $this->tables) ? true : false;
    }

    /**
     * Get table and return it's table key, if given table exist.
     *
     * @access public
     * @param String $table Name of table that wanted
     * @param String $key Name of key that wanted
     * @return array|bool
     */
    public function get($table, $key)
    {
        return isset($this->compiledTables[$table][$key]) ? $this->compiledTables[$table][$key] : false;
    }

    /**
     * Return given field type's features.
     *
     * @access public
     * @param String $table Name of table
     * @param String $fieldType Type name of field
     * @return array|bool
     */
    public function getFieldByType($table, $fieldType)
    {
        if ($this->tableExist($table)) {
            $fields = $this->get($table, 'fields');
            $fieldKey = array_search($fieldType, array_column($fields, 'type'));

            if ($fieldKey !== false) {
                if ($fieldType != 'permalink') {
                    return $fields[$fieldKey];
                } else {
                    $permalinkTitleArrayKey = array_search($fields[$fieldKey]['options']['attach'], array_column($fields, 'key'));
                    return array('permalink' => $fields[$fieldKey], 'permalinkTitle' => $fields[$permalinkTitleArrayKey]);
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @access private
     * @return bool true | false
     */
    private function isValidTableName($name)
    {
        $invalidTableName = array('media_library');

        return !in_array($name, $invalidTableName);
    }

    /**
     *
     * @access private
     * @return bool true | false
     */
    private function isValidFieldType($type)
    {
        $validFieldTypes = array(
            'hidden', 'text', 'textarea', 'editor', 'permalink', 'number', 'price',
            'password', 'file', 'date', 'dateRange', 'dateMultiple', 'time', 'select',
            'category', 'multipleSelect', 'radio', 'checkbox', 'gallery'
        );

        return in_array($type, $validFieldTypes);
    }

    /**
     *
     * @access private
     * @return bool true | false
     */
    private function isValidFieldKey($key)
    {
        $invalidFieldKeys = array(
            'sequence', 'rec_status', 'rec_author',
            'created', 'updated', 'level', 'subMenu'
        );

        return !in_array($key, $invalidFieldKeys);
    }

    /**
     *
     * @access private
     * @return bool true | false
     */
    private function setFieldListOption($field)
    {
        if ($field['options']['dynamic']) {
            return false;
        }

        $invalidFieldList = array(
            'hidden', 'editor', 'password', 'dateMultiple',
            'multipleSelect', 'checkbox', 'gallery'
        );

        return !in_array($field['type'], $invalidFieldList) ? $field['list'] : false;
    }

    /**
     * Return filtered fields
     *
     * @access private
     * @param array $fields field list.
     * @param array $groupOptions option list.
     * @return array filtered fields
     */
    private function filterFields($fields = array(), $groupOptions = array())
    {
        foreach ($fields as $key => $field) {
            $type = preg_replace("/\[([^\]]*)\]/", null, $field['type']);

            if ($this->isValidFieldType($type) && $this->isValidFieldKey($field['key'])) {
                $fields[$key]['options']['group']['id'] = $groupOptions['id'];
                $fields[$key]['options']['group']['title'] = $groupOptions['title'];
                $fields[$key]['list'] = $this->setFieldListOption($field);

                if ($type == 'permalink') {
                    $fields[$key]['lang'] = true;
                } else if ($type == 'gallery') {
                    $fields[$key]['lang'] = false;
                }
            } else {
                unset($fields[$key]);
            }
        }

        return $fields;
    }

    /**
     *
     * @access private
     * @return bool true | false
     */
    private function isValidOptionType($type)
    {
        $validOptionTypes = array(
            'text', 'textarea', 'editor', 'number', 'price', 'file',
            'date', 'dateRange', 'dateMultiple', 'time', 'select',
            'multipleSelect', 'radio', 'checkbox'
        );

        return in_array($type, $validOptionTypes);
    }

    /**
     * Return filtered fields
     *
     * @access private
     * @param array $options field list.
     * @param array $groupOptions option list.
     * @return array filtered fields
     */
    private function filterOptions($options = array(), $groupOptions = array())
    {
        foreach ($options as $key => $option) {
            if ($this->isValidOptionType($option['type'])) {
                $options[$key]['options']['group']['id'] = $groupOptions['id'];
                $options[$key]['options']['group']['title'] = $groupOptions['title'];
            } else {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * Return all or just some tables.
     * If $tables array is not given, then select $this->tables (all tables) for operation,
     * Else if $_tables is given, then use just given array as tables.
     * If there is no array provided, return false,
     * If an array is provided, return it's tables array.
     *
     * @access private
     * @param array|string $tables List of tables for custom table operation
     * @return bool|array Return false on failure, array on success
     */
    private function compileTables($tables = null)
    {
        $result = array();
        $tableList = array();

        if (empty($tables)) {
            $tableList = $this->tables;
        } else if (is_array($tables)) {
            foreach ($tables as $table) {
                $tableList[$table] = $this->tables[$table];
            }
        } else {
            $tableList[$tables] = $this->tables[$tables];
        }

        foreach ($tableList as $key => $table) {
            if ($this->isValidTableName($key)) {
                $result[$key]['name'] = $table['name'] = $key;
                $result[$key]['module'] = $table['module'];
                $result[$key]['title'] = !empty($table['title']) ? $table['title'] : $key;
                $result[$key]['description'] = $table['description'];
                $result[$key]['sort'] = !empty($table['sort']) ? $table['sort'] : array('field' => 'sequence', 'order' => 'ASC');
                $result[$key]['group'] = $table['group'];
                $result[$key]['view'] = isset($table['view']) ? $table['view'] : true;
                $result[$key]['type'] = !empty($table['type']) ? $table['type'] : 'list';
                $result[$key]['pagination'] = !empty($table['pagination']) ? $table['pagination'] : 25;

                if ($result[$key]['type'] == 'list') {
                    $result[$key]['fields'] = $this->compileFields($table);
                } else if ($result[$key]['type'] == 'option') {
                    $result[$key]['options'] = $this->compileOptions($table);
                }

                // Connected system_pages modules
                // if (!empty($table['module'])) {
                // $this->connectedTables['system_pages'][] = array('table' => $table['name'], 'dependency' => 'module', 'value' => $table['module']);
                //}
            }
        }

        return $result;
    }

    /**
     *
     * @access private
     */
    private function compileFields($table)
    {
        // Filter the fields
        $i = 0;
        $filteredFields = array();

        foreach ($table['fields'] as $fieldKey => $field) {
            $groupOptions = array();

            if (is_int($fieldKey)) {
                $field = array(0 => $field);
            } else {
                $groupOptions = array('id' => $i++, 'title' => $fieldKey);
            }

            $filteredFields = array_merge($filteredFields, $this->filterFields($field, $groupOptions));
        }

        // Permalink Title 'lang' should always be true
        $permalinkField = array_search('permalink', array_column($filteredFields, 'type'));

        if ($permalinkField !== false) {
            $titleKey = $filteredFields[$permalinkField]['options']['attach'];
            $permalinkTitleKey = array_search($titleKey, array_column($filteredFields, 'key'));
            $filteredFields[$permalinkTitleKey]['lang'] = true;
            $filteredFields[$permalinkTitleKey]['options']['text-transform'] = empty($filteredFields[$permalinkTitleKey]['options']['text-transform']) ? 'none' : $filteredFields[$permalinkTitleKey]['options']['text-transform'];
        }

        // Connected Tables
        $categoryField = array_search('category', array_column($filteredFields, 'type'));

        if ($categoryField !== false && $filteredFields[$categoryField]['options']['lookup']['table'] != $table['name']) {
            $this->connectedTables[$filteredFields[$categoryField]['options']['lookup']['table']][] = array('table' => $table['name'], 'dependency' => 'category', 'field' => $filteredFields[$categoryField]['key']);
        }

        // Process is begins
        $i = 0;
        $compiledFields = array();

        foreach ($filteredFields as $field) {
            $langList = null;
            $field['lang'] == true ? $langList = $this->languages['list'] : $langList[0] = $this->languages['list'][0];

            foreach ($langList as $lang => $value) {
                // Language Control
                $field['lang'] == true ? $compiledFields[$i]['lang'] = $lang : $lang = null;

                // Field Size Option
                preg_match("/\[(.*?)\]/", $field['type'], $fieldSize);

                $compiledFields[$i]['title'] = $field['title'];
                $compiledFields[$i]['key'] = $field['key'];
                $compiledFields[$i]['label'] = $field['key'] . $lang;
                $compiledFields[$i]['type'] = preg_replace("/\[([^\]]*)\]/", null, $field['type']);
                $compiledFields[$i]['size'] = !empty($fieldSize[1]) ? $fieldSize[1] : null;
                $compiledFields[$i]['list'] = $field['list'];

                if (is_array($field['options'])) {
                    if (isset($field['options']['lookup'])) {
                        $tableType = $this->tables[$field['options']['lookup']['table']]['type'] == 'option' ? 'options' : 'fields';
                        $tableFields = $this->tables[$field['options']['lookup']['table']][$tableType];
                        $fieldKey = $field['options']['lookup']['field'];
                        $lang = $field['options']['lookup']['lang'];

                        $arrayKey = array_search($fieldKey, array_column($tableFields, 'key'));

                        if ($tableFields[$arrayKey]['lang']) {
                            $lang = empty($lang) || !array_key_exists($lang, $this->languages['list']) ? $this->languages['default_lang'] : $lang;
                            $field['options']['lookup']['field'] = $fieldKey . $lang;
                        }
                    }

                    $compiledFields[$i]['options'] = $field['options'];
                }

                $i++;
            }
        }

        return $compiledFields;
    }

    /**
     * Return Rendered Raw Fields.
     *
     * @access public
     * @param string $table Table name
     * @param array $data Raw Data list
     * @param array $lang Language
     * @param bool $preserveKey Preserving key of the list
     * @return array $result
     */
    public function renderRawFields($table, array $data, $lang, $preserveKey = false)
    {
        $i = 0;
        $result = array();
        $fieldResult = null;
        $fields = $this->get($table, 'fields');

        $currencySymbols = array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        );

        foreach ($data as $key => $value) {
            if (is_array($fields) && !empty($value)) {
                $renderedFields = array();

                foreach ($fields as $field) {
                    $fieldKey = $field['lang'] == true ? $field['key'] . $lang : $field['key'];
                    $fieldValue = $value[$fieldKey];

                    if (array_key_exists($fieldKey, $value)) {
                        if ($field['options']['dynamic'] || $field['type'] == 'dateRange' || $field['type'] == 'dateMultiple' || $field['type'] == 'multipleSelect' || $field['type'] == 'checkbox') {
                            $fieldValue = unserialize($fieldValue);
                        }

                        if ($field['type'] == 'text' || $field['type'] == 'textarea' || $field['type'] == 'editor') {
                            if ($field['options']['dynamic']) {
                                if (!empty($fieldValue)) {
                                    foreach ($fieldValue as $k => $item) {
                                        $fieldResult[$k] = $this->Loader->Helper->htmlPathReplace(stripslashes($item), false);
                                    }
                                } else {
                                    $fieldResult = $fieldValue;
                                }

                                $fieldValue = null;
                            } else {
                                $fieldResult = $this->Loader->Helper->htmlPathReplace(stripslashes($fieldValue), false);
                            }
                        } else if ($field['type'] == 'permalink') {
                            $fieldResult = $fieldValue;
                            $renderedFields['full_url'] = $value['full_url'];
                            $renderedFields['full_link'] = $this->baseurl_lang . $value['full_url'];
                        } else if ($field['type'] == 'price') {
                            if ($field['options']['dynamic']) {
                                foreach ($fieldValue as $k => $item) {
                                    $fieldValue[$k] = array('original' => $item);
                                }
                            } else {
                                $fieldValue = array('original' => $fieldValue);
                            }

                            $fieldResult = $fieldValue;

                            if (!empty($field['options']['price_format'])) {
                                foreach ($field['options']['price_format'] as $currency => $format) {
                                    if ($field['options']['dynamic']) {
                                        foreach ($fieldValue as $k => $item) {
                                            $fieldResult[$currency][$k]['value'] = number_format($fieldValue[$k]['original'], $format['decimals'], $format['dec_point'], $format['thousands_sep']);
                                            $fieldResult[$currency][$k]['icon'] = $currencySymbols[$currency];
                                        }
                                    } else {
                                        $fieldResult[$currency]['value'] = number_format($fieldValue['original'], $format['decimals'], $format['dec_point'], $format['thousands_sep']);
                                        $fieldResult[$currency]['icon'] = $currencySymbols[$currency];
                                    }
                                }
                            }
                        } else if ($field['type'] == 'file') {
                            if ($field['options']['dynamic']) {
                                foreach ($fieldValue as $k => $item) {
                                    $fieldValue[$k] = array('original' => $item);
                                }
                            } else {
                                $fieldValue = array('original' => $fieldValue);
                            }

                            $fieldResult = $fieldValue;

                            if (isset($field['options']['crop']) && !empty($field['options']['crop'])) {
                                foreach ($field['options']['crop'] as $cropKey => $crop) {
                                    if (isset($crop['width']) && isset($crop['height'])) {
                                        $cropPath = $crop['width'] . 'x' . $crop['height'] . '/';
                                    } else {
                                        continue;
                                    }

                                    if ($field['options']['dynamic']) {
                                        foreach ($fieldValue as $k => $item) {
                                            $fieldResult[$k][$cropKey] = 'crop/' . $cropPath . $fieldValue[$k]['original'];
                                        }
                                    } else {
                                        $fieldResult[$cropKey] = 'crop/' . $cropPath . $fieldValue['original'];
                                    }
                                }
                            }
                        } else if ($field['type'] == 'gallery') {
                            $fieldResult = $this->Loader->Db->select("SELECT `name` as `original` FROM `system_files` WHERE `rec_table` = :rec_table AND `rec_id` = :id AND `field_name` = :field_name ORDER BY `sequence` ASC", array('rec_table' => $table, 'id' => $value['id'], 'field_name' => $field['key']));

                            if (!empty($fieldResult) && isset($field['options']['crop']) && !empty($field['options']['crop'])) {
                                foreach ($field['options']['crop'] as $cropKey => $crop) {
                                    if (isset($crop['width']) && isset($crop['height'])) {
                                        $cropPath = $crop['width'] . 'x' . $crop['height'] . '/';
                                    } else {
                                        continue;
                                    }

                                    foreach ($fieldResult as $imgKey => $img) {
                                        $fieldResult[$imgKey][$cropKey] = 'crop/' . $cropPath . $img['original'];
                                    }
                                }
                            }
                        } else {
                            $fieldResult = $fieldValue;
                        }

                        $renderedFields['id'] = $value['id'];
                        $renderedFields[$field['key']] = is_array($fieldResult) ? $fieldResult : stripslashes($fieldResult);
                        $fieldResult = null;
                    }
                }

                if (array_key_exists('sequence', $value)) {
                    $renderedFields['sequence'] = $value['sequence'];
                }

                if (array_key_exists('created', $value)) {
                    $renderedFields['created'] = $this->Loader->Helper->dateFormat($value['created'], 'Y-m-d - H:i');
                }

                if (array_key_exists('updated', $value)) {
                    $renderedFields['updated'] = $this->Loader->Helper->dateFormat($value['updated'], 'Y-m-d - H:i');
                }

                if ($preserveKey) {
                    $result[$key] = $renderedFields;
                } else {
                    $result[$i++] = $renderedFields;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @access private
     */
    private function compileOptions($table)
    {
        // Filter the options
        $i = 0;
        $filteredOptions = array();

        foreach ($table['options'] as $optionKey => $option) {
            $groupOptions = array();

            if (is_int($optionKey)) {
                $option = array(0 => $option);
            } else {
                $groupOptions = array('id' => $i++, 'title' => $optionKey);
            }

            $filteredOptions = array_merge($filteredOptions, $this->filterOptions($option, $groupOptions));
        }

        // Process is begins
        $i = 0;
        $compiledOptions = array();

        foreach ($filteredOptions as $option) {
            $langList = null;
            $option['lang'] == true ? $langList = $this->languages['list'] : $langList[0] = $this->languages['list'][0];

            foreach ($langList as $lang => $value) {
                // Field Size Option
                $compiledOptions[$i]['title'] = $option['title'];
                $compiledOptions[$i]['key'] = $option['key'];
                $compiledOptions[$i]['lang'] = $option['lang'] == true ? $lang : null;
                $compiledOptions[$i]['label'] = $option['key'] . $compiledOptions[$i]['lang'];
                $compiledOptions[$i]['type'] = $option['type'];

                if (is_array($option['options'])) {
                    if (isset($option['options']['lookup'])) {
                        $tableType = $this->tables[$option['options']['lookup']['table']]['type'] == 'option' ? 'options' : 'fields';
                        $tableOptions = $this->tables[$option['options']['lookup']['table']][$tableType];
                        $optionKey = $option['options']['lookup']['field'];
                        $lang = $option['options']['lookup']['lang'];

                        $arrayKey = array_search($optionKey, array_column($tableOptions, 'key'));

                        if ($tableOptions[$arrayKey]['lang']) {
                            $lang = empty($lang) || !array_key_exists($lang, $this->languages['list']) ? $this->languages['default_lang'] : $lang;
                            $option['options']['lookup']['field'] = $optionKey . $lang;
                        }
                    }

                    $compiledOptions[$i]['options'] = $option['options'];
                }

                $i++;
            }
        }

        return $compiledOptions;
    }
}