<?php

/**
 * Class DbSync
 *
 * Class that look for database changes and synchronize it.
 */
class DbSync
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Name of table to synchronize
     * @var string $table
     */
    public $table;

    /**
     * @var string $charset
     */
    public $charset;

    /**
     * @var string $collate
     */
    public $collate;

    /**
     * DbSync constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
        $this->charset = DB_CHARSET;
        $this->collate = DB_COLLATE;
    }

    /**
     *
     * @access public
     * @param string $table Name of table to create
     */
    public function syncTable($table)
    {
        $this->table = $table;

        switch ($this->Loader->Compiler->get($this->table, 'type')) {
            case 'list':
                $this->createTable();
                break;
            case 'option':
                $this->createOptions();
                break;
            default:
                $this->createTable();
        }
    }

    /**
     *
     * @access private
     */
    private function createTable()
    {
        if (!$this->Loader->Db->existsTable($this->table)) {
            $disallowSequenceTables = array('system_login_attempts', 'system_meta', 'system_users', 'system_options', 'system_settings');
            $disallowRecStatusTables = array('system_files', 'system_login_attempts', 'system_users', 'system_options', 'system_settings');
            $disallowRecAuthorTables = array('system_login_attempts', 'system_meta', 'system_options', 'system_settings');
            $disallowCreatedTables = array('system_login_attempts', 'system_meta', 'system_users', 'system_options', 'system_settings');
            $disallowUpdatedTables = array('system_files', 'system_login_attempts', 'system_meta', 'system_users', 'system_options', 'system_settings');

            $columns = 'id INT(11) AUTO_INCREMENT PRIMARY KEY';
            $columns .= !in_array($this->table, $disallowSequenceTables) ? ',sequence INT(11)' : null;
            $columns .= !in_array($this->table, $disallowRecStatusTables) ? ',rec_status INT(1)' : null;
            $columns .= !in_array($this->table, $disallowRecAuthorTables) ? ',rec_author INT(1)' : null;
            $columns .= !in_array($this->table, $disallowCreatedTables) ? ',created DATETIME' : null;
            $columns .= !in_array($this->table, $disallowUpdatedTables) ? ',updated DATETIME' : null;

            try {
                $this->Loader->Db->exec("CREATE TABLE IF NOT EXISTS `$this->table` ($columns) DEFAULT CHARACTER SET $this->charset COLLATE $this->collate");
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        $this->createTableFields();
    }

    /**
     *
     * @access private
     */
    private function createTableFields()
    {
        $fields = $this->Loader->Compiler->get($this->table, 'fields');
        $beforeLabel = null;

        foreach ($fields as $field) {
            $fieldType = $this->getFieldByType($field);
            $fieldLabel = $field['label'];
            $after = !is_null($beforeLabel) ? 'AFTER ' . $beforeLabel : null;

            if ($fieldType != false) {
                try {
                    if ($this->Loader->Db->existsColumnInTable($fieldLabel, $this->table)) {
                        $this->Loader->Db->exec("ALTER TABLE `$this->table` MODIFY $fieldLabel $fieldType $after");
                    } else {
                        $this->Loader->Db->exec("ALTER TABLE `$this->table` ADD $fieldLabel $fieldType $after");
                    }
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
            }

            $beforeLabel = $fieldLabel;
        }
    }

    /**
     *
     * @access private
     */
    private function createOptions()
    {
        $options = $this->Loader->Compiler->get($this->table, 'options');
        $newData = array();

        foreach ($options as $option) {
            if ($option['lang']) {
                $existRecord = $this->Loader->Db->selectOne("SELECT * FROM `system_options` WHERE `rec_table` = :rec_table AND `option_key` = :option_key AND `lang` = :lang", array('rec_table' => $this->table, 'option_key' => $option['key'], 'lang' => $option['lang']));
            } else {
                $existRecord = $this->Loader->Db->selectOne("SELECT * FROM `system_options` WHERE `rec_table` = :rec_table AND `option_key` = :option_key AND lang IS NULL", array('rec_table' => $this->table, 'option_key' => $option['key']));
            }

            if (empty($existRecord)) {
                $newData['rec_table'] = $this->table;
                $newData['option_key'] = $option['key'];
                $newData['lang'] = $option['lang'] ? $option['lang'] : NULL;
                $newData['option_value'] = isset($option['options']['default']) ? $option['options']['default'] : null;
                $this->Loader->Db->insert('system_options', $newData);
            }
        }
    }

    /**
     * Return a part of the sql string for a specific column type.
     *
     * @access private
     * @param array $field Field
     * @return string Part of the sql string for given field.
     */
    private function getFieldByType($field)
    {
        if ($field['options']['dynamic']) {
            return 'MEDIUMTEXT CHARACTER SET ' . $this->charset;
        }

        $size = $this->isValidFieldSize($field['size']) ? $field['size'] : $this->getDefaultFieldSize($field['type']);
        return $size . $this->setCharacterOption($field['type']);
    }

    /**
     *
     * @access private
     */
    private function isValidFieldSize($size)
    {
        $validSizes = array(
            'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT',
            'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL'
        );

        return in_array($size, $validSizes) ? $size : false;
    }

    /**
     *
     * @access private
     */
    private function getDefaultFieldSize($type)
    {
        $defaultFieldSizes = array(
            'text' => 'MEDIUMTEXT',
            'textarea' => 'MEDIUMTEXT',
            'editor' => 'MEDIUMTEXT',
            'permalink' => 'TEXT',
            'number' => 'INT(11)',
            'price' => 'DECIMAL(10,2)',
            'password' => 'VARCHAR(255)',
            'file' => 'TEXT',
            'date' => 'DATE',
            'dateRange' => 'MEDIUMTEXT',
            'dateMultiple' => 'MEDIUMTEXT',
            'time' => 'TIME',
            'select' => 'INT(11)',
            'category' => 'INT(11)',
            'multipleSelect' => 'TEXT',
            'radio' => 'VARCHAR(255)',
            'checkbox' => 'TEXT',
            'gallery' => 'INT(1)'
        );

        return $defaultFieldSizes[$type];
    }

    /**
     *
     * @access private
     */
    private function setCharacterOption($type)
    {
        $validTypes = array(
            'text', 'textarea', 'editor', 'permalink',
            'password', 'file', 'dateRange', 'dateMultiple',
            'multipleSelect', 'radio', 'checkbox'
        );

        return in_array($type, $validTypes) ? ' CHARACTER SET ' . $this->charset : null;
    }
}
