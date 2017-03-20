<?php
return array(
    'title' => 'Notification Plugin',
    'description' => lang("notification::notification-setting-description"),
    'settings' => array(
        'enable-email-notification' => array(
            'title' => lang('enable-email-notifications'),
            'description' => '',
            'type' => 'boolean',
            'value' => 1
        ),

        'notification-list-limit' => array(
            'title' => 'Notification List Limit',
            'description' => '',
            'type' => 'selection',
            'value' => 10,
            'data' => array(
                '5' => 5,
                '10' => 10,
                '15' => 15,
                '20' => 20,
                '30' => 30,
                '40' => 40,
                '50' => 50
            )
        ),

        'notification-dropdown-list-limit' => array(
            'title' => 'Notification Drop Down List Limit',
            'description' => '',
            'type' => 'selection',
            'value' => 5,
            'data' => array(
                '2' => 2,
                '3' => '3',
                '5' => '5',
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
                '15' => 15,
                '20' => 20
            )
        ),


    )
);
 