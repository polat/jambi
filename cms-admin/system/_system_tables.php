<?php

/**
 * System Files
 */
$system['system_files'] = array(
    'view' => false,
    'fields' => array(
        array(
            'title' => 'rec_id',
            'key' => 'rec_id',
            'type' => 'number',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'rec_table',
            'key' => 'rec_table',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'field_name',
            'key' => 'field_name',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'name',
            'key' => 'name',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'size',
            'key' => 'size',
            'type' => 'number',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'type',
            'key' => 'type',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'title',
            'key' => 'title',
            'type' => 'text',
            'lang' => true,
            'list' => false
        ),
        array(
            'title' => 'description',
            'key' => 'description',
            'type' => 'text',
            'lang' => true,
            'list' => false
        )
    )
);


/**
 * System Labels
 */
$system['system_labels'] = array(
    'title' => _('Tanımlamalar'),
    'sort' => array('field' => 'sequence', 'direction' => 'ASC'),
    'pagination' => 25,
    'view' => false,
    'fields' => array(
        array(
            'title' => _('Etiket'),
            'key' => 'label',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => true,
            'options' => array(
                'identity' => true,
                'rank' => array(1 => 'disable', 2 => 'hidden')
            )
        ),
        array(
            'title' => _('Tip'),
            'key' => 'type',
            'type' => 'radio',
            'lang' => false,
            'list' => false,
            'options' => array(
                'data' => array('content' => _('İçerik'), 'file' => _('Dosya'))
            )
        ),
        array(
            'title' => _('Dosya'),
            'key' => 'file',
            'type' => 'file',
            'lang' => true,
            'list' => false
        ),
        array(
            'title' => _('İçerik'),
            'key' => 'content',
            'type' => 'textarea',
            'lang' => true,
            'list' => true
        )
    )
);


/**
 * System Login Attempts
 */
$system['system_login_attempts'] = array(
    'view' => false,
    'fields' => array(
        array(
            'title' => 'username',
            'key' => 'username',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'ip',
            'key' => 'ip',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'time',
            'key' => 'time',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'status',
            'key' => 'status',
            'type' => 'number[TINYINT]',
            'lang' => false,
            'list' => false
        )
    )
);

/**
 * System Menu
 */
$system['system_menu'] = array(
    'title' => _('Menü'),
    'view' => false,
    'fields' => array(
        array(
            'title' => _('Etiket'),
            'key' => 'label',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => true,
            'options' => array(
                'identity' => true,
                'rank' => array(1 => 'disable', 2 => 'hidden')
            )
        ),
        array(
            'title' => _('Menü Başlığı'),
            'key' => 'title',
            'type' => 'text',
            'lang' => true,
            'list' => true
        )
    )
);

/**
 * System Meta
 */
$system['system_meta'] = array(
    'view' => false,
    'fields' => array(
        array(
            'title' => 'rec_table',
            'key' => 'rec_table',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'rec_id',
            'key' => 'rec_id',
            'type' => 'number',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'lang',
            'key' => 'lang',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'title',
            'key' => 'title',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'url',
            'key' => 'url',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'full_url',
            'key' => 'full_url',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'short_url',
            'key' => 'short_url',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'meta_image',
            'key' => 'meta_image',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'meta_title',
            'key' => 'meta_title',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'meta_desc',
            'key' => 'meta_desc',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'sitemap',
            'key' => 'sitemap',
            'type' => 'number[SMALLINT]',
            'lang' => false,
            'list' => false
        )
    )
);


/**
 * System Options
 */
$system['system_options'] = array(
    'view' => false,
    'fields' => array(
        array(
            'title' => 'rec_table',
            'key' => 'rec_table',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'option_key',
            'key' => 'option_key',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'lang',
            'key' => 'lang',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'option_value',
            'key' => 'option_value',
            'type' => 'text',
            'lang' => false,
            'list' => false
        )
    )
);


/**
 * System Pages
 */
$system['system_pages'] = array(
    'title' => _('Sayfalar'),
    'sort' => array('field' => 'sequence', 'direction' => 'ASC'),
    'pagination' => 25,
    'view' => false,
    'fields' => array(
        array(
            'title' => _('Yeri'),
            'key' => 'display',
            'type' => 'checkbox',
            'lang' => false,
            'list' => false,
            'options' => array(
                'default' => 1,
                'lookup' => array(
                    'table' => 'system_menu', 'field' => 'title'
                )
            )
        ),
        array(
            'title' => 'Target',
            'key' => 'target',
            'type' => 'radio',
            'lang' => false,
            'list' => false,
            'options' => array(
                'data' => array('_self' => _('Self'), '_blank' => _('Blank'))
            )
        ),
        array(
            'title' => _('Kayıt Tipi'),
            'key' => 'recordType',
            'type' => 'radio',
            'lang' => false,
            'list' => false,
            'options' => array(
                'data' => array('page' => _('Sayfa'), 'link' => _('Link'))
            )
        ),
        array(
            'title' => _('Modül Tipi'),
            'key' => 'moduleType',
            'type' => 'radio',
            'lang' => false,
            'list' => false,
            'options' => array(
                'data' => array('standard' => _('Standart'), 'landing' => _('Landing'))
            )
        ),
        array(
            'title' => _('Modül'),
            'key' => 'module',
            'type' => 'text',
            'list' => true,
            'lang' => false,
            'options' => array(
                'identity' => true,
                'rank' => array(1 => 'disable', 2 => 'hidden')
            )
        ),
        array(
            'title' => _('Dosya'),
            'key' => 'file',
            'type' => 'file',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => _('İkon'),
            'key' => 'icon',
            'type' => 'file',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => _('Üst Menü'),
            'key' => 'cat',
            'type' => 'category',
            'lang' => false,
            'list' => true,
            'options' => array(
                'lookup' => array(
                    'table' => 'system_pages', 'field' => 'title'
                )
            )
        ),
        array(
            'title' => _('Menü Başlığı'),
            'key' => 'menu_title',
            'type' => 'text',
            'lang' => true,
            'list' => false
        ),
        array(
            'title' => _('Sayfa Başlığı'),
            'key' => 'title',
            'type' => 'text',
            'lang' => true,
            'list' => true
        ),
        array(
            'title' => _('URL'),
            'key' => 'url',
            'type' => 'permalink',
            'lang' => true,
            'list' => false,
            'options' => array('attach' => 'title')
        ),
        array(
            'title' => _('İçerik'),
            'key' => 'content',
            'type' => 'editor',
            'lang' => true,
            'list' => false
        ),
        array(
            'title' => _('Galeri'),
            'key' => 'gallery',
            'type' => 'gallery',
            'lang' => false,
            'list' => false
        )
    )
);


/**
 * System Settings
 */
$system['system_settings'] = array(
    'title' => _('Ayarlar'),
    'view' => false,
    'fields' => array(
        array(
            'title' => 'lang',
            'key' => 'lang',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'option_key',
            'key' => 'option_key',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => true
        ),
        array(
            'title' => 'option_value',
            'key' => 'option_value',
            'type' => 'text',
            'lang' => false,
            'list' => true
        )
    )
);


/**
 * System Users
 */
$system['system_users'] = array(
    'title' => _('Kullanıcılar'),
    'view' => false,
    'fields' => array(
        array(
            'title' => 'account_status',
            'key' => 'account_status',
            'type' => 'number[SMALLINT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'display_name',
            'key' => 'display_name',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'email',
            'key' => 'email',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'username',
            'key' => 'username',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'password',
            'key' => 'password',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'rank',
            'key' => 'rank',
            'type' => 'number[SMALLINT]',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'permissions',
            'key' => 'permissions',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'cms_lang',
            'key' => 'cms_lang',
            'type' => 'text',
            'lang' => false,
            'list' => false
        ),
        array(
            'title' => 'register_date',
            'key' => 'register_date',
            'type' => 'text[TINYTEXT]',
            'lang' => false,
            'list' => false
        )
    )
);