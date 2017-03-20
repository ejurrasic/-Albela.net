<?php
function sharer_self_url() {
    $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 'https' : strtolower(explode('/', $_SERVER["SERVER_PROTOCOL"])[0]);
    $port = $_SERVER["SERVER_PORT"] == "80" ? '' : ':'.$_SERVER["SERVER_PORT"];
    $url = $protocol.'://'.$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
    return $url;
}