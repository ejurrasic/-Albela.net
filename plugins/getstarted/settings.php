<?php
return array(
    'title' => 'Getstarted Plugin',
    'description' => lang("getstarted::getstarted-setting-description"),
    'settings' => array(
        'enable-getstarted' => array(
            'title' => lang('getstarted::enable-getstarted'),
            'description' => lang('getstarted::enable-getstarted-desc'),
            'type' => 'boolean',
            'value' => 1,
        )
    )
);
 