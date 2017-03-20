<?php
function membership_upgrade_database() {
    register_site_page("membership-choose-plan", array('title' => 'membership::membership-choose-plan', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'membership-choose-plan', 'content', 'middle');
        Widget::add(null, 'profile', 'plugin::membership|membership', 'right');
    });
    register_site_page("membership-payment", array('title' => 'membership::membership-payment', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'membership-payment', 'content', 'middle');
    });
    register_site_page("membership-paypal", array('title' => 'membership::membership-paypal', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'membership-paypal', 'content', 'middle');
    });
    register_site_page("membership-stripe", array('title' => 'membership::membership-stripe', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'membership-stripe', 'content', 'middle');
    });
}