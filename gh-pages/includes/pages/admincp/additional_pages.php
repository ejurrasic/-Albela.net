<?php
get_menu("admin-menu", "cms")->setActive()->findMenu("static-pages")->setActive();
function additional_pager($app)
{
    $pagetitle = (string) segment(2);
    $pagetype  = $pagetitle;

    /*request is a post request*/
    if(get_request_method() == 'POST' && !empty($_POST))
    {
        $response = $_POST;
        unset($_POST);

        load_functions("admin_additional_pages");
        updatePageContent($response,$pagetype);
    }

    $app->setTitle(lang('manage-static-pages'));

    load_functions("admin_additional_pages");

    $content = getPageContent($pagetype);

    if($pagetype == "contact") return $app->render(view("additional-pages/contact", array('page' => $pagetype,'content' => $content)));
    return $app->render(view("additional-pages/main", array('page' => $pagetype,'content' => $content)));
}