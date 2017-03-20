<?php
function forum_pager($app){
    $app->setTitle(lang("forum::forum"));
    $val = input('val');
    $category_id = input('c') ? input('c') : null;
    $tag_id = input('t') ? input('t') : null;
    $order = input('o') ? input('o') : null;
    $search = input('s') ? input('s') : null;
    $page = input('page') ? input('page') : null;
    $limit = config('pagination-length-forum', 20);
    $categories = forum_get_categories();
    $category = $category_id ? lang(forum_get_category($category_id)[0]['title']) : lang('forum::all-categories');
    $appends = $_GET;
    unset($appends['page']);
    $current_class = array('l' => '', 'n' => '', 't' => '', 'ft' => '', 'f' => '');
    switch($order) {
        case 'l': $order_title = lang('forum::latest'); $current_class['l'] = ' active'; break;
        case 't': $order_title = lang('forum::top'); $current_class['t'] = ' active'; break;
        case 'ft': $order_title = lang('forum::featured'); $current_class['ft'] = ' active'; break;
        case 'f': $order_title = lang('forum::followed'); $current_class['f'] = ' active'; break;
        default: $order_title = lang('forum::new'); $current_class['n'] = ' active'; break;
    }
    $threads = forum_get_threads($category_id, $tag_id, $search, $order, $page, $limit)->append($appends);
    $tag = $tag_id ? forum_get_tag($tag_id)[0]['title'] : lang('forum::all-tags');
    $tags = forum_get_tags();
    $message = null;
    $url = http_build_query($_GET) == '' ? url_to_pager("forum-slug", array('appends' => '')) : url_to_pager("forum-slug", array('appends' => '')).'?'.http_build_query($_GET);
    $images_path = getBase().'plugins/forum/images/';
    return $app->render(view('forum::forum', array('categories' => $categories, 'tags' => $tags, 'category' => $category, 'tag' => $tag, 'search' => $search, 'url' => $url, 'threads' => $threads, 'images_path' => $images_path, 'current_class' => $current_class, 'order_title' => $order_title, 'message' => $message)));
}


function forum_slug_pager(){
    $path = (isset(parse_url($_SERVER['REQUEST_URI'])['path']) && parse_url($_SERVER['REQUEST_URI'])['path'] != '/') ? parse_url($_SERVER['REQUEST_URI'])['path'] : null;
    if(preg_match('/\/category\/([0-9+])\//i', $path)) {
        preg_match('/\/category\/([0-9+])\//i', $path, $matches);
        $_GET['c'] = $matches[1];
    }
    if(preg_match('/\/tag\/([0-9+])\//i', $path)) {
        preg_match('/\/tag\/([0-9+])\//i', $path, $matches);
        $_GET['t'] = $matches[1];
    }
    if(preg_match('/\/latest(\/|$|\?|#)/i', $path)) {
        $_GET['o'] = 'l';
    }
    if(preg_match('/\/top(\/|$|\?|#)/i', $path)) {
        $_GET['o'] = 't';
    }
    if(preg_match('/\/featured(\/|$|\?|#)/i', $path)) {
        $_GET['o'] = 'ft';
    }
    if(preg_match('/\/followed(\/|$|\?|#)/i', $path)) {
        $_GET['o'] = 'f';
    }
    if(preg_match('/\/new(\/|$|\?|#)/i', $path)) {
        $_GET['o'] = 'n';
    }
    if(preg_match('/\/search\/(.*?)\/?/i', $path)) {
        preg_match('/\/search\/(.*?)\/?/i', $path, $matches);
        $_GET['s'] = $matches[1];
    }
    return forum_pager(app());
}