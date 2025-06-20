<?php

// Custom Settings Saving
if ($_POST) {
    $result = true;

    foreach ($Jambi->Loader->languages['keys'] as $value) {
        // Meta Image
        if (isset($_POST['meta_image' . $Jambi->Loader->languages['first']])) {
            if (strstr($_POST['meta_image' . $value], JAMBI_UPLOADS)) {
                $_POST['meta_image' . $value] = substr($_POST['meta_image' . $value], strlen(JAMBI_UPLOADS));
            }
        }

        // LogoSVG Settings
        if (isset($_POST['logoSVG' . $Jambi->Loader->languages['first']])) {
            if (strstr($_POST['logoSVG' . $value], JAMBI_UPLOADS)) {
                $_POST['logoSVG' . $value] = substr($_POST['logoSVG' . $value], strlen(JAMBI_UPLOADS));
            }
        }

        // LogoPNG Settings
        if (isset($_POST['logoPNG' . $Jambi->Loader->languages['first']])) {
            if (strstr($_POST['logoPNG' . $value], JAMBI_UPLOADS)) {
                $_POST['logoPNG' . $value] = substr($_POST['logoPNG' . $value], strlen(JAMBI_UPLOADS));
            }
        }

        // Favicon Settings
        if (isset($_POST['favicon' . $Jambi->Loader->languages['first']])) {
            if (strstr($_POST['favicon' . $value], JAMBI_UPLOADS)) {
                $_POST['favicon' . $value] = substr($_POST['favicon' . $value], strlen(JAMBI_UPLOADS));
            }
        }

        // Popup Settings
        if (isset($_POST['popup' . $Jambi->Loader->languages['first']])) {
            $_POST['popup' . $value] = $Jambi->Loader->Helper->htmlPathReplace($_POST['popup' . $value]);
        }
    }

    foreach ($_POST as $key => $value) {
        $recordData = array();
        $multiLang = false;
        $currLang = null;

        // Check if value is admin language
        if ($key == 'default_admin_lang') {
            $adminLang = $Jambi->Loader->Db->selectOne("SELECT `option_value` FROM `system_settings` WHERE `option_key` = :option_key", array('option_key' => 'default_admin_lang'));
            $adminLang = $adminLang['option_value'];
        }

        // Check if $_POST value multi lang or not.
        foreach ($Jambi->Loader->languages['keys'] as $lang) {
            if (substr($key, -1 * strlen($lang)) == $lang) {
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

        // Check if value is admin language
        if ($key == 'default_admin_lang' && $adminLang != $value) {
            $Jambi->Loader->setLocaleLanguage();
        }
    }

    if ($result) {
        $Jambi->Loader->Helper->alert(_('Değişiklik yapıldı.'), 'success');
    } else {
        $Jambi->Loader->Helper->alert(_('Değişiklik yapılamadı!'), 'danger');
    }
}

$Jambi->settings = $Jambi->getAllSettings();

?>

<div class="module_title">
    <div class="pull-left">
        <h4><?= _('Ayarlar') ?> > <?= _('Genel') ?></h4>
    </div>

    <div class="pull-right">
        <button type="submit" class="btn btn-danger" id="form-submit"><?= _('Kaydet') ?></button>
    </div>

    <div style="clear: both;"></div>
</div>

<form id="recordForm" action='' method="post">
    <ol class="breadcrumb">
        <li><?= _('Seçenekler') ?></li>
    </ol>

    <!-- DEFAULT ADMIN LANGUAGE -->
    <?php
        $adminLang = $Jambi->settings['default_admin_lang'][$Jambi->Loader->languages['first']];
    ?>
    <div class="fieldset clearfix">
        <label><?= _('Yönetim Panelinin Dili') ?></label>

        <label class="full">
            <select name="default_admin_lang" class="form-control">
                <option value="TR" <?= $adminLang == 'TR' ? 'selected' : '' ?>>Türkçe</option>
                <option value="EN" <?= $adminLang == 'EN' ? 'selected' : '' ?>>English</option>
                <option value="RU" <?= $adminLang == 'RU' ? 'selected' : '' ?>>Russian</option>
            </select>
        </label>
    </div>
    <!-- //DEFAULT ADMIN LANGUAGE -->

    <!-- DEFAULT SITE LANGUAGE -->
    <div class="fieldset clearfix">
        <label><?= _('Sitenin Varsayılan Dili') ?></label>

        <label class="full">
            <select name="default_lang" class="form-control">
                <?php

                foreach ($Jambi->Loader->languages['list'] as $key => $value) {
                    $selected = $key == $Jambi->settings['default_lang'][$Jambi->Loader->languages['first']] ? 'selected' : '';

                    echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>';
                }

                ?>
            </select>
        </label>
    </div>
    <!-- //DEFAULT SITE LANGUAGE -->

    <!-- META -->
    <ol class="breadcrumb">
        <li><?= _('Meta') ?></li>
    </ol>

    <ul class="nav nav-tabs">
        <?php
        $i = 0;

        foreach ($Jambi->Loader->languages['list'] as $l => $value) {
            $activeClass = $i == 0 ? ' class="active"' : '';
            ?>

            <li <?= $activeClass ?>><a href="#meta<?= $l ?>" data-toggle="tab"><?= $value ?></a></li>

            <?php
            $i++;
        }
        ?>
    </ul>

    <div class="tab-content">
        <?php

        $i = 0;

        foreach ($Jambi->Loader->languages['list'] as $l => $value) {
            $cl = $i == 0 ? ' active' : null;
            ?>

            <div class="tab-pane meta <?= $cl ?>" id="meta<?= $l ?>" lang="<?= $l ?>">
                <div class="fieldset clearfix">
                    <label><?= _('Site Resmi') ?> (<?= $value ?>)</label>
                    <label class="full fileSelect">
                        <input id="meta_image<?= $l ?>Input" name="meta_image<?= $l ?>" class="form-control"
                               readonly="readonly" value="<?= $Jambi->settings['meta_image'][$l] ?>">
                        <a href="<?= JAMBI_ADMIN_CONTENT ?>plugins/filemanager/dialog.php?type=2&field_id=meta_image<?= $l ?>Input"
                           class="btn btn-sm btn-primary filemanager-iframe" type="button"><i
                                    class="fa fa-search"></i> <?= _('Dosya Seç') ?></a>

                        <?php
                        if ($Jambi->settings['meta_image'][$l]) {
                            echo '<a href="javascript:void(0)" id="meta_image' . $l . '" class="btn btn-sm btn-danger removeFile"><i class="fa fa-times"></i></a>';
                        }
                        ?>
                    </label>

                    <div class="alert alert-info help"><?= _("Sayfanın öne çıkarılan resmidir.") ?></div>
                </div>

                <div class="fieldset clearfix">
                    <label><?= _('Site Başlığı') ?> (<?= $value ?>)</label>
                    <label class="full">
                        <input type="text" name="meta_title<?= $l ?>" value="<?= $Jambi->settings['meta_title'][$l] ?>" class="form-control" data-limit-counter="60" />
                    </label>

                    <div class="alert alert-info help counter"><?= _('Önerilen sayfa başlığı limiti 60 karakterdir, <span class="limit_counter valid">60</span> karakter kaldı.') ?></div>
                </div>

                <div class="fieldset clearfix">
                    <label><?= _('Site Açıklaması') ?> (<?= $value ?>)</label>
                    <label class="full">
                        <input type="text" name="meta_desc<?= $l ?>" value="<?= $Jambi->settings['meta_desc'][$l] ?>" class="form-control" data-limit-counter="160" />
                    </label>

                    <div class="alert alert-info help counter"><?= _('Önerilen sayfa açıklama limiti 160 karakterdir, <span class="limit_counter valid">160</span> karakter kaldı.') ?></div>
                </div>
            </div>

            <?php
            $i++;
        }
        ?>
    </div>
    <!-- META -->

    <!-- DESIGN -->
    <ol class="breadcrumb">
        <li><?= _('Tasarım') ?></li>
    </ol>

    <ul class="nav nav-tabs">
        <?php
        $i = 0;

        foreach ($Jambi->Loader->languages['list'] as $l => $value) {
            $activeClass = $i == 0 ? ' class="active"' : '';
            ?>

            <li <?= $activeClass ?>><a href="#design<?= $l ?>" data-toggle="tab"><?= $value ?></a></li>

            <?php
            $i++;
        }
        ?>
    </ul>

    <div class="tab-content">
        <?php
        $i = 0;

        foreach ($Jambi->Loader->languages['list'] as $l => $value) {
            $cl = $i == 0 ? ' active' : null;
            ?>

            <div class="tab-pane design <?= $cl ?>" id="design<?= $l ?>" lang="<?= $l ?>">
                <!-- LOGO SVG -->
                <div class="fieldset clearfix">
                    <label><?= _('Logo') ?> (<?= $value ?>)</label>

                    <label class="full fileSelect">
                        <input id="logoSVG<?= $l ?>Input" name="logoSVG<?= $l ?>" class="form-control"
                               readonly="readonly" value="<?= $Jambi->settings['logoSVG'][$l] ?>">
                        <a href="<?= JAMBI_ADMIN_CONTENT ?>plugins/filemanager/dialog.php?type=2&field_id=logoSVG<?= $l ?>Input"
                           class="btn btn-sm btn-primary filemanager-iframe" type="button"><i
                                    class="fa fa-search"></i> <?= _('Dosya Seç') ?></a>
                        <a href="javascript:void(0)" id="logoSVG<?= $l ?>" class="btn btn-sm btn-danger removeFile"><i
                                    class="fa fa-times"></i></a>
                    </label>

                    <div class="alert alert-warning help"><?= $Jambi->Loader->locale['logoSVGHelp'] ?></div>
                </div>
                <!-- //LOGO SVG-->

                <!-- LOGO PNG -->
                <div class="fieldset clearfix">
                    <label><?= _('Logo') ?> (<?= $value ?>)</label>

                    <label class="full fileSelect">
                        <input id="logoPNG<?= $l ?>Input" name="logoPNG<?= $l ?>" class="form-control"
                               readonly="readonly" value="<?= $Jambi->settings['logoPNG'][$l] ?>">
                        <a href="<?= JAMBI_ADMIN_CONTENT ?>plugins/filemanager/dialog.php?type=2&field_id=logoPNG<?= $l ?>Input"
                           class="btn btn-sm btn-primary filemanager-iframe" type="button"><i
                                    class="fa fa-search"></i> <?= _('Dosya Seç') ?></a>
                        <a href="javascript:void(0)" id="logoPNG<?= $l ?>" class="btn btn-sm btn-danger removeFile"><i
                                    class="fa fa-times"></i></a>
                    </label>

                    <div class="alert alert-warning help"><?= $Jambi->Loader->locale['logoPNGHelp'] ?></div>
                </div>
                <!-- //LOGO PNG-->

                <!-- FAVICON -->
                <div class="fieldset clearfix">
                    <label><?= _('Favicon') ?> (<?= $value ?>)</label>

                    <label class="full fileSelect">
                        <input id="favicon<?= $l ?>Input" name="favicon<?= $l ?>" class="form-control"
                               readonly="readonly" value="<?= $Jambi->settings['favicon'][$l] ?>">
                        <a href="<?= JAMBI_ADMIN_CONTENT ?>plugins/filemanager/dialog.php?type=2&field_id=favicon<?= $l ?>Input"
                           class="btn btn-sm btn-primary filemanager-iframe" type="button"><i
                                    class="fa fa-search"></i> <?= _('Dosya Seç') ?></a>
                        <a href="javascript:void(0)" id="favicon<?= $l ?>" class="btn btn-sm btn-danger removeFile"><i
                                    class="fa fa-times"></i></a>
                    </label>

                    <div class="alert alert-warning help"><?= $Jambi->Loader->locale['faviconHelp'] ?></div>
                </div>
                <!-- //FAVICON -->
            </div>

            <?php
            $i++;
        }
        ?>
    </div>
    <!-- DESIGN -->
</form>