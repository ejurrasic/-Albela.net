<?php
function ads_pager($app) {
    $app->setTitle(lang('ads::manage-ads'));
    return ads_render(view('ads::manage'));
}

function create_ads_pager($app) {
    $app->setTitle(lang('ads::create-ads'));
    return ads_render(view('ads::create'), 'create');
}

function process_create_ads_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $val = input('val');
    if (!$val) return json_encode($result);

    return ads_create($val, $result);
}

function edit_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) return redirect_to_pager('ads-manage');

    $app->setTitle(lang('ads::edit-ads'));
    return ads_render(view('ads::edit', array('ads' => $ads)));
}

function process_save_ads_pager($app) {
    $id = input('id');
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) return json_encode($result);

    $val = input('val');
    //var_dump($ads);
    //if ($val) var_dump($val);
    if (!$val) return json_encode($result);

    return ads_save($val, $result, $ads);
}


function activate_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) return redirect_to_pager('ads-manage');
    $app->setTitle(lang('ads::activate-ads'));
    $action = input('action', 'method');
    switch($action) {
        case 'method':
            return ads_render(view('ads::activate', array('ads' => $ads)));
            break;
        case 'paypal':
            $type = input('type', 'request');
            switch($type) {
                case 'request':
                    $plan = get_plan($ads['plan_id']);
                    require_once(path('includes/libraries/paypal_class.php'));

                    $paypal = new \paypal_class();
                    $paypal->admin_mail = config('paypal-notification-email');
                    $paypal->add_field('business', config('paypal-corporate-email'));
                    $paypal->add_field('cmd', '_cart');
                    $paypal->add_field('return', url_to_pager('ads-activate', array('id' => $ads['ads_id'])).'?action=paypal&type=success');
                    $paypal->add_field('cancel_return', url_to_pager('ads-activate', array('id' => $ads['ads_id'])).'?action=paypal&type=cancel');
                    $paypal->add_field('notify_url', url_to_pager('ads-paypal-notify', array('id' => $ads['ads_id'])));
                    $paypal->add_field('currency_code', config('default-currency'));
                    $paypal->add_field('invoice', time().'-'.$ads['ads_id']);
                    $paypal->add_field('upload',  $ads['ads_id']);
                    $paypal->add_field('item_name_1', $ads['name']);
                    $paypal->add_field('item_number_1', $ads['ads_id']);
                    $paypal->add_field('quantity', $ads['ads_id']);
                    $paypal->add_field('amount_1', $plan['price']);
                    $paypal->add_field('email', get_user_data('email_address'));
                    $paypal->add_field('first_name', get_user_data('first_name'));
                    $paypal->add_field('last_name', get_user_data('last_name'));
                    $paypal->add_field('address1', '');
                    $paypal->add_field('city', get_user_data('city'));
                    $paypal->add_field('state', get_user_data('state'));
                    $paypal->add_field('country', get_user_data('country'));
                    $paypal->add_field('zip', 'null');
                    $paypal->submit_paypal_post();
                    //$paypal->dump_fields();
                    break;
                case 'cancel':
                    return ads_render(view('ads::paypal/cancel'));
                    break;
                case 'success':
                    activate_ads($ads);
                    return ads_render(view('ads::paypal/success'));
                    break;
            }
           break;
    }

}

function ads_paypal_process_pager($app) {
    $id = segment(3);
    $ads = find_ads($id);
    $plan = get_plan($ads['plan_id']);

    require_once(path('includes/libraries/paypal_class.php'));

    $paypal = new \paypal_class();

    if($paypal->validate_ipn()) {
        //that means user has successfully paid
        return activate_ads($ads);
    }
}

function ads_stripe_process_pager($app) {
    $id = segment(3);
    $ads = find_ads($id);
    $plan = get_plan($ads['plan_id']);
    if (!$ads or $ads['user_id'] != get_userid()) return redirect_to_pager('ads-manage');
    $token = input('stripeToken');
    if(!$token) return redirect_back();

    require_once(path('includes/libraries/stripe/lib/Stripe.php'));


    try {

        \Stripe::setApiKey(config('stripe-secret-key'));
        \Stripe_Charge::create(array(
            'amount' => (int) $plan['price'] * 100, // this is in cents: $20
            'currency' => config('default-currency'),
            'card' => $token,
            'description' => $plan['description']
        ));
        activate_ads($ads['ads_id']);
        return redirect_to_pager('ads-manage');

    } catch (\Exception $e) {
        // Declined. Don't process their purchase.
        // Go back, and tell the user to try a new card
        return redirect_back();
    }
}

function delete_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);

    //$plan = get_plan($ads['plan_id']);
    if ((!$ads or $ads['user_id'] != get_userid()) and !is_admin()) return redirect_to_pager('ads-manage');
    delete_ads($id);

    if (input('admin')) return redirect_back();
    return redirect_to_pager('ads-manage');
}

function ads_clicked_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $ads = find_ads($id);
    if ($ads and is_loggedIn()) {
        $userClicks = get_privacy('ads-clicks', array());
        if (!in_array($ads['ads_id'], $userClicks)) {
            $clicks = $ads['clicks_stats'] + 1;
            $adsId = $ads['ads_id'];
            $quantity = $ads['quantity'];
            if ($ads['plan_type'] == 1) {
                $quantity -= config('ads-quantity-deduction-per-click', 5);
            }
            db()->query("UPDATE ads SET clicks_stats='{$clicks}',quantity='{$quantity}' WHERE ads_id='{$adsId}'");

            $userClicks[] = $ads['ads_id'];
            save_privacy_settings(array('ads-clicks' => $userClicks));
        }
    }
}

function load_plans_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $result = array(
        'content' => '',
        'description' => ''
    );

    $plans = get_ads_plans($type);
    $content = '';
    foreach($plans as $plan) {
        if (!$result['description']) $result['description'] = lang($plan['description']);
        $content .= '<option value="'.$plan['id'].'">'.lang($plan['name']).'</option>';
    }
    $result['content'] = $content;

    return json_encode($result);
}

function load_plans_description_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $plan = get_plan($id);
    if ($plan) return lang($plan['description']);
}

function load_plans_page_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $page = find_page($id);
    $result = array(
        'title' => '',
        'description' => '',
        'link' => '',
        'avatar' => ''
    );
    if ($page) {
        $result['title'] = $page['page_title'];
        $result['description'] = $page['page_desc'];
        $result['link'] = page_url(null, $page);
        $result['avatar'] = get_page_logo(600, $page);
    }

    return json_encode($result);
}
/**
 * Help function to render page with its layout
 */
function ads_render($content, $type = "manage") {
    return app()->render(view("ads::layout", array('content' => $content, 'type' => $type)));
}

 