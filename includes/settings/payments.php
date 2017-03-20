<?php
return array(
    'title' => lang('payments'),
    'description' => lang('payments-settings-description'),
    'settings' => array(
        'default-currency' => array(
            'type' => 'selection',
            'title' => lang('default-currency'),
            'description'=> lang('default-currency-desc'),
            'value' => 'USD',
            'data' => array(
                'USD' => 'US Dollar',
                'AUD' => 'Australian Dollar',
                'BRL' => 'Brazilian Real',
                'CAD' => 'Canadian Dollar',
                'CHF' => 'Swiss Franc',
                'DKK' => 'Danish Krone',
                'EUR' => 'Euro',
                'GBP' => 'British Pound Sterling',
                'HKD' => 'Hong Kong Dollar',
                'JPY' => 'Japanese Yen',
                'MXN' => 'Mexican Peso',
                'NOK' => 'Norwegian Krone',
                'NZD' => 'New Zealand Dollar',
                'RUB' => 'Russian Ruble',
                'SEK' => 'Swedish Krona',
                'TRY' => 'Turkish Lira'
            )
        ),

        'enable-paypal' => array(
            'type' => 'boolean',
            'title' => lang('enable-paypal'),
            'description' => lang('enable-paypal-desc'),
            'value' => 0,
        ),

        'enable-paypal-sandbox' => array(
            'type' => 'boolean',
            'title' => lang('enable-paypal-sandbox'),
            'description' => lang('enable-paypal-sandbox-desc'),
            'value' => 1,
        ),

        'paypal-corporate-email' => array(
            'type' => 'text',
            'title' => lang('paypal-corporate-email'),
            'description' => lang('paypal-corporate-email-desc'),
            'value' => '',
        ),

        'paypal-notification-email' => array(
            'type' => 'text',
            'title' => lang('paypal-notification-email'),
            'description' => lang('paypal-notification-email-desc'),
            'value' => '',
        ),

        'enable-stripe' => array(
            'type' => 'boolean',
            'title' => lang('enable-stripe'),
            'description' => lang('enable-stripe-desc'),
            'value' => 0,
        ),

        'stripe-secret-key' => array(
            'type' => 'text',
            'title' => lang('stripe-secret-key'),
            'description' => lang('stripe-secret-key-desc'),
            'value' => '',
        ),

        'stripe-publishable-key' => array(
            'type' => 'text',
            'title' => lang('stripe-publishable-key'),
            'description' => lang('stripe-publishable-key-desc'),
            'value' => '',
        ),

    )
);
 