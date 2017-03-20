<?php
return array(
    'title' => lang('users'),
    'description' => lang('user-settings-description'),
    'settings' => array(
        'design-profile' => array(
            'type' => 'boolean',
            'title' => lang('enable-profile-design'),
            'description'=> lang('enable-profile-design-desc'),
            'value' => 1,
        ),
        'enable-last-seen' => array(
            'type' => 'boolean',
            'title' => lang('enable-profile-lastseen'),
            'description'=> '',
            'value' => 1,
        ),
        'request-verification' => array(
            'type' => 'boolean',
            'title' => lang('verification-request'),
            'description'=> lang('verification-request-desc'),
            'value' => 1,
        ),

        'default-profile-privacy' => array(
            'type' => 'selection',
            'title' => lang('default-profile-privacy'),
            'description'=> lang('default-profile-privacy-desc'),
            'value' => 1,
            'data' => array(
                '1' => lang('everyone'),
                '2' => lang('friends-followers')
            )
        ),
        'default-birthdate-privacy' => array(
            'type' => 'selection',
            'title' => lang('default-birthdate-privacy'),
            'description'=> lang('default-birthdate-privacy-desc'),
            'value' => 1,
            'data' => array(
                '1' => lang('everyone'),
                '2' => lang('friends-followers')
            )
        ),
        'enable-birthdate' => array(
            'type' => 'boolean',
            'title' => lang('enable-birthdate'),
            'description' => lang('enable-birthdate-desc'),
            'value' => 1
        ),

        'birthdate-min-age' => array(
            'type' => 'text',
            'title' => lang('birthdate-minimum-age'),
            'description' => lang('birthdate-minimum-age-desc'),
            'value' => '10'
        ),

        'login-trial-enabled' => array(
            'type' => 'boolean',
            'title' => lang('enable-login-trial'),
            'description' => lang('enable-login-trial-desc'),
            'value' => '1',
        ),

        'login-trials-limit' => array(
            'type' => 'selection',
            'title' => lang('login-trial-limit'),
            'description' => lang('login-trial-limit-desc'),
            'value' => '5',
            'data' => array('5' => '5 Times', '10' => '10 Times', '15' => '15 Times', '20' => '20 Times')
        ),

        'login-trial-wait-time' => array(
            'type' => 'selection',
            'title' => lang('login-trial-wait-time'),
            'description' => lang('login-trial-wait-time'),
            'value' => '15',
            'data' => array(
                '5' => '5 '.lang('minutes'),
                '10' => '10 '.lang('minutes'),
                '15' => '15 '.lang('minutes'),
                '20' => '20 '.lang('minutes'),
                '30' => '30 '.lang('minutes')
            )
        ),

        'allow-change-email' => array(
            'type' => 'boolean',
            'title' => lang('allow-change-email'),
            'description' => lang('allow-change-email-desc'),
            'value' => '1',
        ),


        'allow-change-username' => array(
            'type' => 'boolean',
            'title' => lang('allow-change-username'),
            'description' => lang('allow-change-username-desc'),
            'value' => '1',
        ),

        'loose-verify-badge-username' => array(
            'type' => 'boolean',
            'title' => lang('remove-verify-badge-change-username'),
            'description' => lang('remove-verify-badge-change-username-desc'),
            'value' => '1',
        ),

    )
);
 