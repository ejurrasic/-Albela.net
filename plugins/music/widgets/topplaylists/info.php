<?php
return array(
    'title' => lang('music::top-playlists'),
    'description' => lang('music::top-musics-playlists'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);