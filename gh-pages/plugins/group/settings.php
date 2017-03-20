<?php
return array (
    'title' => 'Group Plugin',
    'description' => lang("group::group-setting-description"),
    'settings' => array (
        'enable-group-posts-in-timeline' => array (
            'title' => lang('group::enable-group-posts-in-timeline'),
            'description' => lang('group::enable-group-posts-in-timeline-desc'),
            'type' => 'boolean',
            'value' => 1
        ),
        'default-group-list-type' => array (
            'type' => 'selection',
            'title' => lang('group::default-group-list-type'),
            'description' => lang('group::default-group-list-type-desc'),
            'value' => 'list',
            'data' => array (
                'list' => lang('group::list'),
                'grid' => lang('group::grid')
            )
        )
    )
);
 