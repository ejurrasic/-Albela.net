<?php
return array(
    'title' => 'Ads',
    'description' => lang("ads::ads-setting-description"),
    'settings' => array(
        'enable-post-inline-ads' => array(
            'type' => 'boolean',
            'title' => lang('ads::enable-post-inline-ads'),
            'description'=> lang('ads::enable-post-inline-ads-desc'),
            'value' => 1,
        ),
        'render-ads-after-post-number' => array(
            'type' => 'text',
            'title' => lang('ads::render-ads-after-post-number'),
            'description'=> lang('ads::render-ads-after-post-number-desc'),
            'value' => 2,
        ),

        'ads-quantity-deduction-per-click' => array(
            'type' => 'text',
            'title' => lang('ads::ads-quantity-deduction-per-click'),
            'description'=> lang('ads::ads-quantity-deduction-per-click-desc'),
            'value' => 5,
        ),

        'ads-quantity-deduction-per-impression' => array(
            'type' => 'text',
            'title' => lang('ads::ads-quantity-deduction-per-impression'),
            'description'=> lang('ads::ads-quantity-deduction-per-impression-desc'),
            'value' => 5,
        )

    )
);
 