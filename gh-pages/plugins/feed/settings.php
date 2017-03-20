<?php
return array(
    'title' => 'Feed Plugin',
    'description' => lang("feed::feed-setting-description"),
    'settings' => array(
        'default-feed-privacy' => array(
            'type' => 'selection',
            'title' => lang('feed::default-feed-privacy'),
            'description' => lang('feed::default-feed-privacy-desc'),
            'value' => 1,
            'data' => array(
                '1' => lang('public'),
                '2' => lang('feed::friends-only')
            )
        ),
        'feed-realtime-update' => array(
            'title' => lang('feed::feed-real-time-update'),
            'description' => lang('feed::feed-real-time-update-desc'),
            'type' => 'boolean',
            'value' => 1
        ),

        'feed-user-title' => array(
            'title' => 'User Post Title option',
            'description' =>'Use username or user fullname',
            'type' => 'selection',
            'value' => '2',
            'data' => array(
                '1' => 'Username',
                '2' => 'Full Name'
            )
        ),

        'feed-realtime-update-interval' => array(
            'title' => lang('feed::feed-real-time-update-interval'),
            'description' => lang('feed::feed-real-time-update-interval-desc'),
            'type' => 'selection',
            'value' => 20000,
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

        'feed-limit' => array(
            'title' => lang('feed::feed-limit'),
            'description' => lang('feed::feed-limit-desc'),
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

        'feed-comment-limit' => array(
            'title' => lang('feed::feed-comment-limit'),
            'description' => lang('feed::feed-comment-limit-desc'),
            'type' => 'selection',
            'value' => 3,
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

        'enable-feed-notifications' => array(
            'title' => lang('feed::enable-feed-notifications'),
            'description' => lang('feed::enable-feed-notifications-desc'),
            'type' => 'boolean',
            'value' => 1
        ),

        'allow-feed-editing' => array(
            'title' => lang('feed::allow-feed-editing'),
            'description' => lang('feed::allow-feed-editing-desc'),
            'type' => 'boolean',
            'value' => 1
        ),

        'allow-feed-sharing' => array(
            'title' => lang('feed::allow-feed-sharing'),
            'description' => lang('feed::allow-feed-sharing-desc'),
            'type' => 'boolean',
            'value' => 1
        ),

        'enable-feed-text-limit' => array(
            'title' => lang('feed::enable-feed-text-limit'),
            'description' => lang('feed::enable-feed-text-limit-desc'),
            'type' => 'boolean',
            'value' => 0
        ),

        'maximum-feed-text-limit' => array(
            'title' => lang('feed::maximum-feed-text-limit'),
            'description' => lang('feed::maximum-feed-text-limit-desc'),
            'type' => 'text',
            'value' => 150
        ),

        'maximum-feed-text-display' => array(
            'title' => lang('feed::maximum-feed-text-display'),
            'description' => lang('feed::maximum-feed-text-display-desc'),
            'type' => 'selection',
            'value' => '500',
            'data' => array(
                'all' => lang('feed::all-text'),
                '200' => 200 ,
                '300' => 300,
                '400' => 400,
                '500' => 500,
                '600' => 600,
                '700' => 700,
                '800' => 800,
                '1000' => 1000
            )
        ),

        'enable-feed-poll' => array(
            'title' => lang('feed::enable-feed-poll'),
            'description' => lang('feed::enable-feed-poll'),
            'type' => 'boolean',
            'value' => 1
        ),

        'enable-feed-social-share' => array(
            'title' => lang('feed::enable-feed-social-share'),
            'description' => lang('feed::enable-feed-social-share'),
            'type' => 'boolean',
            'value' => 1
        ),


    )
);
 