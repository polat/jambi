<?php


/**
 * Class Fields
 *
 * Process fields by field type and return proper value for showing in admin panel.
 */
class Fields
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Table of current data
     * @var string $table
     */
    private $table;

    /**
     * Fields of Table
     * @var array $fields
     */
    private $fields;

    /**
     * Dynamic Group Fields of Table
     * @var array $dynamicGroupFields
     */
    private $dynamicGroupFields;

    /**
     * @var array $field
     */
    private $field;

    /**
     * @var array $field
     */
    private $id;

    /**
     * Current row of field's data
     * @var array $data
     */
    private $data;

    /**
     * @var array $template
     */
    private $template;

    /**
     * @var array $disableOption
     */
    private $disableOption;

    /**
     * @var array $permalink
     */
    public $permalink;

    /**
     * @var array $gallery
     */
    public $gallery;

    /**
     * @var array $dynamic
     */
    public $dynamic;

    /**
     * @var array $groupFields
     */
    public $groupFields;

    /**
     * Result of the Fields
     * @var array $result
     */
    public $result;

    /**
     * Fields constructor.
     * Create a Field instance and with given parameters, return html for specific field type.
     *
     * @param $Loader Loader object
     * @param string $table Table of current data
     * @param integer|null $id Id of current data
     */
    public function __construct(Loader $Loader, $table, $id = null)
    {
        // Assignments
        $this->Loader = $Loader;
        $this->table = $table;
        $this->id = $id;

        if ($this->Loader->Compiler->get($this->table, 'type') == 'list') {
            $this->fields = $this->Loader->Compiler->get($this->table, 'fields');
            $this->data = $this->id ? $this->Loader->Db->selectOne("SELECT * FROM `" . $this->table . "` WHERE `id` = :id", array('id' => $this->id)) : null;
        } else if ($this->Loader->Compiler->get($this->table, 'type') == 'option') {
            $this->fields = $this->Loader->Compiler->get($this->table, 'options');
            $dataList = $this->Loader->Db->select("SELECT `option_key`, `lang`, `option_value` FROM `system_options` WHERE `rec_table` = :rec_table", array('rec_table' => $this->table));

            foreach ($dataList as $data) {
                $this->data[$data['option_key'] . $data['lang']] = $data['option_value'];
            }
        }

        // Template Settings
        $twigLoader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_VIEW);
        $this->twig = new Twig_Environment($twigLoader, array('cache' => $_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_CONTROLLER . 'twig-cache/admin', 'auto_reload' => true, 'autoescape' => false));
        $this->template = $this->twig->load('library/system/Fields.html.twig');

        // Calc Dynamic Fields
        foreach ($this->fields as $key => $field) {
            if ($field['options']['dynamic']) {
                $decoded = json_decode($this->data[$field['key'] . $field['lang']], true);
                $countGroup = is_null($decoded) ? 1 : count($decoded);
                $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']] = $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']] < $countGroup ? $countGroup : $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']];
            }
        }

        // Render Fields
        $this->result = $this->renderFields();
    }

    private function renderFields()
    {
        $fieldList = array();
        $groupList = array();
        $this->groupFields = false;

        // Rendering Fields
        foreach ($this->fields as $key => $field) {
            // Title of Permalink
            if (isset($field['options']['attach'])) {
                $field['options']['attach'] = $field['options']['attach'] . $field['lang'];
            }

            // Field adjustments
            $this->field = $field;
            $this->field['type'] = $this->getFieldType();
            $this->field['value'] = $this->getFieldValue();
            $this->disableOption = $this->getDisableOption();

            // Language of Field
            $lang = isset($field['lang']) ? $field['lang'] : $this->Loader->languages['first'];

            // Single Fields
            if (empty($field['options']['group']['title'])) {
                $this->groupFields = false;
                $fieldList[$lang][$key] = $this->getField();
            } // Group Fields
            else {
                $this->groupFields = true;
                $groupID = $field['options']['group']['id'];
                $groupList[$groupID][$lang]['key'] = $key;
                $groupList[$groupID][$lang]['title'] = $field['options']['group']['title'];

                if ($this->field['options']['dynamic'] == true && $this->field['options']['dynamicGroupID']) {
                    $groupList[$groupID][$lang]['multipleGroups'][$this->field['options']['dynamicGroupID']][] = $this->getField();
                } else {
                    $groupList[$groupID][$lang]['fields'] .= $this->getField();
                }
            }
        }

        // Group Fields Rendering
        if (!empty($groupList)) {
            foreach ($groupList as $key => $item) {
                foreach ($item as $lang => $value) {
                    if (!empty($value['multipleGroups'])) {
                        foreach ($value['multipleGroups'] as $groupID => $multipleGroup) {
                            $mergedGroups = array();

                            foreach ($multipleGroup as $groupItem) {
                                foreach ($groupItem as $itemKey => $itemValue) {
                                    $mergedGroups[$itemKey] .= $itemValue;
                                }
                            }

                            $value['fields'] .= $this->template->renderBlock('dynamicGroupFieldsContainer', array(
                                'fields' => $mergedGroups
                            ));
                        }
                    }

                    if (!empty($value['fields'])) {
                        $fieldList[$lang][$value['key']] .= $this->template->renderBlock('groupFieldsContainer', array(
                            'title' => $value['title'],
                            'fields' => $value['fields']
                        ));
                    }
                }
            }
        }

        // Sort list by key
        foreach ($this->Loader->languages['keys'] as $lang) {
            if (is_array($fieldList[$lang])) {
                ksort($fieldList[$lang]);
            }
        }

        return $fieldList;
    }

    /**
     * Return to Field's type
     *
     * @access private
     * @return string
     */
    private function getFieldType()
    {
        if ($this->field['options']['hidden'] === true || $this->field['options']['rank'][$this->Loader->Session->get('user_rank')] === 'hidden') {
            return 'hidden';
        } else {
            if ($this->field['options']['rank'][$this->Loader->Session->get('user_rank')] === 'disable') {
                $this->field['options']['disable'] = true;
            }

            return $this->field['type'];
        }
    }

    /**
     * Return to Field's Value
     *
     * @access private
     * @return string|null
     */
    private function getFieldValue()
    {
        if ($this->isNewRecord() && $this->Loader->Compiler->get($this->table, 'type') == 'list') {
            if (isset($this->field['options']['default']) && $this->field['type'] != 'dateMultiple' && $this->field['type'] != 'dateRange') {
                if ($this->field['options']['dynamic'] || $this->field['type'] == 'checkbox') {
                    return serialize(array(0 => $this->field['options']['default']));
                } else {
                    return $this->field['options']['default'];
                }
            } else {
                return null;
            }
        } else {
            if (is_string($this->data[$this->field['label']])) {
                return stripslashes($this->data[$this->field['label']]);
            } else {
                return $this->data[$this->field['label']];
            }
        }
    }

    /**
     *
     * @access private
     * @return array
     */
    private function fixDynamicFieldValue($field) {
        $field['value'] = unserialize($field['value']);

        if (is_array($field['value'])) {
            foreach ($field['value'] as $key => $item) {
                if (empty($item)) {
                    $field['value'][$key] = null;
                }
            }

            if (count($field['value']) < $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']]) {
                for ($i = count($field['value']); $i < $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']]; $i++) {
                    $field['value'][$i] = null;
                }
            }
        } else {
            for ($i = 0; $i < $this->dynamicGroupFields[$field['options']['dynamicGroupID']][$field['lang']]; $i++) {
                $field['value'][$i] = null;
            }
        }

        return $field['value'];
    }

    /**
     * It checks field for it is new record or existing record.
     *
     * @access private
     * @return bool
     */
    private function isNewRecord()
    {
        return empty($this->id) ? true : false;
    }

    /**
     * It checks field for disable option
     *
     * @access private
     * @return string|null
     */
    private function getDisableOption()
    {
        return $this->field['options']['disable'] === true ? 'readonly' : null;
    }

    /**
     * Return field html by field type
     *
     * @access private
     * @return string|array Html result by data types above.
     */
    private function getField()
    {
        if ($this->field['options']['dynamic']) {
            $this->dynamic = true;
        }

        switch ($this->field['type']) {
            case 'hidden':
                return $this->getHiddenField();
            case 'text':
                return $this->getTextField();
            case 'textarea':
                return $this->getTextAreaField();
            case 'editor':
                return $this->getEditorField();
            case 'permalink':
                return $this->getPermalinkField();
            case 'number':
                return $this->getNumberField();
            case 'price':
                return $this->getPriceField();
            case 'password':
                return $this->getPasswordField();
            case 'file':
                return $this->getFileField();
            case 'date':
                return $this->getDateField();
            case 'dateRange':
                return $this->getDateRangeField();
            case 'dateMultiple':
                return $this->getDateMultipleField();
            case 'time':
                return $this->getTimeField();
            case 'select':
                return $this->getSelectField();
            case 'category':
                return $this->getSelectField();
            case 'multipleSelect':
                return $this->getMultipleSelectField();
            case 'radio':
                return $this->getRadioField();
            case 'checkbox':
                return $this->getCheckboxField();
            case 'gallery':
                return $this->getGalleryField();
            default:
                return false;
        }
    }

    /**
     * Return hidden field html to print in admin panel.
     *
     * @access private
     * @return string Hidden html result
     */
    private function getHiddenField()
    {
        $blockName = $this->groupFields == false ? 'hidden' : 'hiddenInner';

        return $this->template->renderBlock($blockName, array(
            'field' => $this->field
        ));
    }

    /**
     * Return input field html to print in admin panel.
     *
     * @access private
     * @return string|array Text field
     */
    private function getTextField()
    {
        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicTextGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'single' => $item
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicText' : 'dynamicTextInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'text' : 'textInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'disableOption' => $this->disableOption
        ));
    }

    /**
     * Return textarea tag to print in admin panel.
     *
     * @access private
     * @return string|array Textarea field
     */
    private function getTextAreaField()
    {
        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicTextareaGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'single' => $item
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicTextarea' : 'dynamicTextareaInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'textarea' : 'textareaInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'disableOption' => $this->disableOption
        ));
    }

    /**
     * Return editor to print in admin panel.
     *
     * @access private
     * @return string Editor field
     */
    private function getEditorField()
    {
        $this->field['value'] = $this->Loader->Helper->htmlPathReplace($this->field['value'], false);
        $blockName = $this->groupFields == false ? 'editor' : 'editorInner';

        return $this->template->renderBlock($blockName, array(
            'field' => $this->field
        ));
    }

    /**
     * Return permalink input to print in admin panel.
     *
     * @access private
     * @return string Permalink field
     */
    private function getPermalinkField()
    {
        $this->permalink = true;
        $blockName = $this->groupFields == false ? 'permalink' : 'permalinkInner';

        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
        ));
    }

    /**
     * Return number input to print in admin panel.
     *
     * @access private
     * @return string|array Number field
     */
    private function getNumberField()
    {
        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicNumberGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'single' => $item
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicNumber' : 'dynamicNumberInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'number' : 'numberInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'disableOption' => $this->disableOption
        ));
    }

    /**
     * Return price input to print in admin panel.
     *
     * @access private
     * @return string|array Price field
     */
    private function getPriceField()
    {
        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicNumberGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'single' => $item
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicPrice' : 'dynamicPriceInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'price' : 'priceInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'disableOption' => $this->disableOption
        ));
    }

    /**
     * Return password field html to print in admin panel.
     *
     * @access private
     * @return string Password field
     */
    private function getPasswordField()
    {
        $labels['newpass'] = _('Yeni Şifre');
        $blockName = $this->groupFields == false ? 'password' : 'passwordInner';

        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'labels' => $labels
        ));
    }

    /**
     * Return file uploader input to print in admin panel.
     *
     * @access private
     * @return string|array File field
     */
    private function getFileField()
    {
        $yearFolder = date("Y");
        $monthFolder = date("m");
        $folderPath = $yearFolder . '/' . $monthFolder;

        if (!is_dir(ABSPATH . UPLOADURL . $yearFolder)) mkdir(ABSPATH . UPLOADURL . $yearFolder, 0755);
        if (!is_dir(ABSPATH . UPLOADURL . $folderPath)) mkdir(ABSPATH . UPLOADURL . $folderPath, 0755);

        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            $labels['selectFile'] = _('Dosya Seç');
            $fileManagerLink = JAMBI_ADMIN_CONTENT . 'plugins/filemanager/dialog.php?type=2&fldr=' . $folderPath . '&field_id=' . $this->field['label'] . 'Input';

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicFileGroup', array(
                        'field' => $this->field,
                        'labels' => $labels,
                        'fileManagerLink' => $fileManagerLink,
                        'single' => $item,
                        'key' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicFile' : 'dynamicFileInner';
                goto render;
            }
        } else {
            $labels['selectFile'] = _('Dosya Seç');
            $fileManagerLink = JAMBI_ADMIN_CONTENT . 'plugins/filemanager/dialog.php?type=2&fldr=' . $folderPath . '&field_id=' . $this->field['label'] . 'Input';
            $blockName = $this->groupFields == false ? 'file' : 'fileInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'labels' => $labels,
            'fileManagerLink' => $fileManagerLink
        ));
    }

    /**
     * Return date picker input to print in admin panel.
     *
     * @access private
     * @return string|array Date field
     */
    private function getDateField()
    {
        if ($this->field['options']['dynamic']) {
            if (!empty($this->field['value'])) {
                $this->field['value'] = unserialize($this->field['value']);

                foreach ($this->field['value'] as $key => $value) {
                    $this->field['value'][$key] = $this->Loader->Helper->dateFormat($value);
                }
            } else {
                $this->field['value'] = array(0 => null);
            }

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicDateGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y')),
                        'single' => $item,
                        'key' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicDate' : 'dynamicDateInner';

                return $this->template->renderBlock($blockName, array(
                    'field' => $this->field,
                    'disableOption' => $this->disableOption,
                    'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y'))
                ));
            }
        } else {
            $blockName = $this->groupFields == false ? 'date' : 'dateInner';
            $this->field['value'] = $this->Loader->Helper->dateFormat($this->field['value']);

            return $this->template->renderBlock($blockName, array(
                'field' => $this->field,
                'disableOption' => $this->disableOption,
                'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y'))
            ));
        }
    }

    /**
     * Return date picker input to print in admin panel.
     *
     * @access private
     * @return string|array DateRange field
     */
    private function getDateRangeField()
    {
        if ($this->field['options']['dynamic']) {
            if (!empty($this->field['value'])) {
                $this->field['value'] = unserialize($this->field['value']);

                foreach ($this->field['value']['start'] as $key => $value) {
                    $this->field['value']['start'][$key] = $this->Loader->Helper->dateFormat($this->field['value']['start'][$key]);
                    $this->field['value']['end'][$key] = $this->Loader->Helper->dateFormat($this->field['value']['end'][$key]);
                    $this->field['value']['between'][$key] = implode(',', $this->field['value']['between'][$key]);
                }
            } else {
                $this->field['value'] = array('start' => array(0 => null));
            }

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value']['start'] as $key => $value) {
                    $result[$key] .= $this->template->renderBlock('dynamicDateRangeGroup', array(
                        'field' => $this->field,
                        'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y')),
                        'key' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicDateRange' : 'dynamicDateRangeInner';

                return $this->template->renderBlock($blockName, array(
                    'field' => $this->field,
                    'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y'))
                ));
            }
        } else {
            $this->field['value'] = unserialize($this->field['value']);
            $this->field['value']['start'] = $this->Loader->Helper->dateFormat($this->field['value']['start']);
            $this->field['value']['end'] = $this->Loader->Helper->dateFormat($this->field['value']['end']);
            $this->field['value']['between'] = implode(',', $this->field['value']['between']);
            $blockName = $this->groupFields == false ? 'dateRange' : 'dateRangeInner';

            return $this->template->renderBlock($blockName, array(
                'field' => $this->field,
                'disableOption' => $this->disableOption,
                'placeholder' => $this->Loader->Helper->dateFormat(date('d.m.Y'))
            ));
        }
    }

    /**
     * Return date picker input to print in admin panel.
     *
     * @access private
     * @return string DateMultiple field
     */
    private function getDateMultipleField()
    {
        $this->field['value'] = unserialize($this->field['value']);

        if (!empty($this->field['value'])) {
            $this->field['value'] = implode(',', array_map(function ($value) {
                return "'" . $value . "'";
            }, $this->field['value']));
        } else {
            $this->field['value'] = null;
        }

        $blockName = $this->groupFields == false ? 'dateMultiple' : 'dateMultipleInner';

        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
        ));
    }

    /**
     * Return time input to print in admin panel.
     *
     * @access private
     * @return string|array Time field
     */
    private function getTimeField()
    {
        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicTimeGroup', array(
                        'field' => $this->field,
                        'single' => $item,
                        'key' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicTime' : 'dynamicTimeInner';

                return $this->template->renderBlock($blockName, array(
                    'field' => $this->field
                ));
            }
        } else {
            $blockName = $this->groupFields == false ? 'time' : 'timeInner';

            return $this->template->renderBlock($blockName, array(
                'field' => $this->field
            ));
        }
    }

    /**
     * Return select tag to print in admin panel.
     *
     * @access private
     * @return string|array Select field
     */
    private function getSelectField()
    {
        if (isset($this->field['options']['data']) && !empty($this->field['options']['data'])) {
            $options = array();
            $tableTitle = null;

            $i = 0;
            foreach ($this->field['options']['data'] as $key => $value) {
                $options[$i++] = array('key' => $key, 'value' => $value);
            }
        } else {
            $table = $this->field['options']['lookup']['table'];
            $fieldKey = $this->field['options']['lookup']['field'];
            $sort = !empty($this->field['options']['lookup']['sort']) ? $this->field['options']['lookup']['sort'] : 'sequence ASC';
            $where = null;
            $tableTitle = $this->Loader->Compiler->get($table, 'title');

            // Where
            if (isset($this->data['id']) && $this->data['id'] != '' && $this->table === $table) {
                $where = " AND id != " . $this->data['id'];
            }

            $options = $this->Loader->Db->select("SELECT `id` as `key`, `$fieldKey` as `value` FROM `$table` WHERE `rec_status` = 0 $where ORDER BY $sort");
        }

        $labels['zeroTitle'] = _('--Yok--');

        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicSelectGroup', array(
                        'field' => $this->field,
                        'labels' => $labels,
                        'disableOption' => $this->disableOption,
                        'options' => $options,
                        'single' => $item,
                        'tableTitle' => $tableTitle
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicSelect' : 'dynamicSelectInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'select' : 'selectInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'labels' => $labels,
            'disableOption' => $this->disableOption,
            'options' => $options,
            'tableTitle' => $tableTitle
        ));
    }

    /**
     * Return multiple select tag to print in admin panel.
     *
     * @access private
     * @return string|array MultipleSelect field
     */
    private function getMultipleSelectField()
    {
        $this->field['value'] = unserialize($this->field['value']);

        if (isset($this->field['options']['data']) && !empty($this->field['options']['data'])) {
            $options = array();
            $tableTitle = null;

            $i = 0;
            foreach ($this->field['options']['data'] as $key => $value) {
                $options[$i++] = array('key' => $key, 'value' => $value);
            }
        } else {
            $table = $this->field['options']['lookup']['table'];
            $fieldKey = $this->field['options']['lookup']['field'];
            $sort = !empty($this->field['options']['lookup']['sort']) ? $this->field['options']['lookup']['sort'] : 'sequence ASC';
            $where = null;
            $tableTitle = $this->Loader->Compiler->get($table, 'title');

            // Where
            if (isset($this->data['id']) && $this->data['id'] != '' && $this->table === $table) {
                $where = " AND id != " . $this->data['id'];
            }

            $options = $this->Loader->Db->select("SELECT `id` as `key`, `$fieldKey` as `value` FROM `$table` WHERE `rec_status` = 0 $where ORDER BY $sort");
        }

        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicMultipleSelectGroup', array(
                        'field' => $this->field,
                        'disableOption' => $this->disableOption,
                        'options' => $options,
                        'single' => $item,
                        'tableTitle' => $tableTitle
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicMultipleSelect' : 'dynamicMultipleSelectInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'multipleSelect' : 'multipleSelectInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'disableOption' => $this->disableOption,
            'options' => $options,
            'tableTitle' => $tableTitle
        ));
    }

    /**
     * Return radio buttons to print in admin panel.
     *
     * @access private
     * @return string|array Radio field
     */
    private function getRadioField()
    {
        if (isset($this->field['options']['data']) && !empty($this->field['options']['data'])) {
            $data = array();

            $i = 0;
            foreach ($this->field['options']['data'] as $key => $value) {
                $data[$i++] = array('key' => $key, 'value' => $value);
            }
        } else {
            $table = $this->field['options']['lookup']['table'];
            $fieldKey = $this->field['options']['lookup']['field'];
            $sort = !empty($this->field['options']['lookup']['sort']) ? $this->field['options']['lookup']['sort'] : 'sequence ASC';
            $where = null;

            // Where
            if (isset($this->data['id']) && $this->data['id'] != '' && $this->table === $table) {
                $where = " AND id != " . $this->data['id'];
            }

            $data = $this->Loader->Db->select("SELECT `id` as `key`, `$fieldKey` as `value` FROM `$table` WHERE `rec_status` = 0 $where ORDER BY $sort");
        }

        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicRadioGroup', array(
                        'field' => $this->field,
                        'value' => $item,
                        'data' => $data,
                        'index' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicRadio' : 'dynamicRadioInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'radio' : 'radioInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'data' => $data
        ));
    }

    /**
     * Return checkbox inputs to print in admin panel.
     *
     * @access private
     * @return string|array Checkbox field
     */
    private function getCheckboxField()
    {
        $this->field['value'] = unserialize($this->field['value']);

        if (isset($this->field['options']['data']) && !empty($this->field['options']['data'])) {
            $data = array();

            $i = 0;
            foreach ($this->field['options']['data'] as $key => $value) {
                $data[$i++] = array('key' => $key, 'value' => $value);
            }
        } else {
            $table = $this->field['options']['lookup']['table'];
            $fieldKey = $this->field['options']['lookup']['field'];
            $sort = !empty($this->field['options']['lookup']['sort']) ? $this->field['options']['lookup']['sort'] : 'sequence ASC';
            $where = null;

            // Where
            if (isset($this->data['id']) && $this->data['id'] != '' && $this->table === $table) {
                $where = " AND id != " . $this->data['id'];
            }

            $data = $this->Loader->Db->select("SELECT `id` as `key`, `$fieldKey` as `value` FROM `$table` WHERE `rec_status` = 0 $where ORDER BY $sort");
        }

        if ($this->field['options']['dynamic']) {
            $this->field['value'] = $this->fixDynamicFieldValue($this->field);

            if ($this->groupFields == true && !empty($this->field['options']['dynamicGroupID'])) {
                $result = array();

                foreach ($this->field['value'] as $key => $item) {
                    $result[$key] .= $this->template->renderBlock('dynamicCheckboxGroup', array(
                        'field' => $this->field,
                        'value' => $item,
                        'data' => $data,
                        'index' => $key
                    ));
                }

                return $result;
            } else {
                $blockName = $this->groupFields == false ? 'dynamicCheckbox' : 'dynamicCheckboxInner';
                goto render;
            }
        } else {
            $blockName = $this->groupFields == false ? 'checkbox' : 'checkboxInner';
            goto render;
        }

        render:
        return $this->template->renderBlock($blockName, array(
            'field' => $this->field,
            'data' => $data
        ));
    }

    /**
     * Return tag field html to print in admin panel.
     *
     * @access private
     * @return false
     */
    private function getGalleryField()
    {
        $this->gallery[] = $this->field;
        return false;
    }
}