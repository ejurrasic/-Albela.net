<?php
return array(
    'title' => lang("general"),
    'description' => lang("general-settings-description"),
    'settings' => array(
        'site_title' => array(
            'type' => 'text',
            'title' => lang('your-site-title'),
            'description' => lang('your-site-title-desc'),
            'value' => 'sociaVIBE',
        ),

        'title_separator' => array(
            'type' => 'text',
            'title' => lang('your-site-title-separator'),
            'description' => lang('your-site-title-separator'),
            'value' => '-',
        ),

        'site-keywords' => array(
            'type' => 'text',
            'title' => lang('site-keywords'),
            'description' => '',
            'value' => '',
        ),

        'site-description' => array(
            'type' => 'text',
            'title' => lang('site-description'),
            'description' => '',
            'value' => '',
        ),

        'debug' => array(
            'type' => 'boolean',
            'title' => lang('enable-debug'),
            'description' => lang('enable-debug-desc'),
            'value' => '0',
        ),

        'shutdown-site' => array(
            'type' => 'boolean',
            'title' => lang('shutdown-site'),
            'description' => lang('shutdown-site-desc'),
            'value' => '0',
        ),

        'shutdown-message' => array(
            'type' => 'textarea',
            'title' => lang('shutdown-message'),
            'description' => lang('shutdown-message-desc'),
            'value' => '',
        ),

        'https' => array(
            'type' => 'boolean',
            'title' => lang('enable-https'),
            'description' => lang('enable-https-desc'),
            'value' => '0',
        ),

        'timezone' => array(
            'type' => 'selection',
            'title' => lang('set-your-timezone'),
            'description' => lang('set-your-timezone-desc'),
            'value' => 'GMT',
            'data' => get_timezones()
        ),


        'pusher-driver' => array(
            'type' => 'selection',
            'title' => lang('pusher-driver'),
            'description' => lang('pusher-driver-desc'),
            'value' => 'ajax',
            'data' => Pusher::getInstance()->lists()
        ),


        'ajax-polling-interval' => array(
            'type' => 'selection',
            'title' => lang('ajax-polling-interval'),
            'description' => lang('ajax-polling-interval-desc'),
            'value' => '5000',
            'data' => array(
                '1000' => '1 '.lang('seconds'),
                '3000' => '3 '.lang('seconds'),
                '5000' => '5 '.lang('seconds'),
                '10000' => '10 '.lang('seconds'),
                '20000' => '20 '.lang('seconds'),
                '30000' => '30 '.lang('seconds'),
                '40000' => '40 '.lang('seconds'),
                '50000' => '50 '.lang('seconds'),
                '60000' => '1 '.lang('minute'),
                '120000' => '2 '.lang('minute'),
                '180000' => '3 '.lang('minute'),
                '240000' => '4 '.lang('minute'),
                '300000' => '5 '.lang('minute')
            )
        ),


        'session_timeout' => array(
            'type' => 'selection',
            'title' => lang('enable-session-timeout'),
            'description' => lang('enable-session-timeout-desc'),
            'value' => 1800,
            'data' => array(
                '0' => 'Disabled',
                '300' => '5 '.lang('minutes'),
                '600' => '10 '.lang('minutes'),
                '900' => '15 '.lang('minutes'),
                '1800' => '30 '.lang('minutes'),
                '3600' => '1 '.lang('hour'),
                '86400' => '24 '.lang('hours')
            )
        ),

        'enable-captcha' => array(
            'type'  => 'selection',
            'title' => lang('enable-captcha'),
            'description' => lang('enable-captcha-desc'),
            'value' => 1,
            'data' => array(
                '1' => 'In-Build Captcha',
                '2' => 'Google reCaptcha',
                '0' => 'None'
            )
        ),

        'recaptcha-key' => array(
            'type' => 'text',
            'title' => 'reCaptcha Key',
            'description' => '',
            'value' => '',
        ),

        'recaptcha-secret' => array(
            'type' => 'text',
            'title' => 'reCaptcha Secret',
            'description' => '',
            'value' => '',
        ),



    )
);
 