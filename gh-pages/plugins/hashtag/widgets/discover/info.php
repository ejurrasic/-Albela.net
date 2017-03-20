<?php
return array(
    'title' => lang('hashtag::discover'),
    'description' => 'Displays top hashtags',
    'settings' => array(
        'limit' => array(
            'title' => lang('hashtag::trending-hashtag-limit'),
            'description' => lang('hashtag::trending-hashtag-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);