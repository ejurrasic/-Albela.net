<?php
return array(
    'title' => 'Music',
    'description' => lang('music::music-plugin setting'),
    'settings' => array(
/*        'external-music' => array(
            'type' => 'boolean',
            'title' => lang('music::enable-external-music'),
            'description' => lang('music::enable-external-music-desc'),
            'value' => false
        ),*/
        'default-music-list-type' => array(
            'type' => 'selection',
            'title' => lang('music::default-music-list-type'),
            'description' => lang('music::default-music-list-type-desc'),
            'value' => 'list',
            'data' => array(
                'list' => lang('music::list'),
                'grid' => lang('music::grid')
            )
        ),
        'music-list-limit' => array(
            'type' => 'text',
            'title' => lang('music::music-limit-per-page'),
            'description' => lang('music::music-limit-per-page-desc'),
            'value' => 10
        ),
        'default-music-privacy' => array(
            'type' => 'selection',
            'title' => lang('music::default-music-privacy'),
            'description' => lang('music::default-music-privacy-desc'),
            'value' => 1,
            'data' => array(
                '1' => lang('music::public'),
                '2' => lang('music::user-connections')
            )
        ),
        'enable-music-download' => array(
            'type' => 'boolean',
            'title' => lang('music::enable-music-download'),
            'description' => lang('music::enable-music-download-desc'),
            'value' => false
        )
    )
);
 