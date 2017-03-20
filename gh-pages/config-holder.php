<?php
return array(
    /**
     * Option to know if debug is enabled or not
     */
    'debug' => true,

    /**
     * Provide your MYSQL database credentials
     */
    'mysql_host' => '{localhost}',
    'mysql_user' => '{root}',
    'mysql_db_name' => '{dbname}',
    'mysql_password' => '{dbpassword}',

    /**
     * Default language for your application
     */
    'default_language' => "english",

    /**
     * Fallback language in case the translation does not exists
     */
    'fallback_language' => "english",

    /**
     * Application base path
     */
    'base_path' => __DIR__.'/',

    'type' => 'full',

    /**
     * Option to enable https
     */
    'https' => false,

    /**
     * Storage dir
     */
    'storage_dir' => __DIR__.'/storage/',

    /**
     * Plugins directory
     */
    'plugins_dir' => __DIR__.'/plugins/',

    /**
     * Plugins folder name
     */
    'plugins_folder' => 'plugins',

    /**
     * Themes directory
     */
    'themes_dir' => __DIR__.'/themes/',
    'themes_folder' => 'themes',

    /**
     * Enable bcrpyt for password hashing or other hashing works
     * Please not bcrypt required php 5.3.7 or above
     */
    'bcrypt' => false,

    /**
     * cookie path
     */
    'cookie_path' => '/',

    /*
     * To know that the system is well installed
     */
    'installed' => '{installed}',

    /**
     * Supported cache driver are : file or memcache
     */
    'cache-driver' => 'file',

    /**
     * Settings for memcache driver
     */
    'memcache-host' => '127.0.0.1',
    'memcache-port' => '11211',

    /**
     * Months
     */
    'months' => array(
    ),
);