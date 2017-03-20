<?php
return array(
    'title' => 'Social Integration Plugin',
    'description' => lang("social::social-setting-description"),
    'settings' => array(
        'enable-facebook' => array(
            'type' => 'boolean',
            'title' => lang('social::enable-facebook'),
            'description' => 'Enable Facebook Sign Up, Login and Contact Import',
            'value' => 0
        ),

        'facebook-app-id' => array(
            'type' => 'text',
            'title' => 'Facebook App ID',
            'description' => 'The App ID of your Web Application\'s Facebook App',
            'value' => ''
        ),

        'facebook-secret-key' => array(
            'type' => 'text',
            'title' => 'Facebook App Secret',
            'description' => 'The App Secret of your Web Application\'s Facebook App',
            'value' => ''
        ),

        'enable-twitter' => array(
            'type' => 'boolean',
            'title' => lang('social::enable-twitter'),
            'description' => 'Enable Twitter Sign Up and Login',
            'value' => 0
        ),

        'twitter-app-id' => array(
            'type' => 'text',
            'title' => 'Twitter App ID',
            'description' => 'The App ID of your Web Application\'s Twitter App',
            'value' => ''
        ),

        'twitter-secret-key' => array(
            'type' => 'text',
            'title' => 'Twitter App Secret',
            'description' => 'The App Secret of your Web Application\'s Twitter App',
            'value' => ''
        ),

        'enable-vk' => array(
            'type' => 'boolean',
            'title' => lang('social::enable-vk'),
            'description' => 'Enable VK Sign Up and Login',
            'value' => 0
        ),

        'vk-app-id' => array(
            'type' => 'text',
            'title' => 'VK App ID',
            'description' => 'The App ID of your Web Application\'s VK App',
            'value' => ''
        ),

        'vk-secret-key' => array(
            'type' => 'text',
            'title' => 'VK App Secret',
            'description' => 'The App ID of your Web Application\'s VK App',
            'value' => ''
        ),

        'enable-googleplus' => array(
            'type' => 'boolean',
            'title' => lang('social::enable-g+'),
            'description' => 'Enable Google Plus Sign Up and Login',
            'value' => 0
        ),

        'google-api-key' => array(
            'type' => 'text',
            'title' => 'Google API Key',
            'description' => 'The Google Browser Key or Server Key of your Web Application',
            'value' => 'AIzaSyCekO3PhGgE-H9yOO4z-o0q0aOmm4M0JEA'
        ),

        'google-oauth-client-id' => array(
            'type' => 'text',
            'title' => 'Google OAuth Client ID',
            'description' => 'The Google OAuth Client ID of your Web Application',
            'value' => ''
        ),

        'google-oauth-client-secret' => array(
            'type' => 'text',
            'title' => 'Google OAuth Client Secret',
            'description' => 'The Google OAuth Client Secret of your Web Application',
            'value' => ''
        )
    )
);
 