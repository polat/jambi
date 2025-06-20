<?php

// Custom Settings Saving
if ($_POST) {
    $result = true;

    foreach ($Jambi->Loader->languages['keys'] as $value) {
        // Popup Settings
        if (isset($_POST['popup' . $Jambi->Loader->languages['first']])) {
            $_POST['popup' . $value] = $Jambi->Loader->Helper->htmlPathReplace($_POST['popup' . $value]);
        }
    }

    foreach ($_POST as $key => $value) {
        $recordData = array();
        $multiLang = false;
        $currLang = null;
        $adminLang = '';

        // Check if value is admin language
        if ($key == 'default_admin_lang') {
            $adminLang = $Jambi->Loader->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = :option_key", array('option_key' => 'default_admin_lang'));
            $adminLang = $adminLang['option_value'];
        }

        // Check if $_POST value multi lang or not.
        foreach ($Jambi->Loader->languages['keys'] as $lang) {
            if (substr($key, -1 * count($lang) - 1) == $lang) {
                $multiLang = true;
                $newKey = substr($key, 0, strpos($key, $lang));
                $currLang = $lang;
                break;
            }
        }

        // Create array depends on multi lang or not.
        $recordData['option_value'] = $value;

        if ($multiLang) {
            $recordData['option_key'] = $newKey;
            $recordData['lang'] = $currLang;
        } else {
            $recordData['option_key'] = $key;
            $recordData['lang'] = null;
        }

        // Update (or if doesn't exist create) setting
        $id = $Jambi->Loader->Db->selectOne("SELECT `id` from `system_settings` WHERE `option_key` = :option_key AND (`lang` = :lang OR `lang` IS NULL)", array('option_key' => $recordData['option_key'], 'lang' => $currLang));
        $id = $id['id'];

        if (empty($id)) {
            if (!$Jambi->Loader->Db->insert('system_settings', $recordData)) {
                $result = false;
            }
        } else {
            if (!$Jambi->Loader->Db->update('system_settings', $recordData, 'id=' . $id)) {
                $result = false;
            }
        }
    }
}

$Jambi->settings = $Jambi->getAllSettings();

?>

<div class="module_title">
    <div class="pull-left">
        <h4><?= _('Ayarlar') ?> > <?= _('Popup') ?></h4>
    </div>

    <div class="pull-right">
        <button type="submit" class="btn btn-danger" id="form-submit"><?= _('Kaydet') ?></button>
    </div>
    <div style="clear: both;"></div>
</div>

<ul class="nav nav-tabs">
    <?php
    $i = 0;
    foreach ($Jambi->Loader->languages['list'] as $l => $value) {
        $cl = $i == 0 ? ' class="active"' : null;
        ?>
        <li <?= $cl ?>><a href="#<?= $l ?>" data-toggle="tab"><?= $value ?></a></li>
        <?php
        $i++;
    }
    ?>
</ul>

<form id="recordForm" action="" method="post">
    <div class="tab-content">
        <?php
        $i = 0;

        foreach ($Jambi->Loader->languages['list'] as $l => $value) {
            $cl = $i == 0 ? ' active' : null;

            $popupCacheLifeValue = $Jambi->settings['popup_cache_life'][$l] == '' || !ctype_digit($Jambi->settings['popup_cache_life'][$l]) ? 120 : $Jambi->settings['popup_cache_life'][$l];

            if ($Jambi->settings['popup_show_where'][$l] == 0) {
                $onlyHomePage = 'checked';
                $allPage = '';
            } else {
                $onlyHomePage = '';
                $allPage = 'checked';
            }
            ?>
            <div class="tab-pane <?= $cl ?>" id="<?= $l ?>">
                <div class="fieldset clearfix">
                    <label><?= _('Görünürlük Durumu') ?></label>
                    <label class="full">
                        <label class="radioLabel"><input type="radio" name="popup_show_where<?= $l ?>"
                                                         value="0" <?= $onlyHomePage ?>> <?= _('Sadece Anasayfada görünsün.') ?>
                        </label>
                        <label class="radioLabel"><input type="radio" name="popup_show_where<?= $l ?>"
                                                         value="1" <?= $allPage ?>> <?= _('Tüm sayfalarda görünsün.') ?>
                        </label>
                    </label>
                </div>

                <div class="fieldset clearfix">
                    <label><?= _('Yenilenme Süresi (Dakika)') ?></label>
                    <label class="full" style="width: 150px"><input type="text" name="popup_cache_life<?= $l ?>" value="<?= $popupCacheLifeValue ?>" class="form-control"/></label>
                </div>

                <div class="fieldset clearfix">
                    <label><?= _('Popup Dosyası') ?></label>
                    <label class="full fileSelect">
                        <input id="popup<?= $l ?>Input" name="popup<?= $l ?>" class="form-control"
                               readonly="readonly" value="<?= $Jambi->settings['popup'][$l] ?>">
                        <a href="<?= JAMBI_ADMIN_CONTENT ?>plugins/filemanager/dialog.php?type=2&field_id=popup<?= $l ?>Input"
                           class="btn btn-sm btn-primary filemanager-iframe" type="button"><i
                                    class="fa fa-search"></i> <?= _('Dosya Seç') ?></a>

                        <?php
                        if ($Jambi->settings['popup'][$l]) {
                            echo '<a href="javascript:void(0)" id="popup' . $l . '" class="btn btn-sm btn-danger removeFile"><i class="fa fa-times"></i></a>';
                        }
                        ?>
                    </label>
                </div>
            </div>
            <?php
            $i++;
        }
        ?>
    </div>
</form>