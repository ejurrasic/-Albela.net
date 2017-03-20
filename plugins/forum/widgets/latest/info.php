<?php
return array(
    'title' => lang('forum::latest-topic'),
    'description' => lang('forum::latest-threads-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('forum::threads-block-limit'),
            'description' => lang('forum::threads-block-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),
        'str_limit' => array(
            'title' => lang('forum::string-limit'),
            'description' => lang('forum::string-limit-desc'),
            'type' => 'text',
            'value' => 36
        )
    )
);