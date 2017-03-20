<?php
return array(
    'title' => 'Mention',
    'description' => "",
    'settings' => array(
        'mention-color' => array(
            'title' => 'Mention Color',
            'description' => 'Default : #3498db',
            'type' => 'text',
            'value' => '#3498db'
        ),

        'mention-title' => array(
            'title' => 'Mention Title',
            'description' =>'Use username or user fullname',
            'type' => 'selection',
            'value' => '2',
            'data' => array(
                '1' => 'Username',
                '2' => 'Full Name'
            )
        )
    )
);
 