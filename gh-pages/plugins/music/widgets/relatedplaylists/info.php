<?php
return array(
    'title' => lang('music::related-playlists'),
    'description' => lang('music::related-playlists-desc'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);