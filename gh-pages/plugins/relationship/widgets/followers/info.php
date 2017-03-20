<?php
return array(
    'title' => 'Profile Followers',
    'description' => 'Shows profile owner followers',
    'settings' => array(
        'limit' => array(
            'title' => lang('relationship::user-list-limit'),
            'description' => lang('relationship::user-list-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);