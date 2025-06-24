<?php

$buttonResult = null;

if ($Jambi->get['rec_status'] == 0 || $Jambi->get['rec_status'] == 1) {
    if (($Jambi->get['action'] == false || $Jambi->get['action'] != 'edit')) {
        /* New Record Button */
        $buttonResult .= ' <a href="' . $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'edit', 'page'))) . '" class="btn btn-primary">' . _('Yeni Kayıt Ekle') . '</a>';
    } else {
        if ($Jambi->get['rec_status'] == 0 && $Jambi->get['id']) {
            /* Update Button */
            $buttonResult .= ' <button type="submit" id="form-submit" class="btn btn-success" onclick="this.disabled=true;">' . _('Güncelle') . '</button>';
        } else {
            /* Save Button */
            $buttonResult .= ' <button type="submit" id="form-submit" class="btn btn-success" onclick="this.disabled=true;">' . _('Yayınla') . '</button>';
        }

        /* Draft Button */
        $buttonResult .= ' <button type="button" id="form-submit-draft" class="btn btn-primary" onclick="this.disabled=true;">' . _('Taslak Olarak Kaydet') . '</button>';

        /* Page Preview Button */
        if ($Jambi->get['rec_status'] == 0 && $Jambi->Loader->Compiler->getFieldByType($Jambi->get['module'], 'permalink') !== false && $Jambi->get['id']) {
            $select = $Jambi->Loader->Db->selectOne("SELECT `full_url` FROM `system_meta` WHERE rec_id = :id AND rec_table = :rec_table AND lang = :lang", array('id' => $Jambi->get['id'], 'rec_table' => $Jambi->get['module'], 'lang' => $Jambi->Loader->languages['default_lang']));
            $buttonResult .= ' <a href="' . BASEURL . $select['full_url'] . '" class="btn btn-primary" title="' . _('Sayfaya Git') . '" target="_blank">' . _('Sayfaya Git') . '</a>';
        }

        /* Cancel Button */
        $buttonResult .= ' <button type="button" id="form-quit" class="btn btn-danger" onclick="window.history.back()">' . _('Vazgeç') . '</button>';
    }
}

if ($Jambi->get['action'] == 'option') {
    /* Update Button */
    $buttonResult = ' <button type="submit" id="form-submit" class="btn btn-success" onclick="this.disabled=true;">' . _('Güncelle') . '</button>';
}

$moduleTitle = $Jambi->get['rec_status'] == 2 ? $Jambi->table['title'] . ' (' . _('Çöp Kutusu') . ')' : $Jambi->table['title'];

?>

<div class="module_title">
    <div class="pull-left">
        <h4><?= $moduleTitle ?></h4>
        <p><?= $Jambi->table['description'] ?></p>
    </div>

    <div class="pull-right">
        <?= $buttonResult ?>
    </div>

    <div class="clearfix"></div>
</div>
