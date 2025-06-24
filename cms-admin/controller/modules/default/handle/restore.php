<?php

require_once dirname(__FILE__) . '/../../../../system/_init.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $table = $_POST['module'];
    $permalink = $Jambi->Loader->Compiler->getFieldByType($table, 'permalink');

    $Jambi->Loader->Db->update($table, array('rec_status' => 0), "id = '$id'");

    if ($permalink !== false) {
        $data = $Jambi->Loader->Db->selectOne("SELECT * FROM `$table` WHERE `id` = :id", array('id' => $id));
        $data['edit'] = true;
        $data['rec_status'] = 0;
        $save = new SaveList($Loader, $table, $data);
    }

    echo json_encode(array('result' => true, 'message' => _('Kayıt başarıyla geri yüklendi.')));
}