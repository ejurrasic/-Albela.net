<?php
return array(
    'title' => 'Profile Friends',
    'description' => 'Shows profile owner friends list',
    'settings' => array(
        'limit' => array(
            'title' => lang('relationship::user-list-limit'),
            'description' => lang('relationship::user-list-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);