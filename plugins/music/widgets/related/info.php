<?php
return array(
    'title' => lang('music::related-musics'),
    'description' => lang('music::related-music-desc'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);