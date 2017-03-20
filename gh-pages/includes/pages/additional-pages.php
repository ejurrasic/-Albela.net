<?php
function additional_pager($app)
{
    $pagetitle = (string) segment(0);
    $pagetype  = $pagetitle;
    load_functions("admin_additional_pages");
    $detail = getPageContent($pagetype);

    $app->setLayout('additional-pages/layout', array('detail' => $detail))->setTitle(lang($pagetype));

    return $app->render(view("additional-pages/main", array('page' => $pagetype,'detail' => $detail)));
}