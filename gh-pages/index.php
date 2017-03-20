<?php
session_start();
error_reporting(-1);
//ini_set('display_errors', 'Off');
date_default_timezone_set('UTC');
//include the functions file
include "includes/functions.php";

//register error handler
set_error_handler("php_error_handler");
set_exception_handler("php_exception_handler");
register_shutdown_function("php_fatal_error_handler");

define('ONE_COLUMN_LAYOUT', 1);
define('TWO_COLUMN_RIGHT_LAYOUT', 2);
define('TWO_COLUMN_LEFT_LAYOUT', 3);
define('THREE_COLUMN_LAYOUT', 4);
define('TOP_ONE_COLUMN_LAYOUT', 5);
define('TOP_TWO_COLUMN_RIGHT_LAYOUT', 6);
define('TOP_TWO_COLUMN_LEFT_LAYOUT', 7);
define('TOP_THREE_COLUMN_LAYOUT', 8);
define('BOTTOM_ONE_COLUMN_LAYOUT', 9);
define('BOTTOM_TWO_COLUMN_RIGHT_LAYOUT', 10);
define('BOTTOM_TWO_COLUMN_LEFT_LAYOUT', 11);
define('BOTTOM_THREE_COLUMN_LAYOUT', 12);
define( 'TOP_NO_CONTAINER_ONE_COLUMN_LAYOUT', 13);
define('TOP_NO_CONTAINER_TWO_COLUMN_RIGHT_LAYOUT', 14);
define('TOP_NO_CONTAINER_TWO_COLUMN_LEFT_LAYOUT', 15);
define('TOP_NO_CONTAINER_THREE_COLUMN_LAYOUT', 16);
//echo $game;
/**
 * Include our Application class
 */
require "includes/core.php";
$app = App::getInstance();


/**
 * Useful constants definitions
 */
define("BASE_PATH", $app->path());
define("BASE_URL", $app->url());

$app->run();

