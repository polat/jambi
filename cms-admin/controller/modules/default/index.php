<?php

require_once ABSPATH . 'cms-admin/library/__autoloader.php';

if (!$Jambi->Loader->Compiler->tableExist($Jambi->get['module'])) {
    $Jambi->Loader->Helper->alert(_('Modül Bulunamadı!'), 'danger');
} else {
    $Jambi->DbSync->syncTable($Jambi->get['module']);
    require_once ABSPATH . 'cms-admin/controller/layouts/title.php';

    if ($Jambi->get['action']) {
        $moduleHandle = dirname(__FILE__) . '/../' . $Jambi->get['module'] . '/handle/' . $Jambi->get['action'] . '.php';
        $handle = dirname(__FILE__) . '/handle/' . $Jambi->get['action'] . '.php';

        if (file_exists($moduleHandle)) {
            include $moduleHandle;
        } else {
            if (file_exists($handle)) {
                include $handle;
            } else {
                $Jambi->Loader->Helper->alert(_('Sayfa bulunamadı!'), 'warning');
            }
        }
    } else {
        $moduleHandle = dirname(__FILE__) . '/../' . $Jambi->get['module'] . '/handle/list.php';
        $handle = dirname(__FILE__) . '/handle/list.php';

        if (file_exists($moduleHandle)) {
            include $moduleHandle;
        } else {
            if (file_exists($handle)) {
                include $handle;
            } else {
                $Jambi->Loader->Helper->alert(_('Sayfa bulunamadı!'), 'warning');
            }
        }
    }
}
