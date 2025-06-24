<?php

require_once '../../../system/_init.php';

/**
 * Delete Image From Gallery
 */
if ($Jambi->Loader->Helper->get('delete')) {
    if ($Jambi->Loader->Db->delete('system_files', "rec_table = '$_GET[rec_table]' AND id = '$_GET[id]' AND field_name = '$_GET[field_name]'")) {
        echo json_encode(array('result' => true));
    } else {
        echo json_encode(array('result' => false));
    }
}

/**
 * Sort Gallery Images
 */
if ($Jambi->Loader->Helper->get('sort')) {
    foreach ($_GET['listItem'] as $position => $item) {
        $Jambi->Loader->Db->update('system_files', array('sequence' => $position), '`id` = ' . $item);
    }
}