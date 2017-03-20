<?php
return array(
    'title' => lang('music::profile-recent-playlists'),
    'description' => lang('music::profile-recent-playlists-desc'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);