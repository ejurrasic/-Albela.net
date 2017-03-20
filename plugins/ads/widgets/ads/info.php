<?php
return array(
    'title' => 'Side Ads',
    'description' => 'Display ads',
    'settings' => array(
        'type' => array(
            'title' => lang('ads::ads-type'),
            'description' => lang('ads::ads-type-description'),
            'type' => 'selection',
            'value' => 'all',
            'data' => array(
                'all' => lang('all'),
                'page' => lang('ads::page'),
                'website' => lang('ads::website')
            )
        ),
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 2
        ),
    )
);