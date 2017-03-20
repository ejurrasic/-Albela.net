<?php
return array(
    'title' => lang('game::featured-games'),
    'description' => lang('game::featured-games-desc'),
    'settings' => array(
        'limit' => array(
            'title' => lang('game::display-limit'),
            'description' => lang('game::display-limit-desc'),
            'type' => 'text',
            'value' => 6
        )
    )
);