<?php
return array(
    'title' => lang('music::profile-recent-musics'),
    'description' => lang('music::profile-recent-musics-desc'),
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => lang('music::number-music-display'),
            'description' => lang('music::number-music-display-desc'),
            'value' => 3
        )
    )
);