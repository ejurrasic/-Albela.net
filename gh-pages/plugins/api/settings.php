<?php
return array(
    'title' => 'API',
    'description' => "API settings",
    'settings' => array(
        'api-key' => array(
            'type' => 'text',
            'title' => "API Key",
            'description'=> "API key make request from api secure",
            'value' =>"normalKey",
        ),

        'google-fcm-api-key' => array(
            'type' => 'text',
            'title' => 'Google FCM Api Key',
            'description' => 'Provide your google FCM api key here',
            'value' => 'AIzaSyD7JB4TT0bpOV7ZR87Kdj93cNGGVmVwyWs'
        )

    )
);
 