<?php
return array(
    'title' => lang('people::people-directory'),
    'description' => lang("people::people-setting-description"),
    'settings' => array(
        'enable-gender-filter' => array(
            'type' => 'boolean',
            'title' => lang('people::enable-gender-filter'),
            'description' => lang('people::enable-gender-filter-desc'),
            'value' => true
        ),
        'enable-age-filter' => array(
            'type' => 'boolean',
            'title' => lang('people::enable-age-filter'),
            'description' => lang('people::enable-age-filter-desc'),
            'value' => true
        ),
        'enable-country-filter' => array(
            'type' => 'boolean',
            'title' => lang('people::enable-country-filter'),
            'description' => lang('people::enable-country-filter-desc'),
            'value' => true
        ),
        'enable-online-filter' => array(
            'type' => 'boolean',
            'title' => lang('people::enable-online-filter'),
            'description' => lang('people::enable-gender-filter-desc'),
            'value' => true
        ),
        'enable-feature-filter' => array(
            'type' => 'boolean',
            'title' => lang('people::enable-feature-filter'),
            'description' => lang('people::enable-gender-filter-desc'),
            'value' => true
        ),
        'default-people-list-type' => array(
            'type' => 'selection',
            'title' => lang('people::default-people-list-type'),
            'description' => lang('people::default-people-list-type-desc'),
            'value' => 'list',
            'data' => array(
                'list' => 'List',
                'grid' => 'Grid'
            )
        ),
        'max-page-result' => array(
            'type' => 'text',
            'title' => lang('people::max-page-result'),
            'description' => lang('people::max-page-result-desc'),
            'value' => '20'
        ),
        'featured-badge-bg-color' => array(
            'title' => lang('people::featured-badge-bg-color'),
            'description' => lang('people::featured-badge-bg-color-desc'),
            'type' => 'text',
            'value' => 'rgba(255, 0, 0, 0.5)'
        ),

        'featured-badge-text-color' => array(
            'title' => lang('people::featured-badge-text-color'),
            'description' => lang('people::featured-badge-text-color-desc'),
            'type' => 'text',
            'value' => '#FFCCCC'
        ),
        'people-dashboard-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('people::show-people-dashboard-link'),
            'description' => lang('people::show-people-dashboard-link-desc'),
            'value' => false
        ),
        'people-explorer-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('people::show-people-explorer-link'),
            'description' => lang('people::show-people-explorer-link-desc'),
            'value' => true
        ),
        'people-footer-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('people::show-people-footer-link'),
            'description' => lang('people::show-people-footer-link-desc'),
            'value' => false
        ),
    )
);