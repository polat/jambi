<?php

// Require necessary files.
$path = realpath(dirname(__FILE__) . '/../../../..');
require_once $path . '/system/_init.php';
require_once 'UploadHandler.php';

$options = array(
    'delete_type' => 'POST',
    'db_host' => DB_HOST,
    'db_user' => DB_USER,
    'db_pass' => DB_PASS,
    'db_name' => DB_NAME,
    'db_table' => 'system_files'
);

/**
 * Customized Upload Handler for Database Operations
 * Class CustomUploadHandler
 */
class CustomUploadHandler extends UploadHandler
{

    protected function initialize()
    {
        parent::initialize();
    }

    protected function handle_form_data($file, $index)
    {
        $file->created = date(JAMBI_ADMIN_DATE_FORMAT);
        $file->rec_id = @$_REQUEST['rec_id'];
        $file->rec_table = @$_REQUEST['rec_table'];
        $file->field_name = @$_REQUEST['field_name'];
        $file->title = @$_REQUEST['title'][$index];
        $file->description = @$_REQUEST['description'][$index];

    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
    {
        global $Jambi;
        $file = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);

        $sequence = $this->get_sort() + 1;

        if (empty($file->error)) {
            $Jambi->Loader->Db->insert($this->options['db_table'], array(
                'sequence' => $sequence,
                'rec_author' => $Jambi->Loader->Session->get('user_id'),
                'created' => $file->created,
                'rec_id' => $file->rec_id,
                'rec_table' => $file->rec_table,
                'field_name' => $file->field_name,
                'name' => $file->name,
                'size' => $file->size,
                'type' => $file->type
            ));

            $file->id = $Jambi->Loader->Db->lastInsertId();
        }
        return $file;
    }

    protected function set_additional_file_properties($file)
    {
        parent::set_additional_file_properties($file);
        global $Jambi;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $select = $Jambi->Loader->Db->selectOne('SELECT `id`, `type`, `title`, `description` FROM `' . $this->options['db_table'] . '` WHERE `name`= :fileName', array('fileName' => $file->name));

            $file->id = $select['id'];
            $file->type = $select['type'];
            $file->title = $select['title'];
            $file->description = $select['description'];

        }
    }

    public function get_sort()
    {
        global $Jambi;

        $sequence = $Jambi->Loader->Db->selectOne('SELECT MAX(`sequence`) FROM `' . $this->options['db_table'] . '`');
        $sequence = $sequence['MAX(`sequence`)'];

        return $sequence;
    }

    public function delete($print_response = true)
    {
        global $Jambi;

        $response = parent::delete(false);

        foreach ($response as $name => $deleted) {
            if ($deleted) {
                $Jambi->Loader->Db->delete($this->options['db_table'], "`name` = '$name'");
            }
        }

        return $this->generate_response($response, $print_response);
    }
}

$upload_handler = new CustomUploadHandler($options);