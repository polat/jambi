<?php

try {
    // PHP version compare between the user and the Jambi
    if (version_compare($ver = PHP_VERSION, $req = '7.0', '<')) {
        throw new \RuntimeException(sprintf(_('Kullandığınız PHP versiyonu <strong>%s</strong>, Cms-Admin minimum PHP <strong>%s</strong> versiyonu ile çalışır.'), $ver, $req));
    }

    // Library Control
    $missingList = null;
    $requiredModuleList = array('curl', 'calendar', 'fileinfo', 'gettext', 'gd', 'iconv', 'mbstring', 'pdo', 'zip');

    foreach ($requiredModuleList as $item) {
        if (!extension_loaded($item)) {
            $missingList .= $item . ', ';
        }
    }

    if (!is_null($missingList)) {
        throw new \RuntimeException(_("Jambi'nin düzgün çalışabilmesi için şu kütüphaneleri yüklemeniz gerekli -> ") . rtrim($missingList, ', '));
    }

    mb_internal_encoding('UTF-8');

    // Require necessary files.
    require_once dirname(__FILE__) . '/system/_bootstrap.php';

    // Is Jambi installation ready?
    if ($Jambi->Loader->Db->query("SHOW TABLES LIKE 'system_users'")->rowCount() == 0) {
        if (file_exists(ABSPATH . 'install/index.php')) {
            header('Location: ' . BASEURL . 'install/');
        } else {
            throw new \RuntimeException(_('Cms-Admin Kurulum dosyası bulunamadı! Lütfen kontrol ediniz.'));
        }
    } else if ($Jambi->Login->checkLogin()) {
        include ABSPATH . 'cms-admin/controller/layouts/header.php';

        if ($Jambi->get['module']) {
            $customFile = ABSPATH . 'cms-admin/controller/modules/' . $Jambi->get['module'] . '/index.php';

            if (file_exists($customFile)) {
                include $customFile;
            } else if ($Jambi->Loader->Compiler->tableExist($Jambi->get['module'])) {
                include ABSPATH . 'cms-admin/controller/modules/default/index.php';
            } else {
                $Jambi->Loader->Helper->alert(_('Sayfa bulunamadı!'), 'warning');
            }

        } else if ($Jambi->get['action'] == 'logout' && $Jambi->Login->logout()) {
            header("Location: " . JAMBI_ADMIN);
        } else {
            include ABSPATH . 'cms-admin/controller/layouts/home.php';
        }

        include ABSPATH . 'cms-admin/controller/layouts/footer.php';
    } else {
        include ABSPATH . 'cms-admin/controller/layouts/login.php';
    }
} catch (RuntimeException $e) {
    echo $e->getMessage();
}