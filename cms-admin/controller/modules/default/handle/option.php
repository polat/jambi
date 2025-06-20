<form id="recordForm" method="post"
      action="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'save'))) ?>"
      role="form" autocomplete="off" enctype="multipart/form-data">
    <ul class="nav nav-tabs">
        <?php

        $getFields = new Fields($Loader, $Jambi->get['module']);

        $i = 0;
        foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
            $activeClass = $i == 0 ? ' class="active"' : null;
            echo '<li ' . $activeClass . '><a href="#' . $lang . '" data-toggle="tab">' . $value . '</a></li>';
            $i++;
        }

        ?>
    </ul>

    <div class="tab-content <?= $Jambi->get['module'] ?>">
        <?php

        /**
         * Content Tab For Each Language
         */
        $i = 0;
        foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
            $activeClass = $i == 0 ? ' active' : null;

            echo '<div class="tab-pane ' . $activeClass . '" id="' . $lang . '">';
            echo '<ul class="metabox">';

            foreach ($getFields->result[$lang] as $item) {
                echo $item;
            }

            echo '</ul>';

            if ($getFields->dynamic) {
                echo '<div class="metabox_alert">
                        <div class="metabox_alert_inner">
                            <div class="metabox_alert_title">' . _('Bu alanı silmek istiyor musunuz ?') . '</div>
    
                            <div class="metabox_alert_buttons">
                                <a href="javascript:void(0)" class="metabox_alert_yes">' . _('Evet') . '</a>
                                <a href="javascript:void(0)" class="metabox_alert_no">' . _('Hayır') . '</a>
                            </div>
                        </div>
                    </div>';
            }

            echo '</div>';

            $i++;
        }

        ?>
    </div>
</form>