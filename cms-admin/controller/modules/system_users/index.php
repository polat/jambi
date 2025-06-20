<?php

$Jambi->DbSync->syncTable($Jambi->get['module']);

/**
 * Import Custom User Pages
 */
if ($Jambi->get['custom']) {
    require_once dirname(__FILE__) . '/custom/' . $Jambi->get['custom'] . '.php';
} /**
 * Import Users Page Handles
 */
else if ($Jambi->get['module']) {
    if ($Jambi->get['action']) {
        $handle = dirname(__FILE__) . '/handle/' . $Jambi->get['action'] . '.php';

        if (file_exists($handle)) {
            include $handle;
        } else {
            $defaultHandle = dirname(__FILE__) . '/../default/handle/' . $Jambi->get['action'] . '.php';
            if (file_exists($defaultHandle)) {
                include $defaultHandle;
            } else {
                $Jambi->Loader->Helper->alert(_('Sayfa bulunamadı!'), 'warning');
            }
        }
    } else {
        $handle = dirname(__FILE__) . '/handle/list.php';

        if (file_exists($handle)) {
            include $handle;
        } else {
            $defaultHandle = dirname(__FILE__) . '/../default/handle/list.php';
            if (file_exists($defaultHandle)) {
                include $defaultHandle;
            } else {
                $Jambi->Loader->Helper->alert(_('Sayfa bulunamadı!'), 'warning');
            }
        }
    }
}
