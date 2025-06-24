<?php

require_once dirname(__FILE__) . '/../../../../system/_init.php';

$limit = count($Jambi->Loader->languages['keys']);

if (isset($_POST['selectedRecordList'])) {
    $table = $_POST['module'];
    $countRecords = count(explode(',', $_POST['selectedRecordList']));
    $permalink = $Jambi->Loader->Compiler->getFieldByType($table, 'permalink');

    /**
     * MULTIPLE DATA
     */
    if ($countRecords > 1) {
        $deleteList = explode(',', $_POST['selectedRecordList']);
        $updateData['rec_status'] = 2;

        if ($permalink !== false) {
            foreach ($Jambi->Loader->languages['list'] as $key => $value) {
                $updateData[$permalink['permalink']['key'] . $key] = '';
            }

            foreach ($deleteList as $id) {
                $Jambi->Loader->Db->delete('system_meta', "rec_table = '$table' AND rec_id = '$id'", $limit);
            }

            $Jambi->Loader->Helper->createSitemapFiles();
        }

        foreach ($deleteList as $id) {
            $Jambi->Loader->Db->update($table, $updateData, "id = '$id'");
        }

        echo json_encode(array('result' => true, 'message' => _('Kayıtlar çöp kutusuna taşındı.')));
    }

    /**
     * SINGLE DATA
     */
    if ($countRecords == 1) {
        $id = $_POST['selectedRecordList'];
        $updateData['rec_status'] = 2;

        if ($permalink !== false) {
            foreach ($Jambi->Loader->languages['list'] as $key => $value) {
                $updateData[$permalink['permalink']['key'] . $key] = '';
            }

            $Jambi->Loader->Db->delete('system_meta', "rec_table = '$table' AND rec_id = '$id'", $limit);
            $Jambi->Loader->Helper->createSitemapFiles();
        }

        $Jambi->Loader->Db->update($table, $updateData, "id = '$id'");

        echo json_encode(array('result' => true, 'message' => _('Kayıt çöp kutusuna taşındı.')));
    }
}