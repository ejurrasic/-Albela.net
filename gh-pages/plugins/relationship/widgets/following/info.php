<?php
return array(
    'title' => 'Profile Following',
    'description' => 'Shows profile owner following members',
    'settings' => array(
        'limit' => array(
            'title' => lang('relationship::user-list-limit'),
            'description' => lang('relationship::user-list-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);