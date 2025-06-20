<?php

require_once dirname(__FILE__) . '/../../../../system/_init.php';

if (isset($_POST['selectedRecordList'])) {
    $limit = count($Jambi->Loader->languages['keys']);
    $table = $_POST['module'];
    $countRecords = count(explode(',', $_POST['selectedRecordList']));
    
    if ($countRecords > 1) {
        // Multiple Data
        if ($Jambi->Loader->Db->deleteAll($table, $_POST['selectedRecordList'])) {
            if ($Jambi->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
                $idList = explode(',', $_POST['selectedRecordList']);

                foreach ($idList as $id) {
                    $Jambi->Loader->Db->delete('system_meta', "rec_table = '$table' AND rec_id = '$id'", $limit);
                }

                $Jambi->Loader->Helper->createSitemapFiles();
            }

            echo json_encode(array('result' => true, 'message' => _('Kayıtlar silindi.')));
        } else {
            echo json_encode(array('result' => false, 'message' => _('Kayıtlar silinirken bir hata oluştu!')));
        }
    } else if ($countRecords == 1) {
        // Single Data
        $id = $_POST['selectedRecordList'];

        if ($Jambi->Loader->Db->delete($table, "id='$id'")) {
            if ($Jambi->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
                $Jambi->Loader->Db->delete('system_meta', "rec_table = '$table' AND rec_id = '$id'", $limit);
                $Jambi->Loader->Helper->createSitemapFiles();
            }

            echo json_encode(array('result' => true, 'message' => _('Kayıt silindi.')));
        } else {
            echo json_encode(array('result' => false, 'message' => _('Kayıt silinirken bir hata oluştu!')));
        }
    }
}