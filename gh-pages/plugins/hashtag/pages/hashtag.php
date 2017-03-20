<?php
load_functions("hashtag::hashtag");

function hashtags_pager($app) {
    $app->topMenu = lang('discover');
    $hashtag = input('t');
    $app->setTitle('#'.lang('discover'));

    return $app->render(view('hashtag::discover', array('feeds' => get_feeds('hashtag', $hashtag))));
}