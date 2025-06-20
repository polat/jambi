<!-- Sidebar -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only"><?= _('Navigasyonu aç/kapat') ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="<?= BASEURL ?>" target="_blank"><i
                    class="fa fa-home"></i> <?= _('Siteyi Görüntüle') ?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
            <?php
            $active_settings = '';
            $active_homepage = '';
            $activeFirstStep[] = '';
            $activeSecondStep[] = '';

            if ($Jambi->get['module'] != false) {
                $activeFirstStep[$Jambi->get['module']] = ' class="active"';

                if ($Jambi->get['module'] == 'system_settings') {
                    $settings_collapse = 'in';
                    $activeSecondStep[$Jambi->get['custom']] = ' class="active"';
                }
            } else {
                $active_homepage = ' class="active"';
            }

            ?>

            <li <?= $activeFirstStep['media_library'] ?>><a href='<?= JAMBI_ADMIN ?>?module=media_library'><i class='fa fa-camera'></i> <span class='withIcon'><?= _('Medya Kütüphanesi') ?></span></a></li>
            <li <?= $activeFirstStep['system_labels'] ?>><a href='<?= JAMBI_ADMIN ?>?module=system_labels&action=list&rec_status=0'><i class='fa fa-edit'></i> <span class='withIcon'><?= _('Tanımlamalar') ?></span></a></li>
            <li <?= $activeFirstStep['system_menu'] ?>><a href='<?= JAMBI_ADMIN ?>?module=system_menu&action=list&rec_status=0'><i class='fa fa-align-left'></i> <span class='withIcon'><?= _('Menü') ?></span></a></li>
            <li <?= $activeFirstStep['system_pages'] ?>><a href='<?= JAMBI_ADMIN ?>?module=system_pages&action=list&rec_status=0'><i class='fa fa-file-o'></i> <span class='withIcon'><?= _('Sayfalar') ?></_></span></a></li>

            <?php

            foreach ($Jambi->Loader->Compiler->compiledTables as $key => $value) {
                if ($value['view'] != false) {
                    if ($value['group'] != '') {
                        $group_modules[$value['group']][] = array('name' => $value['name'], 'title' => $value['title'], 'type' => $value['type']);
                    } else {

                        $single_modules .= '<li ' . $activeFirstStep[$key] . '><a href="' . JAMBI_ADMIN . '?module=' . $value['name'] . '&action=' . $value['type'] . '&rec_status=0"><i class="fa fa-file"></i> <span class="withIcon">' . $value['title'] . '</span></a></li>';
                    }
                }
            }

            if (is_array($group_modules)) {
                foreach ($group_modules as $key => $value) {
                    $group_active = null;
                    $group_collapse = null;
                    $groupList = null;

                    foreach ($value as $v) {
                        $active_link = $Jambi->get['module'] == $v['name'] ? 'class = "active"' : null;

                        if ($Jambi->get['module'] == $v['name']) {
                            $group_active = ' class="active"';
                            $group_collapse = ' in';
                        }

                        $groupList .= '<li ' . $active_link . '><a href="' . JAMBI_ADMIN . '?module=' . $v['name'] . '&action=' . $v['type'] . '&rec_status=0">' . $v['title'] . '</a></li>';
                    }

                    echo '<li' . $group_active . '>';
                    echo '<a href="javascript:void(0)" data-toggle="collapse" data-target="#group' . str_replace(' ', '', $key) . '" class="collapsed"><i class="fa fa-level-down"></i> <span class="withIcon">' . $key . '</span></a>';
                    echo '<ul id="group' . str_replace(' ', '', $key) . '" class="collapse' . $group_collapse . '">';
                    echo $groupList;
                    echo '</ul>';
                    echo '</li>';
                }
            }

            echo $single_modules;
            ?>

            <li <?= $activeFirstStep['system_settings'] ?>>
                <a href="javascript:void(0)" data-toggle="collapse" data-target="#settings_dropdown"
                   class="collapsed"><i class="fa fa-cog"></i> <span class="withIcon"><?= _('Ayarlar') ?></span></a>

                <ul id="settings_dropdown" class="collapse <?= $settings_collapse ?>">
                    <li <?= $activeSecondStep['general'] ?>>
                        <a href="<?= JAMBI_ADMIN ?>?module=system_settings&custom=general"><?= _('Genel') ?></a>
                    </li>
                    <li <?= $activeSecondStep['popup'] ?>>
                        <a href="<?= JAMBI_ADMIN ?>?module=system_settings&custom=popup"><?= _('Popup') ?></a>
                    </li>
                </ul>
            </li>
        </ul>

        <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?= $Jambi->Loader->Session->get('user_display_name') ?> <b class="caret"></b></a>

                <ul class="dropdown-menu">
                    <li><a href="<?= JAMBI_ADMIN ?>?module=system_users&custom=profile"><i
                                    class="fa fa-user"></i> <?= _('Profili Düzenle') ?></a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="<?= JAMBI_ADMIN ?>?action=logout"><i class="fa fa-sign-out"></i> <?= _('Çıkış Yap') ?>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- /.navbar-collapse -->
</nav>