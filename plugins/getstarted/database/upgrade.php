<?php
function getstarted_upgrade_database() {
    register_site_page("signup-welcome", array('title' => 'getstarted', 'column_type' => THREE_COLUMN_LAYOUT ), function() {
        Widget::add(2233348585005,'signup-welcome','content', 'middle');
    });
}