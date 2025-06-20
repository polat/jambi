<?php

$id = $Jambi->get['id'];

if (trim($_POST['username']) != '' && trim($_POST['password']) != '') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userRank = $_POST['rank'];
    $userStatus = $_POST['account_status'];
    $result = $Jambi->Member->updateMember($id, $username, $password, $userRank, $userStatus);

    $Jambi->Loader->Helper->alert($result['message'], $result['result']);
}

$select = $Jambi->Loader->Db->selectOne("SELECT * FROM `system_users` WHERE `id` = :id", array('id' => $id));

if ($select['account_status'] == 1) {
    $statusOfActive = 'checked';
    $statusOfPassive = null;
} else {
    $statusOfPassive = 'checked';
    $statusOfActive = null;
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
<form id='recordForm' method='post' action='<?= JAMBI_ADMIN . '?module=system_users&action=edit&id=' . $id ?>'>
    <input type="hidden" name="sequence" value="<?= $sequence['MAX(sequence)'] + 1 ?>"/>
    <input type="hidden" name="rank" value="<?= $select['rank'] ?>"/>

    <div class='fieldset clearfix'>
        <label><?= _('Kullanıcı Adı') ?></label>
        <label class='full'><input name='username' type='text' class='form-control' value="<?= $select['username'] ?>"/></label>
    </div>

    <div class='fieldset clearfix'>
        <label><?= _('Şifre') ?></label>
        <label class='full'><input name='password' type='password' class='form-control'
                                   value=""/></label>
    </div>

    <div class="fieldset clearfix">
        <label><?= _('Hesap Durumu') ?></label>

        <label class="full">
            <label class="radioLabel"><input type="radio" name="account_status"
                                             value="1" <?= $statusOfActive ?>> <?= _('Aktif') ?></label>
            <label class="radioLabel"><input type="radio" name="account_status"
                                             value="0" <?= $statusOfPassive ?>> <?= _('Pasif') ?></label>
        </label>
    </div>
</form>
<!-- //ADD NEW USER -->