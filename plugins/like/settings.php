<?php
return array(
    'title' => 'Like Plugin',
    'description' => lang("like::like-setting-description"),
    'settings' => array(
        'feed-like-type' => array(
            'title' => lang('like::like-type'),
            'description' => lang('feed::feed-like-type-desc'),
            'type' => 'selection',
            'value' => 'regular',
            'data' => array(
                'regular' => 'Like System',
                'reaction' => 'Reaction System'
            )
        ),
        'enable-dislike' => array(
            'title' => lang('like::enable-dislike'),
            'description' => lang('like::enable-dislike-desc'),
            'type' => 'boolean',
            'value' => 0,
        ),

        'dislike-notification' => array(
            'title' => lang('like::enable-dislike-notification'),
            'description' => lang('like::enable-dislike-notification-desc'),
            'type' => 'boolean',
            'value' => 0,
        )
    )
);
 