<?php
return array(
    'title' => lang('music::featured-musics'),
    'description' => lang('music::featured-playlists-desc'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-playlists'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);