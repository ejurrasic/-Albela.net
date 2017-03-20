<?php
return array(
    'title' => 'People You May Know',
    'description' => 'Shows people you may know lists',
    'settings' => array(
        'limit' => array(
            'title' => lang('relationship::people-suggestion-limit'),
            'description' => lang('relationship::people-suggestion-limit-desc'),
            'type' => 'text',
            'value' => 5
        )
    )
);