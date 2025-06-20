<!DOCTYPE html>
<html>
<head>
    <!--Mobile first-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" type="image/x-icon" href="<?= JAMBI_ADMIN_CONTENT ?>css/images/fav.ico">
    <title><?= _('Yönetim Paneli') ?></title>

    <link rel="stylesheet" type="text/css" href="<?= JAMBI_ADMIN_CONTENT ?>css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?= JAMBI_ADMIN_CONTENT ?>css/login.min.css"/>
</head>
<body>
<div class="col-md-3 col-md-offset-col-md-2 col-md-offset-4">

<?php

if ($_REQUEST['username'] && $_REQUEST['password']) {
    $Jambi->Login->init($_REQUEST['username'], $_REQUEST['password']);

    if ($Jambi->Login->result === 0) {
        header('Location: ' . $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('username', 'password'))));
    } else if ($Jambi->Login->result === 1) {
        $Jambi->Loader->Helper->alert(_('Bu kullanıcı hesabı pasif durumda! Giriş yapabilmeniz için "Yönetici" hesabın, bu hesabı aktif hale getirmesi gerekir.'), 'danger');
    } else if ($Jambi->Login->result === 2) {
        $Jambi->Loader->Helper->alert(_('IP adresiniz engellendi!'), 'danger');
    } else {
        $Jambi->Loader->Helper->alert(_('Kullanıcı adı ya da şifre hatalı!'), 'danger');
    }
}

?>

<form action="" class="form-signin" role="form" method="post">
    <h4><?= _('Kullanıcı Adı') ?></h4>
    <input type="text" class="form-control" name="username" required autofocus>
    <h4><?= _('Şifre') ?></h4>
    <input type="password" class="form-control" name="password" required>
    <button class="btn btn-lg btn-primary btn-block" type="submit"><?= _('Giriş Yap') ?></button>
</form>

</div>
</body>
</html>