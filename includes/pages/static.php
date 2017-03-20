<?php
function render_pager($app) {
    //$app->setLayout('layouts/blank');
    $slug = segment(0);
    $page = get_static_page($slug);

    $app->setTitle(lang($page['title']))->setKeywords($page['tags'])->setDescription(str_limit($page['content'], 100));
    return $app->render(view('static/render', array('page' => $page)));
}