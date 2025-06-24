<?php

// Require necessary files.
$path = realpath(dirname(__FILE__) . '/../../../system');
require_once($path . '/_init.php');
require_once('includes.php');

$field_name = $item['label'];

/* Gallery Items */
$galleryItems = '';
$files = $Jambi->Loader->Db->select("SELECT * FROM `system_files` WHERE `rec_table` = :rec_table AND `rec_id` = :rec_id AND `field_name` = :field_name ORDER BY `sequence` ASC", array('rec_table' => $Jambi->get['module'], 'rec_id' => $Jambi->get['id'], 'field_name' => $field_name));

$folderPath = BASEURL . UPLOADURL . 'thumbnail/';
$folderPathSvg = BASEURL . UPLOADURL . '/';

foreach ($files as $file) {
    if ($file['type'] == 'image/svg+xml') {
        $folderPath = $folderPathSvg;
    }

    $galleryItems .= '<li id="listItem_' . $file['id'] . '"><span class="tools"><a href="javascript:void(0)" class="fa fa-edit" title="Düzenle"></a><a href="javascript:void(0)" class="fa fa-trash-o deleteImg" rel="' . $file['id'] . '" title="Sil"></a></span><img src="' . $folderPath . $file['name'] . '" class="handle" /></li>';
}

?>

<div class="dropzone <?= $field_name ?>">
	<span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span><?= _('Dosyaları Seçin') ?></span>
        <!-- The file input field used as target for the file upload widget -->
        <input class="fileupload" type="file" name="files[]" multiple>
    </span>
    <em><?= _('Ya da bu alana sürükleyip bırakın.') ?></em>

    <!-- The global progress bar -->
    <div class="progress progress-striped">
        <div class="progress-bar"></div>
    </div>
    <!-- The container for the uploaded files -->
</div>

<div class="infoSort <?= $field_name ?>"></div>

<div class="gallery-div <?= $field_name ?>">
    <ul class="gallery-list">
        <?= $galleryItems ?>
    </ul>

    <div class="clear"></div>
</div>

<script>
    $(document).ready(function () {
        getFileUpload('<?= $Jambi->get['module'] ?>', '<?= $Jambi->get['id'] ?>', '<?= $field_name ?>', '<?= JAMBI_ADMIN_CONTENT ?>');
    });
</script>