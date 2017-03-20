<?php
return array(
    'title' => 'Page Plugin',
    'description' => '',
    'settings' => array (
        'default-page-list-type' => array (
            'type' => 'selection',
            'title' => lang('page::default-page-list-type'),
            'description' => lang('page::default-page-list-type-desc'),
            'value' => 'list',
            'data' => array (
                'list' => lang('page::list'),
                'grid' => lang('page::grid')
            )
        )
    )
);