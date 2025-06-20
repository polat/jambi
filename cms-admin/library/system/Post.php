<?php

/**
 * Class Post
 *
 * Process fields by field type and return proper value for uploading fields data to database.
 */
class Post
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Field
     * @var array $field
     */
    private $field;

    /**
     * Field
     * @var array $data
     */
    private $data;

    /**
     * Post constructor.
     *
     * @var Loader object $Loader
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
    }

    /**
     *
     * @access public
     * @param array $field
     * @param array $data
     * @return string | bool
     */
    public function init(array $field, array $data)
    {
        $this->field = $field;
        $this->data = $data;

        return $this->getPostByField();
    }

    /**
     * Returns post fields depend on type of field.
     *
     * @access private
     * @return string Value result by data types above.
     */
    private function getPostByField()
    {
        if ($this->field['options']['dynamic']) {
            return $this->getDynamic();
        }

        switch ($this->field['type']) {
            case 'text':
                return $this->getText();
            case 'textarea':
                return $this->getTextArea();
            case 'editor':
                return $this->getEditor();
            case 'permalink':
                return $this->getPermalink();
            case 'number':
                return $this->getNumber();
            case 'price':
                return $this->getPrice();
            case 'password':
                return $this->getPassword();
            case 'file':
                return $this->getFile();
            case 'date':
                return $this->getDate();
            case 'dateRange':
                return $this->getDateRange();
            case 'dateMultiple':
                return $this->getDateMultiple();
            case 'time':
                return $this->getTime();
            case 'select':
                return $this->getSelect();
            case 'category':
                return $this->getSelect();
            case 'multipleSelect':
                return $this->getMultipleSelect();
            case 'radio':
                return $this->getRadio();
            case 'checkbox':
                return $this->getCheckbox();
            default:
                return false;
        }
    }

    /**
     * Return input value for upload to database.
     *
     * @access private
     * @return string Value of input
     */
    private function getText()
    {
        if (empty($this->field['options']['text-transform']) || $this->field['options']['text-transform'] == 'none') {
            return $this->data[$this->field['label']];
        } else {
            return $this->Loader->Helper->convertStr($this->data[$this->field['label']], $this->field['options']['text-transform']);
        }

    }

    /**
     * Return textarea value for upload to database.
     *
     * @access private
     * @return string Value of textarea
     */
    private function getTextArea()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Return editor value for upload to database.
     *
     * @access private
     * @return string Value of editor
     */
    private function getEditor()
    {
        return $this->Loader->Helper->htmlPathReplace($this->data[$this->field['label']]);
    }

    /**
     * Return permalink value for upload to database.
     *
     * @access private
     * @return string Value of permalink input
     */
    private function getPermalink()
    {
        if (empty($this->data[$this->field['label']]) && !empty($this->data[$this->field['options']['attach'] . $this->field['lang']])) {
            return $this->Loader->Helper->urlSlug($this->data[$this->field['options']['attach'] . $this->field['lang']]);
        } else {
            return $this->data[$this->field['label']];
        }
    }

    /**
     * Return number field value for upload to database.
     *
     * @access private
     * @return string Value of number input
     */
    private function getNumber()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Return price field value for upload to database.
     *
     * @access private
     * @return string Value of price input
     */
    private function getPrice()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Return password value for upload to database.
     *
     * @access private
     * @return string Value of password
     */
    private function getPassword()
    {
        if (!empty($this->data[$this->field['label']])) {
            return password_hash($this->data[$this->field['label']], PASSWORD_DEFAULT);
        } else {
            return false;
        }
    }

    /**
     * Return file name for upload to database.
     *
     * @access private
     * @return string Value of file name
     */
    private function getFile()
    {
        if (strstr($this->data[$this->field['label']], JAMBI_UPLOADS)) {
            return substr($this->data[$this->field['label']], strlen(JAMBI_UPLOADS));
        } else {
            return $this->data[$this->field['label']];
        }
    }

    /**
     * Return date value as proper Mysql datetime format for upload to database.
     *
     * @access private
     * @return string Value of formatted datetime
     */
    private function getDate()
    {
        return $this->Loader->Helper->dateFormat($this->data[$this->field['label']], 'Y-m-d');
    }

    /**
     * Return date value as proper Mysql datetime format for upload to database.
     *
     * @access private
     * @return string Value of formatted datetime
     */
    private function getDateRange()
    {
        $betweenDates = explode(',', $this->data[$this->field['label'] . 'Between']);

        foreach ($betweenDates as $key => $betweenDate) {
            $betweenDates[$key] = $this->Loader->Helper->dateFormat($betweenDate, 'Y-m-d');
        }

        return serialize(array('start' => $this->Loader->Helper->dateFormat($this->data[$this->field['label'] . 'Start'], 'Y-m-d'), 'end' => $this->Loader->Helper->dateFormat($this->data[$this->field['label'] . 'End'], 'Y-m-d'), 'between' => $betweenDates, 'count' => $this->data[$this->field['label'] . 'Count']));
    }

    /**
     * Return date value as proper Mysql datetime format for upload to database.
     *
     * @access private
     * @return string Value of formatted datetime
     */
    private function getDateMultiple()
    {
        if (!empty($this->data[$this->field['label']])) {
            $multipleDates = explode(',', $this->data[$this->field['label']]);

            foreach ($multipleDates as $key => $multipleDate) {
                $multipleDates[$key] = $this->Loader->Helper->dateFormat($multipleDate, 'Y-m-d');
            }

            return serialize($multipleDates);
        } else {
            return false;
        }
    }

    /**
     * Return time value for upload to database.
     *
     * @access private
     * @return string Value of select input
     */
    private function getTime()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Return select value for upload to database.
     *
     * @access private
     * @return string Value of select input
     */
    private function getSelect()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Implode multiple values and return it for upload to database.
     *
     * @access private
     * @return string Value of imploded multiple input
     */
    private function getMultipleSelect()
    {
        if (!empty($this->data[$this->field['label']]) && is_array($this->data[$this->field['label']])) {
            return serialize($this->data[$this->field['label']]);
        } else {
            return null;
        }
    }

    /**
     * Return radio value for upload to database.
     *
     * @access private
     * @return string Value of radio input
     */
    private function getRadio()
    {
        return $this->data[$this->field['label']];
    }

    /**
     * Return checkbox value for upload to database.
     *
     * @access private
     * @return string Value of checkbox input
     */
    private function getCheckbox()
    {
        if (!empty($this->data[$this->field['label']]) && is_array($this->data[$this->field['label']])) {
            return serialize($this->data[$this->field['label']]);
        } else {
            return null;
        }
    }

    /**
     * Return date value as proper Mysql datetime format for upload to database.
     *
     * @access private
     * @return string Value of formatted datetime
     */
    private function getDynamic()
    {
        $result = array();

        if ($this->field['type'] == 'text' || $this->field['type'] == 'textarea') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[$key] = $item;
            }
        } else if ($this->field['type'] == 'price') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[$key] = number_format($item, 2, ".", "");
            }
        } else if ($this->field['type'] == 'file') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                if (strstr($item, JAMBI_UPLOADS)) {
                    $result[$key] = substr($item, strlen(JAMBI_UPLOADS));
                } else {
                    $result[$key] = $item;
                }
            }
        } else if ($this->field['type'] == 'date') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[$key] = $this->Loader->Helper->dateFormat($item, 'Y-m-d');
            }
        } else if ($this->field['type'] == 'dateRange') {
            foreach ($this->data[$this->field['label'] . 'Start'] as $key => $start) {
                $startDates[$key] = $this->Loader->Helper->dateFormat($start, 'Y-m-d');
            }

            foreach ($this->data[$this->field['label'] . 'End'] as $key => $end) {
                $endDates[$key] = $this->Loader->Helper->dateFormat($end, 'Y-m-d');
            }

            foreach ($this->data[$this->field['label'] . 'Between'] as $key => $item) {
                $betweenDates[$key] = explode(',', $item);

                foreach ($betweenDates[$key] as $k => $betweenDate) {
                    $betweenDates[$key][$k] = $this->Loader->Helper->dateFormat($betweenDate, 'Y-m-d');
                }
            }

            $result = array('start' => $startDates, 'end' => $endDates, 'between' => $betweenDates, 'count' => $this->data[$this->field['label'] . 'Count']);
        } else if ($this->field['type'] == 'multipleSelect') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[key($item)][] = reset($item);
            }
        } else if ($this->field['type'] == 'radio') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[$key] = reset($item);
            }
        } else if ($this->field['type'] == 'checkbox') {
            foreach ($this->data[$this->field['label']] as $key => $item) {
                $result[key($item)][] = reset($item);
            }
        } else {
            $result = $this->data[$this->field['label']];
        }

        return serialize($result);
    }
}