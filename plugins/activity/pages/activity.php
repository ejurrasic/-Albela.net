<?php
function load_pager($app){
    CSRFProtection::validate(false);
    return view("activity::load", array("activities" => getActivities()));
}

function lists_pager($app) {
    $app->setTitle(lang('activity::activity-log'));
    return $app->render(view("activity::lists", array("activities" => getActivities())));
}
 