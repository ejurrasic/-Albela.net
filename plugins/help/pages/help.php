<?php
function help_pager($app) {
    $app->setTitle(lang('help::helps'))->setlayout('layouts/blank');
    $content = config('help-introduction');
    $term = input('term');
    if ($term) {
        $content = view('help::search', array('helps' => get_helps($term)));
    }
    return $app->render(view('help::layout', array('content' => $content)));
}

function help_page_pager($app) {
    $category = segment(1);
    $help = segment(2);
    $category = get_help($category);
    if (!$category) return redirect(url('helps'));
    if ($help) {
        $help = get_help($help, $category['id']);
    } else {
        $help = $category;
    }

    $app->setTitle(lang('help::helps').' - '. $help['title'])
        ->setKeywords($help['tags'])
        ->setDescription(sanitizeText(str_limit($help['content'], 100)))
        ->setlayout('layouts/blank');

    return $app->render(view('help::layout', array('content' => $help['content'])));
}