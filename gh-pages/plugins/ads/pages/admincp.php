<?php
get_menu('admin-menu', 'plugins')->setActive()->findMenu("ads-manager")->setActive();

function ads_lists_pager($app){
    $app->setTitle(lang('ads::manage-ads'));
    return $app->render(view('ads::lists'));
}

function ads_create_pager($app) {
    $app->setTitle(lang('ads::create-ads'));
    return $app->render(view('ads::create'));
}

function ads_process_create_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $val = input('val');
    if (!$val) return json_encode($result);

    return  ads_create($val, $result, true);
}

function edit_ads_pager($app) {
    $id = segment(3);
    $ads = find_ads($id);
    if (!$ads) return redirect_to_pager('admin-ads-list');

    $app->setTitle(lang('ads::edit-ads'));
    return $app->render(view('ads::edit', array('ads' => $ads)));
}

function ads_process_save_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $ads = find_ads($id);
    if (!$ads) return json_encode($result);

    $val = input('val');
    if (!$val) return json_encode($result);

    return ads_save($val, $result, $ads, true);
}

function plans_pager($app) {
    $app->setTitle(lang('ads::ads-plan'));
    return $app->render(view('ads::plans/list', array('plans' => get_ads_plans())));
}

function add_plan_pager($app) {
    $app->setTitle(lang('ads::add-plan'));
    $val = input('val');
    $message =  null;
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'price' => 'required',
            'quantity' => 'required'
        ));
        if (validation_passes()) {
            add_ads_plan($val);
            redirect_to_pager('admin-ads-plan');
        } else{
            $message = validation_first();
        }

    }
    return $app->render(view('ads::plans/add', array('message' => $message)));
}

function manage_plans_pager($app) {
    $action = input('action', 'order');

    switch($action) {
        case 'order':
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_ads_plan_order($ids[$i], $i);
            }
             break;
        case 'edit':
            $plan = get_plan(input('id'));
            if (!$plan) return redirect_to_pager('admin-ads-plan');
            $app->setTitle(lang('ads::edit-plan'));
            $message = null;
            $val = input('val');
            if ($val) {
		        CSRFProtection::validate();
                save_ads_plan($val, $plan);
                redirect_to_pager('admin-ads-plan');
            }
            return $app->render(view('ads::plans/edit', array('plan' => $plan, 'message' => $message)));
            break;
        case 'delete':
            delete_ads_plan(input('id'));
            redirect_to_pager('admin-ads-plan');
            break;
    }
}