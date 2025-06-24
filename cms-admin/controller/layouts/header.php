<?php

// Panel Title
$siteTitle = $Jambi->settings['meta_title'][$Jambi->settings['default_admin_lang'][$Jambi->Loader->languages['first']]] == '' ? null : $Jambi->settings['meta_title'][$Jambi->settings['default_admin_lang'][$Jambi->Loader->languages['first']]] . ' - ';

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" type="image/x-icon" href="<?= JAMBI_ADMIN_CONTENT ?>css/images/fav.ico">
    <title><?= $siteTitle . _('Yönetim Paneli') ?></title>

    <!--Mobile first-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>css/bootstrap.min.css">

    <!-- Add custom CSS here -->
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>css/sb-admin.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_CONTENT . 'css/sb-admin.min.css') ?>">
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>css/font-awesome/css/font-awesome.min.css">

    <!-- JavaScript -->
    <script>
        var BASEURL = '<?= BASEURL ?>';
        var JAMBI_ADMIN_CONTENT = '<?= JAMBI_ADMIN_CONTENT ?>';
    </script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/jquery-1.10.2.js"></script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/bootstrap.js"></script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/jquery.maskMoney.min.js"></script>

    <!-- DataTables -->
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>plugins/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>plugins/datatables/DT_bootstrap.js"></script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>plugins/datatables/jquery.dataTables.rowReordering.js"></script>
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>plugins/datatables/css/demo_page.css">
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>plugins/datatables/css/DT_bootstrap.css">

    <!-- jQuery UI -->
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>css/ui-lightness/jquery-ui-1.10.4.custom.css">
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/jquery-ui.multidatespicker.min.js"></script>

    <!-- Fancybox -->
    <link rel="stylesheet" href="<?= JAMBI_ADMIN_CONTENT ?>css/jquery.fancybox.css">
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/jquery.fancybox.pack.js"></script>

    <!-- TinyMCE -->
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>plugins/tinymce/tinymce.min.js"></script>

    <!-- Admin Script -->
    <script type="text/javascript" src="<?= JAMBI_ADMIN_CONTENT ?>js/script.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_CONTENT . 'js/script.min.js') ?>"></script>
</head>
<body>
<div id="wrapper">
    <?php include_once 'sidebar.php'; ?>

    <div id="page-wrapper">
        <div class="col-lg-12">
				