<?php

require_once dirname(__FILE__) . '/../../../../system/_init.php';

/**
 * SHORTING ALL RECORDS
 */
if ($_POST['sortData']) {
    $idList = array();
    $sequenceList = array();

    foreach ($_POST['sortData'] as $data) {
        $data = htmlspecialchars(filter_var(trim($data, '/'), FILTER_SANITIZE_STRING));
        $dataExplode = explode(';', $data);
        $idList[] = $dataExplode[0];
        $sequenceList[] = $dataExplode[1];
    }

    $i = min($sequenceList);

    if ($_POST['sort'] == 'DESC') {
        $idList = array_reverse($idList);
    }

    foreach ($idList as $id) {
        $Jambi->Loader->Db->update($_POST['module'], array('sequence' => $i), "id='$id'");
        $i++;
    }

    echo json_encode(array('result' => true, 'message' => _('Sıralama yeniden oluşturuldu.')));
}

/**
 * RECORD TRANSFER TO BETWEEN PAGES
 */
if ($_POST['recordTransferType']) {
    $table = $_POST['module'];
    $page = $_POST['page'];
    $pagination = $_POST['pagination'];
    $sort = 'sequence ' . $_POST['sort'];
    $rec_status = $_POST['rec_status'];
    $selectedRecords = $_POST['selectedRecordList'];
    $selectedRecordsExplode = explode(',', $_POST['selectedRecordList']);
    $selectedRecordsCount = count($selectedRecordsExplode);
    $recordsNumber = ($pagination * $page) - $selectedRecordsCount;

    if ($_POST['recordTransferType'] == 'back') {
        $destinationPageRecordNumber = (($page - 2) * $pagination);
    } else {
        $destinationPageRecordNumber = ($page * $pagination);
    }

    $records = $Jambi->Loader->Db->select("SELECT `id`,`sequence` FROM `$table` WHERE `id` NOT IN (:selectedRecords) AND `rec_status` = :rec_status ORDER BY $sort LIMIT $destinationPageRecordNumber,$recordsNumber", array('rec_status' => $rec_status, 'selectedRecords' => $selectedRecords));
    $minimumSequence = $records[0]['sequence'];
    $startSequence = $minimumSequence + $selectedRecordsCount;

    if ($_POST['sort'] == 'DESC') {
        if ($_POST['recordTransferType'] == 'back') {
            for ($i = 0; $i < $selectedRecordsCount; $i++) {
                $Jambi->Loader->Db->update($table, array('sequence' => $minimumSequence), "id='$selectedRecordsExplode[$i]'");
                $minimumSequence--;
            }
        } else {
            for ($i = 0; $i < $selectedRecordsCount; $i++) {
                $Jambi->Loader->Db->update($table, array('sequence' => $startSequence), "id='$selectedRecordsExplode[$i]'");
                $startSequence--;
            }
        }

        foreach ($records as $record) {
            $id = $record['id'];
            $Jambi->Loader->Db->update($table, array('sequence' => $minimumSequence), "id='$id'");
            $minimumSequence--;
        }

        // Update Record Old Page
        $oldRecordsNumber = ($pagination * $page);
        $oldDestinationPageRecordNumber = (($page - 1) * $pagination);
        $oldRecords = $Jambi->Loader->Db->select("SELECT `id`,`sequence` FROM `$table` WHERE `id` NOT IN (:selectedRecords) AND `rec_status` = :rec_status ORDER BY $sort LIMIT $oldDestinationPageRecordNumber,$oldRecordsNumber", array('rec_status' => $rec_status, 'selectedRecords' => $selectedRecords));
        $maxOldSequence = $oldRecords[0]['sequence'];

        foreach ($oldRecords as $oldRecord) {
            $id = $oldRecord['id'];
            $Jambi->Loader->Db->update($table, array('sequence' => $maxOldSequence), "id='$id'");
            $maxOldSequence--;
        }
    } else {
        for ($i = 0; $i < $selectedRecordsCount; $i++) {
            $Jambi->Loader->Db->update($table, array('sequence' => $minimumSequence), "id='$selectedRecordsExplode[$i]'");
            $minimumSequence++;
        }

        foreach ($records as $record) {
            $id = $record['id'];
            $Jambi->Loader->Db->update($table, array('sequence' => $startSequence), "id='$id'");
            $startSequence++;
        }
    }

    echo json_encode(array('result' => true, 'message' => _('Sıralama yeniden oluşturuldu.')));
}