<?php

/**
 *
 * Class SaveList
 *
 */
class SaveList
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Name of the record table
     * @var string $table
     */
    private $table;

    /**
     * Fields
     * @var array $fields
     */
    private $fields;

    /**
     * Data of the record
     * @var array $post
     */
    private $data;

    /**
     * Record ID
     * @var integer | array $id
     */
    private $id;

    /**
     * Last ID of Record Table
     * @var integer $rec_id
     */
    private $rec_id;

    /**
     * Data to be recorded
     * @var array $recordData
     */
    private $recordData;

    /**
     * Identity Labels
     * @var array $identityLabels
     */
    private $identityLabels;

    /**
     * Full Url of the record
     * @var array $full_url
     */
    private $full_url;

    /**
     * Short Url of the record
     * @var array $short_url
     */
    private $short_url;

    /**
     * Permalink field control
     * @var bool $permalink
     */
    private $permalink;

    /**
     * Key of permalink field
     * @var string $permalinkKey
     */
    private $permalinkKey;

    /**
     * Title of permalink field
     * @var string $permalinkTitle
     */
    private $permalinkTitle;

    /**
     * category field control
     * @var bool $category
     */
    private $category;

    /**
     * Key of category field
     * @var string $categoryKey
     */
    private $categoryKey;

    /**
     * Lookup table name of category field
     * @var string $lookupTable
     */
    private $lookupTable;

    /**
     * Result messages of class
     * @var array $result
     */
    public $result;

    /**
     * SaveList constructor.
     *
     * @var Loader object
     * @var string $table Name of the record table
     * @var array $data data of the record
     */
    public function __construct(Loader $Loader, $table, array $data)
    {
        $this->Loader = $Loader;
        $this->Post = new Post($this->Loader);
        $this->table = $table;
        $this->fields = $this->Loader->Compiler->get($this->table, 'fields');
        $this->data = $data;
        $this->id = $this->data['id'];

        $this->prepareRecordData();
    }

    /**
     * It checks added record for it is new record or existing record.
     *
     * @access private
     * @return bool
     */
    private function isNewRecord()
    {
        return empty($this->id) ? true : false;
    }

    /**
     * It's checks whether a permalink area.
     *
     * @access private
     * @return bool
     */
    private function issetPermalink()
    {
        return $this->permalink;
    }

    /**
     * It's checks whether a category area.
     *
     * @access private
     * @return bool
     */
    private function issetCategory()
    {
        return $this->category;
    }

    /**
     * It prepare and organize data to will be added.
     *
     * @access private
     * @return void
     */
    private function prepareRecordData()
    {
        $i = 0;

        foreach ($this->fields as $field) {
            $post = $this->Post->init($field, $this->data);

            switch ($field['type']) {
                case 'file':
                    if ($field['options']['dynamic']) {
                        $this->recordData[$field['label']] = $post;

                        if (!empty($post)) {
                            $imageProcessList = array();

                            foreach (unserialize($post) as $key => $item) {
                                $imageProcessList[$key]['name'] = $item;
                            }

                            $this->imageProcess($field, $imageProcessList);
                        }
                    } else if (!empty($post)) {
                        $this->imageProcess($field, array(0 => array('name' => $post)));
                    }

                    $this->recordData[$field['label']] = $post;
                    break;
                case 'editor':
                    $this->recordData[$field['label']] = $post;
                    break;
                case 'permalink':
                    $this->permalink = true;
                    $this->permalinkKey = $field['key'];
                    $this->permalinkTitle = $field['options']['attach'];
                    $this->data[$field['label']] = $this->recordData[$field['label']] = $post;
                    break;
                case 'password':
                    if (!empty($post)) {
                        $this->recordData[$field['label']] = $post;
                    }

                    break;
                case 'category':
                    $this->lookupTable = empty($field['options']['lookup']['table']) ? $this->table : $field['options']['lookup']['table'];
                    $this->category = true;
                    $this->categoryKey = $field['key'];
                    $this->recordData[$field['label']] = $post;
                    break;
                case 'gallery':
                    if (!$this->isNewRecord()) {
                        $images = $this->Loader->Db->select("SELECT `name` FROM `system_files` WHERE `rec_table` = :rec_table AND `rec_id` = :id AND `field_name` = :field_name ORDER BY `sequence` ASC", array('rec_table' => $this->table, 'id' => $this->id, 'field_name' => $field['key']));
                        $this->imageProcess($field, $images);
                    }

                    break;
                default:
                    if ($post !== false) {
                        $this->recordData[$field['label']] = $post;
                    }
            }

            // Select identity fields
            if ($field['options']['identity']) {
                $this->identityLabels[$i++] = array('label' => $field['label'], 'title' => $field['title'], 'lang' => $field['lang']);
            }
        }

        // Process is begins
        $this->handleRecord();
    }

    /**
     *
     * @access private
     * @return void
     */
    private function handleRecord()
    {
        // Identity field control
        if ($this->identityFieldControl()) {
            if ($this->issetPermalink()) {
                // Create Full Url
                $urlResult = $this->createFullUrl($this->table, $this->data);

                foreach ($this->Loader->languages['list'] as $key => $value) {
                    $this->recordData[$this->permalinkKey . $key] = $urlResult[$key]['url'];
                    $this->full_url[$key] = $urlResult[$key]['full_url'];
                    $this->short_url[$key] = $urlResult[$key]['short_url'];
                }
            }

            // Re-Order Hierarchy of Records
            $this->reOrderHierarchy();

            // Process to Record
            $this->isNewRecord() == true ? $this->saveRecord() : $this->updateRecord();
        }
    }

    /**
     * It adds current record to the database.
     *
     * @access private
     * @return bool
     */
    private function saveRecord()
    {
        $this->recordData['rec_status'] = empty($this->data['rec_status']) ? 0 : $this->data['rec_status'];
        $this->recordData['rec_author'] = empty($this->Loader->Session->get('user_id')) ? 0 : $this->Loader->Session->get('user_id');
        $this->recordData['created'] = date(JAMBI_ADMIN_DATE_FORMAT);

        $data = array();
        array_push($data, $this->recordData);

        if ($this->Loader->Db->insertMultiple($this->table, $data)) {
            $this->rec_id = $this->Loader->Db->getLastId($this->table);
            $this->saveMetaData();

            if ($this->issetPermalink()) {
                $this->Loader->Helper->createSitemapFiles();
            }

            $this->result = array('result' => true, 'message' => _('Kayıt eklendi.'));
        } else {
            $this->result = array('result' => false, 'type' => 0, 'message' => _('Kayıt eklenirken bir hata oluştu!'));
        }

        $this->Loader->Hooks->do_action('save_after', array('table' => $this->table, 'id' => $this->rec_id, 'data' => $data, 'result' => $this->result));
    }

    /**
     * When want to make changes to an existing record.
     *
     * @access private
     * @return array
     */
    private function updateRecord()
    {
        $this->recordData['rec_status'] = empty($this->data['rec_status']) ? 0 : $this->data['rec_status'];
        $this->recordData['updated'] = date(JAMBI_ADMIN_DATE_FORMAT);
        $old_data = $this->Loader->Db->selectOne("SELECT * FROM `$this->table` WHERE `id` = :id", array('id' => $this->id));

        if ($this->Loader->Db->update($this->table, $this->recordData, "id='$this->id'")) {
            $this->rec_id = $this->id;
            $this->saveMetaData();
            $this->updateRelatedRecords($this->table, $this->rec_id);

            if ($this->issetPermalink()) {
                $this->Loader->Helper->createSitemapFiles();
            }

            $this->result = array('result' => true, 'message' => _('Değişiklik yapıldı.'));
        } else {
            $this->result = array('result' => false, 'type' => 0, 'message' => _('Değişiklik yapılamadı!'));
        }

        $this->Loader->Hooks->do_action('update_after', array('table' => $this->table, 'id' => $this->rec_id, 'data' => $this->recordData, 'old_data' => $old_data, 'result' => $this->result));
    }

    /**
     *
     * @access private
     * @param string $table
     * @param array $data
     * @return array | bool
     */
    private function createFullUrl($table, $data)
    {
        $permalink = $this->Loader->Compiler->getFieldByType($table, 'permalink');

        if ($permalink !== false) {
            $category = $this->Loader->Compiler->getFieldByType($table, 'category');
            $urlPrefix = $full_url = $result = array();

            foreach ($this->Loader->languages['list'] as $key => $value) {
                if ($this->Loader->Helper->isUrl($data[$permalink['permalink']['key'] . $key])) {
                    $full_url['full_url' . $key] = $data[$permalink['permalink']['key'] . $key];
                } else if ($category !== false && $data[$category['key']] > 0) {
                    $select = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $data[$category['key']], 'rec_table' => $category['options']['lookup']['table'], 'lang' => $key));

                    if (!empty($select['full_url']) && !empty($data[$permalink['permalink']['key'] . $key])) {
                        $full_url['full_url' . $key] = $select['full_url'] . '/' . $data[$permalink['permalink']['key'] . $key];
                    }
                } else if (($category !== false && $data[$category['category']] == 0) || $category === false) {
                    if (!empty($data[$permalink['permalink']['key'] . $key])) {
                        // According to the table prefix
                        if (!empty($this->Loader->Compiler->get($table, 'module')) && $table != 'system_pages' && $category['options']['lookup']['table'] != 'system_pages') {
                            $system_pagesModuleUrl = $this->Loader->Db->selectOne("SELECT `id` FROM `system_pages` WHERE `module` = :module", array('module' => $this->Loader->Compiler->get($table, 'module')));
                            $select = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id  AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $system_pagesModuleUrl['id'], 'rec_table' => 'system_pages', 'lang' => $key));
                            $urlPrefix[$key] = empty($select['full_url']) ? null : $select['full_url'] . '/';
                        }

                        $full_url['full_url' . $key] = $urlPrefix[$key] . $data[$permalink['permalink']['key'] . $key];
                    }
                }

                $result[$key] = $this->findDuplicateUrl($this->recordData[$permalink['permalink']['key'] . $key], $full_url['full_url' . $key], $key);
                $result[$key]['short_url'] = $this->createShortUrl($result[$key]['full_url'], $key);
            }

            return $result;
        }

        return false;
    }

    /**
     *
     * @access private
     * @param String $full_url Full url value of given data
     * @param String $lang Language value of given data
     * @return string unique short_url
     */
    private function createShortUrl($full_url, $lang)
    {
        $explode = explode('/', $full_url);
        $counter = count($explode);
        $short_url = null;

        do {
            if ($counter > 0) {
                $counter--;
                $short_url = rtrim($explode[$counter] . '/' . $short_url, '/');
                $record = $this->Loader->Db->selectOne("SELECT `id` FROM `system_meta` WHERE `lang` = :lang AND `short_url` = :short_url", array('lang' => $lang, 'short_url' => $short_url));
            } else {
                $record = '';
            }
        } while (!empty($record));

        return $short_url;
    }

    /**
     *
     * @access private
     * @param String $url Url value of given data
     * @param String $full_url Full url value of given data
     * @param String $lang Language value of given data
     * @param Integer $counter Counter variable for recursion
     * @return array unique url and full_url
     */
    private function findDuplicateUrl($url, $full_url, $lang, $counter = 1)
    {
        $record = $this->Loader->Db->selectOne("SELECT `rec_table`,`rec_id` FROM `system_meta` WHERE `lang` = :lang AND `full_url` = :full_url AND `rec_id` != :id", array('lang' => $lang, 'full_url' => $full_url, 'id' => $this->id));

        if (!empty($record) && isset($this->Loader->Compiler->compiledTables[$record['rec_table']]) && $this->Loader->Compiler->getFieldByType($record['rec_table'], 'permalink') !== false) {
            $table = $record['rec_table'];
            $record = $this->Loader->Db->selectOne("SELECT `id` FROM `$table` WHERE `id` = :id", array('id' => $record['rec_id']));

            if (!empty($record)) {
                $counter++;

                if ($counter == 2) {
                    $full_url = $full_url . '-' . $counter;
                } else {
                    $str = explode('-', $full_url);
                    $strCounter = count($str) - 1;
                    $str[$strCounter] = $str[$strCounter] + 1;
                    $full_url = implode('-', $str);
                }

                return $this->findDuplicateUrl($url, $full_url, $lang, $counter);
            } else {
                $url = $counter == 1 ? $url : $url . '-' . $counter;
                return array('url' => $url, 'full_url' => $full_url);
            }
        } else {
            $url = $counter == 1 ? $url : $url . '-' . $counter;
            return array('url' => $url, 'full_url' => $full_url);
        }
    }

    /**
     * Get values for inserting or updating to database.
     * Check if given table already has same row with given identity field.
     *
     * @access private
     * @return bool
     */
    private function identityFieldControl()
    {
        $i = 0;
        $where = null;
        $resultMessage = array();

        if ($this->Loader->Helper->checkArray($this->identityLabels)) {
            foreach ($this->identityLabels as $key => $value) {
                $field = $value['label'];

                if (!empty($this->data[$field])) {
                    if ($this->isNewRecord() == false && is_array($this->id)) {
                        foreach ($this->id as $id) {
                            $where .= 'id != ' . $id['id'] . ' AND ';
                        }
                    } else if ($this->isNewRecord() == false) {
                        $where = 'id != ' . $this->id . ' AND ';
                    }

                    $select = $this->Loader->Db->selectOne("SELECT `id` FROM `$this->table` WHERE $where `$field` = :field", array('field' => $this->data[$field]));

                    if (empty($select['id']) == false) {
                        $lang = !empty($value['lang']) ? '(' . $this->Loader->languages['list'][$value['lang']] . ')' : null;
                        $resultMessage[$i] = sprintf(_('Aynı "%s" %s değerine sahip başka bir kayıt bulunuyor. Lütfen başka bir değer girin.'), $value['title'], $lang);
                        $i++;
                    }
                }
            }

            if (empty($resultMessage)) {
                return true;
            } else {
                $error = null;

                foreach ($resultMessage as $key => $value) {
                    $error .= '<li>' . $value . '</li>';
                }

                $errorString = '<ul>' . $error . '</ul>';

                $this->result = array('result' => false, 'type' => 1, 'message' => $errorString);

                return false;
            }
        } else {
            return true;
        }
    }

    /**
     *
     * @access private
     * @return void
     */
    private function reOrderHierarchy()
    {
        if ($this->issetCategory() == true && $_POST[$this->categoryKey] > 0 && $this->table == $this->lookupTable) {
            $changeControl = $this->Loader->Db->selectOne("SELECT `$this->categoryKey` FROM `$this->table` WHERE `id` = :id", array('id' => $this->id));

            if ($this->isNewRecord() || ($changeControl[$this->categoryKey] != $_POST[$this->categoryKey])) {
                $allShort = $this->Loader->Db->select("SELECT `id`, `sequence` FROM `$this->table` ORDER BY `sequence` ASC");
                $lastShort = $this->Loader->Db->selectOne("SELECT `sequence` FROM `$this->table` WHERE `$this->categoryKey` = :cat ORDER BY `sequence` DESC", array('cat' => $_POST[$this->categoryKey]));

                if (empty($lastShort)) {
                    $lastShort = $this->Loader->Db->selectOne("SELECT `sequence` FROM `$this->table` WHERE `id` = :id", array('id' => $_POST[$this->categoryKey]));
                }

                foreach ($allShort as $value) {
                    if ($value['sequence'] > $lastShort['sequence']) {
                        $recordID = $value['id'];
                        $newOrder['sequence'] = $value['sequence'] + 1;
                        $this->Loader->Db->update($this->table, $newOrder, " id = '$recordID'");
                    }
                }

                $this->recordData['sequence'] = $lastShort['sequence'] + 1;
            }
        } else if ($this->isNewRecord()) {
            $sequence = $this->Loader->Db->selectOne("SELECT MAX(sequence) as `max` FROM `$this->table`");
            $this->recordData['sequence'] = $sequence['max'] + 1;
        }
    }

    /**
     * Update releated records.
     *
     * @access private
     * @param string $table
     * @param int $rec_id id value of the given record
     * @return void
     */
    private function updateRelatedRecords($table, $rec_id)
    {
        $permalink = $this->Loader->Compiler->getFieldByType($table, 'permalink');

        if ($permalink !== false) {
            $category = $this->Loader->Compiler->getFieldByType($table, 'category');

            foreach ($this->Loader->languages['list'] as $key => $value) {
                $this->updateRelatedRecordsInConnectedTable($table, $rec_id, $key);

                if ($category !== false && $table === $category['options']['lookup']['table']) {
                    $this->updateRelatedRecordsInTable($table, $rec_id, $key, $permalink['permalink'], $category['key']);
                }
            }
        }
    }

    /**
     * Update releated records in table recursively.
     *
     * @access private
     * @param string $table
     * @param int $rec_id id value of the given record
     * @param string $lang Language to update sub menus
     * @return void
     */
    private function updateRelatedRecordsInTable($table, $rec_id, $lang, $permalinkKey, $categoryKey)
    {
        $records = $this->Loader->Db->select("SELECT `id`, `$permalinkKey$lang` FROM `$table` WHERE `$categoryKey` = :id", array('id' => $rec_id));

        if (!empty($records)) {
            $full_url = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $rec_id, 'rec_table' => $table, 'lang' => $lang));

            foreach ($records as $record) {
                $id = $record['id'];

                if (!empty($record[$permalinkKey . $lang])) {
                    $metaData['full_url'] = $full_url['full_url'] . '/' . $record[$permalinkKey . $lang];
                    $this->Loader->Db->update('system_meta', $metaData, " rec_id = '$id' AND rec_table = '$table' AND lang = '$lang'");
                }

                $this->updateRelatedRecordsInConnectedTable($table, $id, $lang);
                $this->updateRelatedRecordsInTable($table, $id, $lang, $permalinkKey, $categoryKey);
            }
        }
    }

    /**
     * Update releated records in table recursively.
     *
     * @access private
     * @param string $table
     * @param int $rec_id id value of the given record
     * @param string $lang Language to update sub menus
     * @return void
     */
    private function updateRelatedRecordsInConnectedTable($table, $rec_id, $lang)
    {
        if (!empty($this->Loader->Compiler->connectedTables[$table])) {
            foreach ($this->Loader->Compiler->connectedTables[$table] as $connectedTable) {
                $tableName = $connectedTable['table'];
                $categoryField = $connectedTable['field'];
                $records = $this->Loader->Db->select("SELECT `id`, `$this->permalinkKey$lang` FROM `$tableName` WHERE `$categoryField` = :dependencyValue", array('dependencyValue' => $rec_id));

                if (!empty($records)) {
                    $full_url = $this->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE `rec_id` = :rec_id AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $rec_id, 'rec_table' => $table, 'lang' => $lang));

                    foreach ($records as $record) {
                        $id = $record['id'];

                        if (!empty($record[$this->permalinkKey . $lang])) {
                            $metaData['full_url'] = $full_url['full_url'] . '/' . $record[$this->permalinkKey . $lang];
                            $this->Loader->Db->update('system_meta', $metaData, " rec_id = '$id' AND rec_table = '$tableName' AND lang = '$lang'");
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @access private
     * @param array $field
     * @param array $images
     * @return void
     */
    private function imageProcess(array $field, array $images)
    {
        if (isset($field['options']['crop']) && !empty($field['options']['crop'])) {
            foreach ($field['options']['crop'] as $crop) {
                if (isset($crop['width']) && isset($crop['height'])) {
                    $cropPath = 'crop/' . $crop['width'] . 'x' . $crop['height'] . '/';
                } else {
                    continue;
                }

                foreach ($images as $key => $value) {
                    $fileName = $value['name'];

                    if (file_exists(ABSPATH . UPLOADURL . $fileName)) {
                        // If Folder is not exist, create it.
                        $path = dirname(ABSPATH . UPLOADURL . $cropPath . $fileName);
                        $path = str_replace("\\", "/", $path);
                        $path = explode("/", $path);

                        $rebuild = '';
                        foreach ($path AS $p) {
                            if (strstr($p, ":") != false) {
                                $rebuild = $p;
                                continue;
                            }

                            $rebuild .= "/$p";
                            if (!is_dir($rebuild)) mkdir($rebuild, 0755);
                        }

                        // File Extention Control
                        $fileExtention = new SplFileInfo($fileName);

                        if (in_array(strtolower($fileExtention->getExtension()), array('jpeg', 'jpg', 'png'))) {
                            // Creating Image
                            $quality = isset($crop['quality']) ? $crop['quality'] : 90;
                            $image = new SimpleImage(ABSPATH . UPLOADURL . $fileName);

                            if (!isset($crop['imageProtect']) || $crop['imageProtect'] == true) {
                                if ($image->getAspectRatio() > 1) {
                                    $crop['width'] > $crop['height'] ? $crop['height'] = null : $crop['width'] = null;
                                } else {
                                    $crop['width'] > $crop['height'] ? $crop['width'] = null : $crop['height'] = null;
                                }

                                $image
                                    ->autoOrient()
                                    ->toFile(ABSPATH . UPLOADURL . $fileName, null, $quality)
                                    ->resize($crop['width'], $crop['height'])
                                    ->toFile(ABSPATH . UPLOADURL . $cropPath . $fileName, null, $quality);
                            } else {
                                $image
                                    ->autoOrient()
                                    ->toFile(ABSPATH . UPLOADURL . $fileName, null, $quality)
                                    ->thumbnail($crop['width'], $crop['height'])
                                    ->toFile(ABSPATH . UPLOADURL . $cropPath . $fileName, null, $quality);
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($images as $key => $value) {
                $fileName = $value['name'];

                if (file_exists(ABSPATH . UPLOADURL . $fileName)) {
                    $fileExtention = new SplFileInfo($fileName);

                    if (in_array(strtolower($fileExtention->getExtension()), array('jpeg', 'jpg', 'png'))) {
                        $image = new SimpleImage(ABSPATH . UPLOADURL . $fileName);
                        $quality = isset($field['options']['quality']) ? $field['options']['quality'] : 90;

                        $image
                            ->autoOrient()
                            ->toFile(ABSPATH . UPLOADURL . $fileName, null, $quality);
                    }
                }
            }
        }
    }

    /**
     * When added a new or change record , collect meta information according to the language.
     * Then add a new meta record as collectively.
     *
     * @access private
     * @return void
     */
    private function saveMetaData()
    {
        foreach ($this->Loader->languages['list'] as $key => $value) {
            $metaData = array();
            $metaData['rec_status'] = $this->recordData['rec_status'];
            $metaData['rec_table'] = $this->table;
            $metaData['rec_id'] = $this->rec_id;
            $metaData['lang'] = $key;
            $metaData['title'] = strip_tags($this->recordData[$this->permalinkTitle . $key]);
            $metaData['url'] = $this->data[$this->permalinkKey . $key];
            $metaData['full_url'] = $this->full_url[$key];
            $metaData['short_url'] = $this->short_url[$key];
            $metaData['meta_image'] = substr($this->data['meta_image' . $key], strlen(JAMBI_UPLOADS));
            $metaData['meta_title'] = $this->data['meta_title' . $key];
            $metaData['meta_desc'] = $this->data['meta_desc' . $key];
            $metaData['sitemap'] = $this->data['sitemap' . $key];

            // Record Control
            $record = $this->Loader->Db->selectOne("SELECT `id` FROM `system_meta` WHERE `rec_id` = :rec_id AND `rec_table` = :rec_table AND `lang` = :lang", array('rec_id' => $this->rec_id, 'rec_table' => $this->table, 'lang' => $key));

            if (($this->isNewRecord() || empty($record)) && (!empty($metaData['title']) && !empty($metaData['url']))) {
                $this->Loader->Db->insert('system_meta', $metaData);
            } else {
                $this->Loader->Db->update('system_meta', $metaData, " rec_id = '$this->rec_id' AND rec_table='$this->table' AND lang='$key'");
            }
        }
    }
}