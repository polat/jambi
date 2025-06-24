<?php

if ($Jambi->table['type'] == 'list') {
    $save = new SaveList($Loader, $Jambi->get['module'], $_POST);
} else if ($Jambi->table['type'] == 'option') {
    $save = new SaveOption($Loader, $Jambi->get['module'], $_POST);
}

// Redirect Process
header('Location: ' . $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => $Jambi->table['type'], 'id'))));