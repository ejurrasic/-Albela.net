<?php
function run_pager($app) {
    $key = input('key');
    if ($key != config('tasks-run-key', 'runaccesskey')) return false;
    return TaskManager::run();
}