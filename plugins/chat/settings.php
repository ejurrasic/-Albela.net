<?php
return array(
    'title' => 'Chat Plugin',
    'description' => lang("comment::comment-setting-description"),
    'settings' => array(
        'default-send-message-privacy' => array(
            'type' => 'selection',
            'title' => lang('chat::default-send-message-privacy'),
            'description'=> lang('chat::default-send-message-privacy-desc'),
            'value' => 1,
            'data' => array(
                '1' => lang('everyone'),
                '2' => lang('friends-followers')
            )
        ),
    )
);
 