<?php
load_functions("membership::membership");


register_asset("membership::js/membership.js");
register_pager("admincp/membership/plans", array('use' => "membership::admincp@plans_pager", 'filter' => 'admin-auth', 'as' => 'admincp-membership-plans'));
register_pager("admincp/membership/invoices", array('use' => "membership::admincp@invoices_pager", 'filter' => 'admin-auth', 'as' => 'admincp-membership-invoices'));
register_pager("admincp/membership/suscribers", array('use' => "membership::admincp@suscribers_pager", 'filter' => 'admin-auth', 'as' => 'admincp-membership-suscribers'));
register_pager("admincp/membership/plans/add", array('use' => "membership::admincp@add_plans_pager", 'filter' => 'admin-auth', 'as' => 'admincp-membership-add-plans'));
register_pager("admincp/membership/plan/manage", array('use' => "membership::admincp@manage_plans_pager", 'filter' => 'admin-auth', 'as' => 'admincp-membership-manage-plans'));

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("membership::css/membership.css");
        if(is_loggedIn() and user_need_membership()) {
            //exit('we are here');
            $firstSegment = segment(0);
            $allowed_segments = array('membership', 'logout');
            $allowed_segments = fire_hook('membership.segment.allowed', $allowed_segments);
            if ($firstSegment and !in_array($firstSegment, $allowed_segments) and !is_admin()) {
                //redirect(url("membership/choose-plan"));
            }
        }
    }
});

register_pager("membership/choose-plan", array("use" => "membership::membership@choose_plan_pager", 'filter' => 'auth', 'as' => 'membership-choose-plan'));
register_pager("membership/payment", array("use" => "membership::membership@payment_pager", 'filter' => 'auth', 'as' => 'membership-payment'));
register_pager("membership/payment/paypal/{id}", array("use" => "membership::membership@payment_paypal_pager", 'filter' => 'auth', 'as' => 'membership-paypal'))->where(array('id' => '[0-9]+'));
register_pager("membership/payment/stripe/{id}", array("use" => "membership::membership@payment_stripe_pager", 'filter' => 'auth', 'as' => 'membership-stripe'))->where(array('id' => '[0-9]+'));
register_hook("admin-started", function() {


    get_menu("admin-menu", "admin-users")->addMenu(lang('membership::membership-plans'), url('admincp/membership/plans'), "plans");
    //get_menu("admin-menu", "admin-users")->addMenu(lang('membership::invoices'), url('admincp/membership/invoices'), "invoices");
    get_menu("admin-menu", "admin-users")->addMenu(lang('membership::suscribers'), url('admincp/membership/suscribers'), "suscribers");

});

register_hook('admin.statistics', function($stats) {
    $stats['suscribers'] = array(
        'count' => count_membership_suscribers(),
        'title' => lang('membership::suscribers'),
        'icon' => 'ion-android-contacts',
        'link' => url("admincp/membership/suscribers"),
    );
    return $stats;
});


register_hook('user.delete', function($userid) {

});


