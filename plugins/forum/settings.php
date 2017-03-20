<?php
return array(
    'title' => lang('forum::forum'),
    'description' => lang("forum::forum-setting-description"),
    'settings' => array(
        'pagination-length-forum' => array(
            'type' => 'text',
            'title' => lang('forum::pagination-length-forum'),
            'description' => lang('forum::pagination-length-forum-desc'),
            'value' => '20'
        ),
        'pagination-length-thread' => array(
            'type' => 'text',
            'title' => lang('forum::pagination-length-thread'),
            'description' => lang('forum::pagination-length-thread-desc'),
            'value' => '20'
        ),
        'pagination-length-sub-replies' => array(
            'type' => 'text',
            'title' => lang('forum::pagination-length-sub-replies'),
            'description' => lang('forum::pagination-length-sub-replies-desc'),
            'value' => '4'
        ),
        'forum-sub-replies-order' => array(
            'type' => 'selection',
            'title' => lang('forum::forum-sub-replies-order'),
            'description' => lang('forum::forum-sub-replies-order-desc'),
            'value' => 'DESC',
            'data' => array(
                'ASC' => 'Ascending',
                'DESC' => 'Descending'
            )
        )
    )
);