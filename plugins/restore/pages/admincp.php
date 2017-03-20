<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("restore-manager")->setActive();

function restore_pager($app) {
    $app->setTitle(lang('restore::restore-title'));
    return $app->render(view('restore::admincp/lists', array()));
}