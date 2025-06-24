<form id="recordForm" method="post"
      action="<?= $Jambi->Loader->Helper->handleGetUrlParameters(array('change' => array('action' => 'save'))) ?>"
      role="form" autocomplete="off" enctype="multipart/form-data">
    <ul class="nav nav-tabs">
        <?php

        $getFields = new Fields($Loader, $Jambi->get['module'], $Jambi->get['id']);

        $i = 0;
        foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
            if (!empty($getFields->result[$lang])) {
                $activeClass = $i == 0 ? ' class="active"' : null;
                echo '<li ' . $activeClass . '><a href="#' . $lang . '" data-toggle="tab">' . $value . '</a></li>';
                $i++;
            }
        }

        if ($Jambi->get['id'] && !empty($getFields->gallery)) {
            foreach ($getFields->gallery as $item) {
                echo '<li><a href="#' . $item['label'] . '" data-toggle="tab">' . $item['title'] . '</a></li>';
            }
        }

        if ($getFields->permalink == true) {
            echo '<li><a href="#meta" data-toggle="tab">' . _('Sayfa Ayarları') . '</a></li>';
        }

        ?>
    </ul>

    <div class="tab-content <?= $Jambi->get['module'] ?>">
        <?php

        echo '<input type="hidden" name="id" value="'. $Jambi->get['id'] .'" />';
        echo '<input type="hidden" name="rec_status" />';

        /**
         * Content Tab For Each Language
         */
        $i = 0;
        foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
            $recordID = null;
            $activeClass = $i == 0 ? ' active' : null;

            echo '<div class="tab-pane ' . $activeClass . '" id="' . $lang . '">';
            echo '<ul class="metabox">';

            if (is_array($getFields->result[$lang])) {
                foreach ($getFields->result[$lang] as $item) {
                    echo $item;
                }
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

        /**
         * Gallery Tab
         */
        if ($Jambi->get['id'] && !empty($getFields->gallery)) {
            foreach ($getFields->gallery as $item) {
                echo '<div class="tab-pane" id="' . $item['label'] . '">';

                if ($item['options']['help'] != '') {
                    echo '<div class="alert alert-warning help" style="margin-left: 20px; margin-right: 20px;">' . $item['options']['help'] . '</div>';
                }

                include 'content/plugins/gallery/index.php';

                echo '</div>';
            }
        }

        /**
         * Page Settings Tab
         */
        if ($getFields->permalink) {
            echo '<div class="tab-pane" id="meta">';

            $id = $Jambi->get['id'];

            /* Page SEO Part */
            echo '<ol class="breadcrumb">
                      <li>' . _('SEO') . '</li>
                  </ol>
    
                  <ul class="nav nav-tabs">';

            $i = 0;
            foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
                $activeClass = $i == 0 ? ' class="active"' : null;
                echo '<li ' . $activeClass . '><a href="#meta' . $lang . '" data-toggle="tab">' . $value . '</a></li>';
                $i++;
            }

            echo '</ul>
    
                  <div class="tab-content">';

            $i = 0;
            foreach ($Jambi->Loader->languages['list'] as $lang => $value) {
                $meta = $Jambi->Loader->Db->selectOne("SELECT `meta_image`, `meta_title`, `meta_desc`, `sitemap` FROM `system_meta` WHERE `lang` = :lang AND `rec_table` = :rec_table AND `rec_id` = :id", array('lang' => $lang, 'rec_table' => $Jambi->get['module'], 'id' => $id));
                $activeClass = $i == 0 ? ' active' : null;
                empty($meta) ? $sitemapChecked[$lang][1] = 'checked' : $sitemapChecked[$lang][$meta['sitemap']] = 'checked';

                echo '<div class="tab-pane meta ' . $activeClass . '" id="meta' . $lang . '" lang="' . $lang . '">
                <div class="fieldset clearfix">
                    <label>' . _('Sitemap Görünürlüğü') . ' (' . $value . ')</label>
                    <label class="full">        
                        <input type="radio" name="sitemap' . $lang . '" value="1" class="form-control" '. $sitemapChecked[$lang][1] .'>' . _('Açık') . '
                        <input type="radio" name="sitemap' . $lang . '" value="0" class="form-control" '. $sitemapChecked[$lang][0] .'>' . _('Kapalı') . '
                    </label>
                </div>
                <div class="fieldset clearfix">
                    <label>' . _('Sayfa Resmi') . ' (' . $value . ')</label>
                    <label class="full fileSelect">
                        <input id="meta_image' . $lang . 'Input" name="meta_image' . $lang . '" class="form-control" readonly="readonly" value="' . $meta['meta_image'] . '">
                        <a href="' . JAMBI_ADMIN_CONTENT . 'plugins/filemanager/dialog.php?type=2&field_id=meta_image' . $lang . 'Input" class="btn btn-sm btn-primary filemanager-iframe" type="button"><i class="fa fa-search"></i> ' . _('Dosya Seç') . '</a>
                        <a href="javascript:void(0)" id="meta_image' . $lang . '" class="btn btn-sm btn-danger removeFile"><i class="fa fa-times"></i></a>
                    </label>
                    
                    <div class="alert alert-info help">' . _("Sayfanın öne çıkarılan resmidir.") . '</div>
                </div>
    
                <div class="fieldset clearfix">
                    <label>' . _('Sayfa Başlığı') . ' (' . $value . ')</label>
                    <label class="full">
                        <input type="text" name="meta_title' . $lang . '" class="form-control" value="' . $meta['meta_title'] . '" data-limit-counter="60"/>
                    </label>
                    <div class="alert alert-info help counter">' . _('Önerilen sayfa başlığı limiti 60 karakterdir, <span class="limit_counter valid">60</span> karakter kaldı.') . '</div>
                </div>
    
                <div class="fieldset clearfix">
                    <label>' . _('Sayfa Açıklaması') . ' (' . $value . ')</label>
                    <label class="full">
                        <input type="text" name="meta_desc' . $lang . '" class="form-control" value="' . $meta['meta_desc'] . '" data-limit-counter="160" />
                    </label>
                    <div class="alert alert-info help counter">' . _('Önerilen sayfa açıklama limiti 160 karakterdir, <span class="limit_counter valid">160</span> karakter kaldı.') . '</div>
                </div>
                </div>';

                $i++;
            }

            echo '</div>
                  <div class="clear"></div>';
        }

        ?>
    </div>
</form>