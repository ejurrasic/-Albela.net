<?php
get_menu("admin-menu", "cms")->setActive();
function lists_pager($app) {
    $app->setTitle(lang('report::reports'));
    return $app->render(view('report::list', array('reports' => get_reports())));
}

function delete_report_pager($app) {
    $id = input('id');
    delete_report($id);
    redirect_back();
}
 