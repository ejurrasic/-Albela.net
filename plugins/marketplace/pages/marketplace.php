<?php
function marketplace_pager($app){
    $app->setTitle(lang("marketplace::marketplace"));
    $category_id = input('c') ? input('c') : null;
    $type = input('t') ? input('t') : null;
    $search = input('s') ? input('s') : null;
    $page = input('page') ? input('page') : null;
    $limit = config('pagination-limit-listings', 20);
    $categories = marketplace_get_categories();
    $category = $category_id ? lang(marketplace_get_category($category_id)[0]['title']) : lang('marketplace::all-categories');
    $appends = $_GET;
    unset($appends['page']);
    $listings = marketplace_get_listings($category_id, $search, $type, $page, $limit)->append($appends);
    $message = null;
    return $app->render(view('marketplace::marketplace', array('categories' => $categories, 'category' => $category, 'category_id' => $category_id, 'listings' => $listings, 'search' => $search, 'type' => $type,  'message' => $message)));
}

function marketplace_slug_pager(){
    $path = (isset(parse_url($_SERVER['REQUEST_URI'])['path']) && parse_url($_SERVER['REQUEST_URI'])['path'] != '/') ? parse_url($_SERVER['REQUEST_URI'])['path'] : null;
    if(preg_match('/\/category\/(.*?)(\/|$|\?|#)/i', $path)) {
        preg_match('/\/category\/(.*?)(\/|$|\?|#)/i', $path, $matches);
        $_GET['c'] = marketplace_get_slug_id($matches[1], 'category');
    }
    if(preg_match('/\/my-listings(\/|$|\?|#)/i', $path)) {
        $_GET['t'] = 'm';
    }
    if(preg_match('/\/([0-9]+)(\/|$|\?|#)/i', $path)) {
        preg_match('/\/([0-9]+)(\/|$|\?|#)/i', $path, $matches);
        $_GET['t'] = $matches[1];
    }
    return marketplace_pager(app());
}