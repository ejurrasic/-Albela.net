<?php
return array(
    'title' => lang('marketplace::latest-listings'),
    'description' => lang('marketplace::latest-listings-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('marketplace::listings-block-limit'),
            'description' => lang('marketplace::listings-block-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);