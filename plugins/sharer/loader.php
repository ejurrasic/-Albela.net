<?php
load_functions('sharer::sharer');

register_asset("sharer::css/sharer.css");

register_hook("footer", function() {
    echo view("sharer::share_site");
});

register_pager("sharer", array(
    'as' => 'sharer',
    'use' => 'sharer::sharer@sharer_pager'
    )
);