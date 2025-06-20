<?php

if (isset($_POST['exportFields'])) {
    $Jambi->exportTableData($Jambi->data, $_POST);
}

?>

    <div class="pull-left tableButtons">
        <a href="javascript:void(0)" class="pull-left boxicon fa fa-square-o" id="selectAll"></a>
        <a href="javascript:void(0)" class="pull-left boxicon fa fa-trash-o" id="multipleDeleteRecord"></a>
        <a href="javascript:void(0)"
           class="pull-left boxicon fa fa-backward <?= !$Jambi->data['sorting']['before'] ? 'passive' : '' ?>"
           id="transferRecordsBackward" data-transfer-type="back"
           title="<?= _("Seçilenleri Önceki Sayfaya Taşı") ?>"></a>
        <a href="javascript:void(0)"
           class="pull-left boxicon fa fa-forward <?= !$Jambi->data['sorting']['after'] ? 'passive' : '' ?>"
           id="transferRecordsForward" data-transfer-type="forward"
           title="<?= _("Seçilenleri Sonraki Sayfaya Taşı") ?>"></a>

        <form action="" method="get" class="admin-search">
            <?php
            foreach ($_GET as $key => $value) {
                if ($key != 'query' && $key != 'page') {
                    echo '<input type="hidden" name ="' . $key . '" value="' . $value . '">';
                }
            }
            ?>
            <input type="search" name="query" placeholder="<?= _('Ne aramak istediniz?') ?>"
                   value="<?= $Jambi->get['query'] ?>">
            <input type="submit" value="<?= _('Ara') ?>">
        </form>
    </div>

    <div id="rec_status" class="pull-right">
        <?php
            $recStatusActive[$Jambi->get['rec_status']] = 'active';
        ?>

        <span>
            <select name="" id="paginationList">
                <?php
                if ($Jambi->get['pagination'] == false || $Jambi->get['pagination'] == $Jambi->data['paginationList']['default']) {
                    $selectedValue = 'default';
                } else {
                    $selectedValue = $Jambi->get['pagination'];
                }

                $selectedOption[$selectedValue] = 'selected';

                echo '<option value="' . $Jambi->Loader->Helper->handleGetUrlParameters(array('add' => array('pagination' => $Jambi->data['paginationList']['default']))) . '" ' . $selectedOption['default'] . '>'. $Jambi->data['paginationList']['default'] .' (default)</option>';
                unset($Jambi->data['paginationList']['default']);

                if (!empty($Jambi->data['paginationList'][$Jambi->get['rec_status']])) {
                    foreach ($Jambi->data['paginationList'][$Jambi->get['rec_status']] as $key => $value) {
                        echo '<option value="' . $Jambi->Loader->Helper->handleGetUrlParameters(array('add' => array('pagination' => $value))) . '" ' . $selectedOption[$value] . '>' . $value . '</option>';
                    }
                }

                ?>
            </select>

            <span><?= _('Kayıt Göster') ?></span>
        </span>

        <a href="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('rec_status' => 0))) ?>" id="published" class="<?= $recStatusActive[0] ?>"><i class="fa fa-check-square-o"></i> <?= _('Yayınla') ?>
            <span><?= $Jambi->data['count_rec_status'][0] ?></span></a>
        <a href="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('rec_status' => 1))) ?>"
           id="draft" class="<?= $recStatusActive[1] ?>"><i class="fa fa-edit"></i> <?= _('Taslak') ?> <span><?= $Jambi->data['count_rec_status'][1] ?></span></a>
        <a href="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('rec_status' => 2))) ?>"
           id="trashed" class="<?= $recStatusActive[2] ?>"><i class="fa fa-trash-o"></i> <?= _('Çöp Kutusu') ?> <span><?= $Jambi->data['count_rec_status'][2] ?></span></a>

        <details>
            <summary class="btn btn-primary"><?= _('Dışa Aktar') ?></summary>
            <div class="my-modal">
                <form action="" method="POST">
                    <input type="hidden" name="exportFields">

                    <ul>
                        <?php
                            foreach ($Jambi->table['fields'] as $key => $field) {
                                if($field['type'] != 'gallery') {
                                    echo '<li>
                                        <input type="checkbox" id="c' . $key . '" name="fields[]" value="'. $field['label'] .'">
                                        <label for="c' . $key . '">'. $field['title'] . " " . $field['lang'] . '</label>
                                    </li>';
                                }
                            }
                        ?>
                    </ul>

                    <div class="my-modal-bottom">
                        <select name="type" class="form-control">
                            <option value="xlxs">Exel (Xlxs)</option>
                            <option value="csv">CSV</option>
                        </select>

                        <input type="submit" value="Gönder" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </details>
    </div>

    <div class="clearfix"></div>

<?php

if (empty($Jambi->data['records'])) {
    $Jambi->Loader->Helper->alert(_('Kayıt bulunamadı.'), 'warning');
} else {
    if ($Jambi->get['module'] == 'system_labels') {
        /* Moving files to content to display system_labels table */
        foreach ($Jambi->data['records'] as $key => $value) {
            if ($value['type'] == 'file') {
                foreach ($Jambi->Loader->languages['keys'] as $lang) {
                    $Jambi->data['records'][$key]['content' . $lang] = '<a href="' . BASEURL . UPLOADURL . $Jambi->data['records'][$key]['file' . $lang] . '" target="_blank" class="fileLink">' . $Jambi->data['records'][$key]['file' . $lang] . '</a>';
                }
            }
        }
    }
    ?>

    <form id="recordListForm" method="post"
          action="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'sort'))) ?>">
        <input name="module" type="hidden" value="<?= $Jambi->get['module'] ?>">
        <input name="sort" type="hidden" value="<?= $Jambi->table['sort']['direction'] ?>">

        <table id="dataTable" class="table table-hover" data-module="<?= $Jambi->get['module'] ?>"
               data-delete-type="<?= $Jambi->get['rec_status'] == 0 || $Jambi->get['rec_status'] == 1 ? 'trash' : 'delete' ?>">
            <thead>
            <tr>
                <?php

                echo '<td width="1">#id</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';

                if ($Jambi->get['rec_status'] == 0 && $Jambi->Loader->Compiler->getFieldByType($Jambi->get['module'], 'permalink') !== false) {
                    echo '<td width="1">&nbsp;</td>';
                }

                foreach ($Jambi->table['fields'] as $field) {
                    if ($field['list'] == true && $field['options']['rank'][$Jambi->Loader->Session->get('user_rank')] !== 'hidden' && (!isset($field['lang']) || $field['lang'] === $Jambi->Loader->languages['default_lang'])) {
                        echo '<td>' . $field['title'] . '</td>';
                    }
                }

                ?>

                <td><?= _('Eklendiği Tarih') ?></td>
                <td><?= _('Son Güncelleme') ?></td>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <?php

                echo '<td width="1">#id</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';
                echo '<td width="1">&nbsp;</td>';

                if ($Jambi->get['rec_status'] == 0 && $Jambi->Loader->Compiler->getFieldByType($Jambi->get['module'], 'permalink') !== false) {
                    echo '<td width="1">&nbsp;</td>';
                }

                foreach ($Jambi->table['fields'] as $field) {
                    if ($field['list'] == true && $field['options']['rank'][$Jambi->Loader->Session->get('user_rank')] !== 'hidden' && (!isset($field['lang']) || $field['lang'] === $Jambi->Loader->languages['default_lang'])) {
                        echo '<td>' . $field['title'] . '</td>';
                    }
                }

                ?>

                <td><?= _('Eklendiği Tarih') ?></td>
                <td><?= _('Son Güncelleme') ?></td>
            </tr>
            </tfoot>
            <tbody class="sortableTable">
            <?php

            foreach ($Jambi->data['records'] as $key => $record) {
                if ($Jambi->get['rec_status'] == 0 || $Jambi->get['rec_status'] == 1) {
                    echo '<tr data-id="' . $record['id'] . '">';
                    echo '<td align="center" width="1">'. $record['id'] .'</td>';
                    echo '<td align="center" width="1"><a href="javascript:void(0)" class="fa fa-square-o selectBox" rel="' . $record['id'] . '"></a><input type="hidden" name="sortData[]"  value="' . $record['id'] . ';' . $record['sequence'] . '"></td>';
                    echo '<td align="center" width="1"><a href="javascript:void(0)" title="' . _('Çöpe Taşı') . '" class="fa fa-trash-o singleDeleteRecord"></a></td>';
                    echo '<td align="center" width="1"><a href="javascript:void(0)" title="' . _('Kopyala') . '" class="fa fa-copy copyRecord"></a></td>';
                    echo '<td align="center" width="1"><a href="' . $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'edit'), 'add' => array('id' => $record['id']))) . '" class="fa fa-edit" title="' . _('Düzenle') . '"></a></td>';

                    if ($Jambi->get['rec_status'] == 0 && $Jambi->Loader->Compiler->getFieldByType($Jambi->get['module'], 'permalink') !== false) {
                        echo '<td align="center" width="1"><a href="' . BASEURL . $record['full_url'] . '" class="fa fa-magnet" title="' . _('Sayfayı Göster') . '" target="_blank"></a></td>';
                    }
                } else if ($Jambi->get['rec_status'] == 2) {
                    echo '<tr data-id="' . $record['id'] . '">';
                    echo '<td align="center" width="1">'. $record['id'] .'</td>';
                    echo '<td align="center" width="1"><a href="javascript:void(0)" class="fa fa-square-o selectBox" rel="' . $record['id'] . '"></a><input type="hidden" name="sortData[]"  value="' . $record['id'] . ';' . $record['sequence'] . '"></td>';
                    echo '<td align="center" width="1"><a href="javascript:void(0)" title="' . _('Kalıcı Olarak Sil') . '" class="fa fa-trash-o singleDeleteRecord"></a></td>';
                    echo '<td align="center" width="1"><a href="' . $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'edit'), 'add' => array('id' => $record['id']))) . '" class="fa fa-edit" title="' . _('Görüntüle') . '"></a></td>';
                    echo '<td align="center" width="1"><a href="" class="fa fa-refresh restoreRecord" title="' . _('Geri Yükle') . '"></a></td>';
                }

                foreach ($Jambi->table['fields'] as $field) {
                    if ($field['list'] == true && $field['options']['rank'][$Jambi->Loader->Session->get('user_rank')] !== 'hidden' && (!isset($field['lang']) || $field['lang'] === $Jambi->Loader->languages['default_lang'])) {
                        $item = stripslashes($record[$field['label']]);

                        if ($field['type'] == 'textarea' && strlen($item) > 150) {
                            $item = mb_substr(strip_tags($item), 0, 140) . '..';
                        } else if ($field['type'] == 'date') {
                            $item = $Jambi->Loader->Helper->dateFormat($item);
                        } else if ($field['type'] == 'dateRange') {
                            $item = unserialize($item);
                            $item = $Jambi->Loader->Helper->dateFormat($item['start']) . ' - ' . $Jambi->Loader->Helper->dateFormat($item['end']);
                        } else if ($field['type'] == 'file') {
                            $item = '<a href="' . BASEURL . UPLOADURL . $item . '" target="_blank">' . $item . '</a>';
                        } else if ($field['type'] == 'select' || $field['type'] == 'category' || ($field['type'] == 'radio' && !isset($field['options']['data']))) {
                            if (isset($field['options']['lookup'])) {
                                $lookupTable = $field['options']['lookup']['table'];
                                $lookupField = $field['options']['lookup']['field'];
                                $selectLookup = $Jambi->Loader->Db->selectOne("SELECT `$lookupField` FROM `$lookupTable` WHERE `id` = :id", array('id' => $item));
                                $item = $selectLookup[$lookupField];
                            }
                        } else if ($field['type'] == 'radio') {
                            $item = isset($field['options']['data'][$item]) ? $field['options']['data'][$item] : $item;
                        }

                        echo '<td>' . $item . '</td>';
                    }
                }

                echo '<td width="140">' . $record['created'] . '</td>';
                echo '<td width="140">' . $record['updated'] . '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </form>

    <form id="selectedRecordListForm" method="post" action="">
        <input type="hidden" name="module" value="<?= $Jambi->get['module'] ?>">
        <input type="hidden" name="sort" value="<?= $Jambi->table['sort']['direction'] ?>">
        <input type="hidden" name="page" value="<?= empty($Jambi->get['page']) ? 1 : $Jambi->get['page'] ?>">
        <input type="hidden" name="pagination" value="<?= $Jambi->table['pagination'] ?>">
        <input type="hidden" name="rec_status" value="<?= $Jambi->get['rec_status'] ?>">
        <input type="hidden" name="selectedRecordList" id="selectedRecordList"/>
        <input type="hidden" name="recordTransferType">
    </form>

    <?php

    echo $Jambi->data['pagination'];
}