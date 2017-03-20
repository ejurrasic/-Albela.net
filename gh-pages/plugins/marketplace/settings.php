<?php
return array(
    'title' => lang('marketplace::marketplace'),
    'description' => lang("marketplace::marketplace-setting-description"),
    'settings' => array(
        'pagination-limit-listings' => array(
            'type' => 'text',
            'title' => lang('marketplace::pagination-limit-listings'),
            'description' => lang('marketplace::pagination-limit-listings-desc'),
            'value' => '20'
        ),
        'pagination-limit-comments' => array(
            'type' => 'text',
            'title' => lang('marketplace::pagination-limit-comments'),
            'description' => lang('marketplace::pagination-limit-comments-desc'),
            'value' => '4'
        ),
        'default-approval' => array(
            'type' => 'selection',
            'title' => lang('marketplace::default-approval'),
            'description' => lang('marketplace::default-approval-desc'),
            'value' => '1',
            'data' => array(
                '0' => lang('marketplace::approve'),
                '1' => lang('marketplace::dont-approve')
			)
		),
        'max-num-listing-photos' => array(
            'type' => 'text',
            'title' => lang('marketplace::max-num-listing-photos'),
            'description' => lang('marketplace::max-num-listing-photos-desc'),
            'value' => '5'
        ),

        'listing-truncate-comment' => array(
            'title' => lang('marketplace::listing-truncate-comment'),
            'description' => lang('marketplace::listing-truncate-comment-desc'),
            'type' => 'boolean',
            'value' => 0
        ),
		
        'listing-length-comment' => array(
            'title' => lang('marketplace::listing-length-comment'),
            'description' => lang('marketplace::listing-length-comment-desc'),
            'type' => 'text',
            'value' => 150
        ),
		
        'currency' => array(
            'title' => lang('marketplace::currency'),
            'description' => lang('marketplace::currency-desc'),
            'type' => 'text',
            'value' => '$'
        ),

        'featured-badge-bg-color' => array(
            'title' => lang('marketplace::featured-badge-bg-color'),
            'description' => lang('marketplace::featured-badge-bg-color-desc'),
            'type' => 'text',
            'value' => 'rgba(255, 0, 0, 0.5)'
        ),

        'featured-badge-text-color' => array(
            'title' => lang('marketplace::featured-badge-text-color'),
            'description' => lang('marketplace::featured-badge-text-color-desc'),
            'type' => 'text',
            'value' => '#FFCCCC'
        )
    )
);