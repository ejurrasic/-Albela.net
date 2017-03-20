<?php
function notification_upgrade_database() {
    register_site_page("notifications", array('title' => 'notifications-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(2233348584330005,'notifications','content', 'middle');

    });
}