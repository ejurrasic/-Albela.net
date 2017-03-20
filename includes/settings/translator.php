<?php
return array(
    'title' => lang('translator'),
    'description' => '',
    'settings' => array(
        'enable-bing-translator' => array(
            'type' => 'boolean',
            'title' => 'Bing Translator',
            'description' => '',
            'value' => 0,
        ),

        'bing-id' => array(
            'type'  => 'text',
            'title' => 'Bing ID',
            'description' => '',
            'value' => ''
        ),

        'bing-secret' => array(
            'type' => 'text',
            'title' => 'Bing Secret',
            'description' => '',
            'value' => ''
        ),
    )
);
 