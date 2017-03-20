<?php
return array(
    'title' => 'Comment Plugin',
    'description' => lang("comment::comment-setting-description"),
    'settings' => array(
        'enable-comment-replies' => array(
            'title' => lang('comment::enable-comment-replies'),
            'description' => lang('comment::enable-comment-replies-desc'),
            'type' => 'boolean',
            'value' => 1
        ),
        'comment-replies-limit' => array(
            'title' => lang('comment::comment-replies-limit'),
            'description' => lang('comment::comment-replies-limit-desc'),
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



    )
);
 