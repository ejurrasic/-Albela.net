<?php
return array(
    'title' => lang('people::latest-members'),
    'description' => lang('people::latest-members-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('people::display-limit'),
            'description' => lang('people::display-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);