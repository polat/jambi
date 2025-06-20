<?php

/**
 *
 * Class SaveOption
 *
 */
class SaveOption
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
     * Options
     * @var array $options
     */
    private $options;

    /**
     * Data of the record
     * @var array $post
     */
    private $data;

    /**
     * Data to be recorded
     * @var array $recordData
     */
    private $recordData;

    /**
     * Result message of functions
     * @var string $result ;
     */
    public $result = array();

    /**
     * SaveList constructor.
     * Get current field table, fields, post and get.
     * The received data are sending to 'prepareRecordData'
     * for prepare to records.
     *
     * @var Loader object $Loader
     * @var string $table Name of the record table
     * @var array $data data of the record
     */
    public function __construct(Loader $Loader, $table, array $data)
    {
        $this->Loader = $Loader;
        $this->table = $table;
        $this->options = $this->Loader->Compiler->get($this->table, 'options');
        $this->data = $data;

        $this->prepareRecordData();
    }

    /**
     * It prepare and organize data to will be added.
     *
     * Unnecessary characters are deleting when create a new post object.
     * if any special procedures by type are conducted.
     * Edited data are connected to 'id'.
     * if data is adding to system_labels table, type and language organize.
     * Result are send to 'handleRecord' for edit.
     *
     * @access private
     * @return void
     */
    private function prepareRecordData()
    {
        $i = 0;
        foreach ($this->options as $field) {
            $post = new Post($this->Loader, $field, $this->data);

            switch ($field['type']) {
                case 'file':
                    if ($field['options']['dynamic']) {
                        $this->recordData[$field['label']] = $post->result;

                        if (!empty($post->result)) {
                            $imageProcessList = array();

                            foreach (unserialize($post->result) as $key => $item) {
                                $imageProcessList[$key]['name'] = $item;
                            }

                            $this->imageProcess($field, $imageProcessList);
                        }
                    } else if (!empty($post->result)) {
                        $this->imageProcess($field, array(0 => array('name' => $post->result)));
                    }

                    $this->recordData[$field['label']] = $post->result;
                    break;
                case 'editor':
                    $this->recordData[$field['label']] = $post->result;
                    break;
                default:
                    if ($post->result !== false) {
                        $this->recordData[$field['label']] = $post->result;
                    }
            }
        }

        $this->updateRecord();
    }

    /**
     *
     * @access private
     * @return bool
     */
    private function updateRecord()
    {
        foreach ($this->options as $option) {
            $optionKey = $option['key'];

            if (is_null($option['lang'])) {
                $this->Loader->Db->update('system_options', array('option_value' => $this->recordData[$optionKey]), " rec_table='$this->table' AND option_key='$optionKey' AND lang IS NULL");
            } else {
                foreach ($this->Loader->languages['list'] as $key => $lang) {
                    $this->Loader->Db->update('system_options', array('option_value' => $this->recordData[$optionKey . $key]), " rec_table='$this->table' AND option_key='$optionKey' AND lang='$key'");
                }
            }
        }
    }

    /**
     *
     * @access private
     * @param array $field
     * @param array $images
     */
    private function imageProcess($field, $images = array())
    {
        if (isset($field['options']['crop']) && !empty($field['options']['crop'])) {
            foreach ($field['options']['crop'] as $crop) {
                if (isset($crop['width']) && isset($crop['height'])) {
                    $cropPath = 'crop/' . $crop['width'] . 'x' . $crop['height'] . '/';
                } else {
                    continue;
                }

                foreach ($images as $key => $value) {
                    $fileName = urldecode($value['name']);

                    // If Folder is not exist, create it.
                    $path = dirname(ABSPATH . UPLOADURL . $cropPath . $fileName);
                    $path = str_replace("\\", "/", $path);
                    $path = explode("/", $path);

                    $rebuild = '';
                    foreach($path AS $p) {
                        if(strstr($p, ":") != false) {
                            $rebuild = $p;
                            continue;
                        }

                        $rebuild .= "/$p";
                        if(!is_dir($rebuild)) mkdir($rebuild, 0755);
                    }

                    // File Extention Control
                    $fileExtention = new SplFileInfo($fileName);

                    if (in_array(strtolower($fileExtention->getExtension()), array('jpeg', 'jpg', 'png'))) {
                        // Creating Image
                        $quality = isset($crop['quality']) ? $crop['quality'] : 80;
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
        } else {
            foreach ($images as $key => $value) {
                $fileName = urldecode($value['name']);
                $fileExtention = new SplFileInfo($fileName);

                if (in_array(strtolower($fileExtention->getExtension()), array('jpeg', 'jpg', 'png'))) {
                    $image = new SimpleImage(ABSPATH . UPLOADURL . $fileName);
                    $quality = isset($field['options']['quality']) ? $field['options']['quality'] : 80;

                    $image
                        ->autoOrient()
                        ->toFile(ABSPATH . UPLOADURL . $fileName, null, $quality);
                }
            }
        }
    }
}