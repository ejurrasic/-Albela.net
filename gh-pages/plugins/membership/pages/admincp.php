<?php
get_menu("admin-menu", "admin-users")->setActive(true);
function suscribers_pager($app) {
    $app->setTitle(lang('membership::suscribers'));
    return $app->render(view('membership::suscribers', array('users' => get_membership_suscribers())));
}



function plans_pager($app) {
    $app->setTitle(lang('membership::membership-plans'));
    return $app->render(view('membership::plans', array("plans" => get_membership_plans())));
}

function add_plans_pager($app) {
    $app->setTitle(lang('membership::membership-plans'));
    $message = null;
    $val = input('val');

    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'role' => 'required',
            'type' => 'required'
        ));

        if (validation_passes()) {
            add_membership_plan($val);
            redirect(url("admincp/membership/plans"));
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view('membership::add-plan', array('message' => $message)));
}

function manage_plans_pager($app) {
    $app->setTitle(lang('membership::membership-plans'));
    $message = null;
    $action = input('action');
    $id = input('id');
    switch($action) {
        case 'delete' :
            delete_membership_plan($id);
            redirect(url("admincp/membership/plans"));
            break;
        case 'edit':
            $val = input('val');
            $plan = get_membership_plan($id);
            if (!$plan) redirect(url("admincp/membership/plans"));
            if ($val) {
		CSRFProtection::validate();
                $validator = validator($val, array(
                    'role' => 'required',
                    'type' => 'required'
                ));

                if (validation_passes()) {
                    save_membership_plan($val, $plan);
                    redirect(url("admincp/membership/plans"));
                } else {
                    $message = validation_first();
                }
            }
            return $app->render(view('membership::edit-plan', array('message' => $message, 'plan' => $plan)));
            break;
    }

}