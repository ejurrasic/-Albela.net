<?php
return array(
    'title' => lang('people::featured-members'),
    'description' => lang('people::featured-members-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('people::display-limit'),
            'description' => lang('people::display-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);