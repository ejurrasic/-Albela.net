<?php
function play_video_pager($app) {
    $link = input('link');
    return view("video/embed", array('link' => video_url($link)));
}
 