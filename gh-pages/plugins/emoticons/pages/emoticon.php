<?php
function search_pager($app) {
    CSRFProtection::validate(false);
    $term = input('term');
    $emoticons = find_emoticons($term);
    echo view("emoticons::search", array('emoticons' => $emoticons, 'target' => input('target')));
}

function emoticon_load_pager($app) {
    CSRFProtection::validate(false);
    $target = '#'.input('target');
    $action = input('action', 0);
    echo view('emoticons::load', array('target' => $target, 'action' => $action));
}
 