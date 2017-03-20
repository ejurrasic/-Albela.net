<?php
function lastvisitor_pager($app) {
    return $app->render(view('lastvisitor::lastvisitor', array(
        'title' => lang('lastvisitor::profileheadings')
    )));
}