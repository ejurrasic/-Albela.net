<?php
return array(
    'title' => lang('registration'),
    'description' => lang('registration-settings-description'),
    'settings' => array(
        'user-signup' => array(
            'type'  => 'boolean',
            'title' => lang('enable-user-registration'),
            'description' => lang('enable-user-registration-desc'),
            'value' => 1
        ),

        'user-activation' => array(
            'type' => 'boolean',
            'title' => lang('enable-user-account-activation'),
            'description' => lang('enable-user-account-activation-desc'),
            'value' => 0
        ),

        'auto-follow-users' => array(
            'type' => 'textarea',
            'title' => lang('add-auto-follow-accounts'),
            'description' => lang('add-auto-follow-accounts-desc'),
            'value' => ''
        )
    )
);
 