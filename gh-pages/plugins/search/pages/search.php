<?php
load_functions("search::search");
function dropdown_search_pager($app) {
    CSRFProtection::validate(false);
    $term = input('term');
    $term = sanitizeText($term);
    //exit($term);
    $content = "<span></span>";

    $content = fire_hook("search-dropdown-start", $content, array($term));

    $content .= view("search::dropdown", array('users' => search_users($term)));
    $content = fire_hook("search-dropdown-end", $content, array($term));
    return $content;
}

function search_pager($app) {
    $term = input("term");
    $term = sanitizeText($term);
    $type = input('type', 'all');
    $type = sanitizeText($type);
    $content = "<span></span>";
    $app->setTitle(lang("search::search-result-for").' '.$term);
    register_search_menu_items($term);

    if ($type == 'user') {
        get_menu('search-menu', 'user')->setActive();
        $content = view("search::users-result", array('users' => search_users($term), 'term' => $term));
    } elseif ($type == "post") {
        get_menu('search-menu', 'posts')->setActive();
        $content = view("search::post-results", array('feeds' => get_feeds('search', $term), 'term' => $term));
    } elseif( $type == 'all') {
        get_menu('search-menu', 'all')->setActive();
        $content = "<span></span>";

        $content .= view("search::top", array('users' => search_users($term, 3), 'feeds' => get_feeds('search', $term), 'term' => $term));
        $content = fire_hook("search-top-start", $content, array($term));


        $content = fire_hook("search-top-end", $content, array($term));

    }
    $content = fire_hook("search-result", $content, array($term, $type));
    return $app->render(view("search::layout", array('content' => $content)));
}

function register_search_menu_items($term) {
    add_menu("search-menu", array('title' => lang('search::all'), 'id' => 'all', 'link' => form_search_link('all', $term)));
    add_menu("search-menu", array('title' => lang('search::users'), 'id' => 'user', 'link' => form_search_link('user', $term)));
    add_menu("search-menu", array('title' => lang('search::posts'), 'id' => 'posts', 'link' => form_search_link('post', $term)));
    fire_hook("register-search-menu", null, array($term));
}

function form_search_link($type = "user",  $term = null) {
    $link = url_to_pager("search")."?type=".$type;
    if ($term) $link .= "&term=".$term;
    return $link;
}
 