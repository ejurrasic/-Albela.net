<?php
return array(
    'title' => lang('game::latest-games'),
    'description' => lang('game::top-games-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('game::display-limit'),
            'description' => lang('game::display-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);