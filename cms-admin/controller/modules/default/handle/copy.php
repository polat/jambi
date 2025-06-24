<?php

require_once dirname(__FILE__) . '/../../../../system/_init.php';

if (isset($_POST['selectedRecordList'])) {
    $table = $_POST['module'];
    $countRecords = count(explode(',', $_POST['selectedRecordList']));

    if ($countRecords == 1) {
        $keyList = null;
        $fieldList = $Jambi->Loader->Compiler->get($table, 'fields');

        foreach ($fieldList as $key => $item) {
            $keyList .= $item['options']['identity'] !== true ? $item['label'] . ',' : '';
        }

        $keyList = rtrim($keyList, ',');
        $data = $Jambi->Loader->Db->selectOne("SELECT $keyList FROM `$table` WHERE id = :id", array('id' => $_POST['selectedRecordList']));

        foreach ($fieldList as $key => $item) {
            if ($item['type'] == 'checkbox' || $item['type'] == 'dateRange' || $item['type'] == 'dateMultiple' || $item['type'] == 'multipleSelect' || $item['options']['dynamic'] === true) {
               $data[$item['label']] = unserialize($data[$item['label']]);
            }
        }

        if (!empty($data)) {
            $save = new SaveList($Loader, $table, $data);

            echo json_encode(array('result' => true, 'message' => 'Kayıt başarıyla kopyalandı.'));
        } else {
            echo json_encode(array('result' => false, 'message' => 'Kayıt kopyalanırken hata oluştu!'));
        }
    }
}