<?php
return array(
    'title' => lang('music::latest-musics'),
        'description' => lang('music::latest-musics-desc'),
        'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);