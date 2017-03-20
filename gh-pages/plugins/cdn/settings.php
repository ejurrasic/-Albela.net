<?php
return array(
    'title' => 'CDN Storage',
    'description' => '',
    'settings' => array(
        'cdn-process-uploads' => array(
            'type' => 'boolean',
            'title' => lang('cdn::process-uploads'),
            'description'=> lang('cdn::process-uploads-desc'),
            'value' => 1,
        ),

        'cdn-keep-files' => array(
            'type' => 'boolean',
            'title' => lang('cdn::keep-files'),
            'description'=> lang('cdn::keep-files-desc'),
            'value' => 1,
        ),

    )
);
 