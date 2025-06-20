<?php

$user = $Jambi->Loader->Session->get('user_occupant');

if ($_POST) {
    $select = $Jambi->Loader->Db->selectOne("SELECT * FROM `system_users` WHERE `username` = :username", array('username' => $user));

    if (password_verify($_POST['current_password'], $select['password']) == true && $_POST['new_password'] != '' && $_POST['new_password'] == $_POST['new_password_again']) {
        $newpass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $Jambi->Loader->Db->update('system_users', array('password' => $newpass), "username='$user'");
        $Jambi->Loader->Helper->alert(_('Şifre değiştirildi!'), 'success');
    } else {
        $Jambi->Loader->Helper->alert(_('Girilen bilgiler yanlış ya da eksik.'), 'danger');
    }
}

?>

<div class='module_title'>
    <div class='pull-left'>
        <h4><?= _('Profili Düzenle') ?></h4>
    </div>

    <div class='pull-right'>
        <button type='submit' class='btn btn-danger' id='form-submit'><?= _('Kaydet') ?></button>
    </div>

    <div style='clear: both;'></div>
</div>

<!-- USER INFORMATIONS -->
<ol class="breadcrumb">
    <li><?= _('Kullanıcı Bilgileri') ?></li>
</ol>

<div class='fieldset clearfix'>
    <label><?= _('Kullanıcı Adı') ?></label>
    <label class='full'><input name='username' type='text' readonly="readonly" value="<?= $user ?>"
                               class='form-control'/></label>
</div>
<!-- //USER INFORMATIONS -->

<!-- CHANGE PASSWORD -->
<ol class="breadcrumb">
    <li><?= _('Şifre Değiştirme') ?></li>
</ol>

<form id='recordForm' method='post' action=''>
    <div class='fieldset clearfix'>
        <label><?= _('Geçerli Şifre') ?></label>
        <label class='full'><input name='current_password' type='password' class='form-control'/></label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Yeni Şifre') ?></label>
        <label class='full'><input name='new_password' type='password' class='form-control'/></label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Yeni Şifre Tekrar') ?></label>
        <label class='full'><input name='new_password_again' type='password' class='form-control'/></label>
    </div>
</form>
<!-- //CHANGE PASSWORD -->
