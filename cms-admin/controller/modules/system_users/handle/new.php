<?php

if (trim($_POST['username']) != '' && trim($_POST['password']) != '') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordAgain = $_POST['password_again'];
    $userRank = $_POST['rank'];
    $userStatus = $_POST['account_status'];

    if ($password == $passwordAgain) {
        $result = $Jambi->Member->addMember($username, password_hash($password, PASSWORD_DEFAULT), $userRank, $userStatus);
        $Jambi->Loader->Helper->alert($result['message'], $result['result']);
    }
}

?>

<div class='module_title'>
    <div class='pull-left'>
        <h4><?= _('Kullanıcılar') ?></h4>
    </div>

    <div class='pull-right'>
        <button type='submit' class='btn btn-danger' id='form-submit'><?= _('Kaydet') ?></button>
    </div>

    <div style='clear: both;'></div>
</div>

<!-- ADD NEW USER -->
<form id='recordForm' method='post' action='' enctype="multipart/form-data">
    <div class="fieldset clearfix">
        <label><?= _('Kullanıcı Yetkisi') ?></label>

        <label class="full">
            <label class="radioLabel"><input type="radio" name="rank" value="0" checked> <?= _('Kullanıcı') ?></label>
            <label class="radioLabel"><input type="radio" name="rank" value="1"> <?= _('Yönetici') ?></label>
        </label>
    </div>

    <div class="fieldset clearfix">
        <label><?= _('Hesap Durumu') ?></label>

        <label class="full">
            <label class="radioLabel"><input type="radio" name="account_status" value="1" checked> <?= _('Aktif') ?>
            </label>
            <label class="radioLabel"><input type="radio" name="account_status" value="0"> <?= _('Pasif') ?></label>
        </label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Kullanıcı Adı') ?></label>
        <label class='full'><input name='username' type='text' class='form-control'/></label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Şifre') ?></label>
        <label class='full'><input name='password' type='password' class='form-control'/></label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Şifre Onayı') ?></label>
        <label class='full'><input name='password_again' type='password' class='form-control'/></label>
    </div>
</form>
<!-- //ADD NEW USER -->