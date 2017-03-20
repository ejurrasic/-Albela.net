<?php
function post_boost_pager($app){
    $p_id = input('pdi');
    $type = input('type');

    switch($type){
        case 'Post':
            $feed  = find_feed($p_id);
            if(!empty($feed)){
                $_SESSION['booster_type'] = 'feed';
                echo view('booster::test',array('feed'=>$feed));

            }
            break;
        case 'Listing':
            $listing = marketplace_get_listing($p_id);
            if(!empty($listing)){
                $_SESSION['booster_type'] = 'listing';
               echo view('booster::listing',array('listing'=>$listing[0]));
                //print_r($listing); die();
            }
            break;
    }

}

function post_boost_create_pager($app){
    $result = array(
        'status' => 0,
        'message' => lang('booster::error-occured')
    );
    $val = input('val');
    if(isset($_SESSION['booster_type'])) $val['booster_type'] = $_SESSION['booster_type'];
   if (!$val) return json_encode($result);

  return post_boost_create($val, $result);

}

function get_my_boost_pager($app){
    $app->setTitle(lang('booster::boost'));
   $boosts= get_boosted_posts();
    //print_r($boosts);
  //return boost_render(view('booster::booster_home',array('boosts'=>$boosts)));
    return $app->render(view('booster::booster_home',array('boosts'=>$boosts)));
}

function post_boost_delete_pager($app){
    $id = segment(2);
    delete_boost($id);
   return redirect(url_to_pager('manage-boost'));
}
function post_boost_activate_pager($app) {
    $id = segment(2);
    $bp = find_pb($id);

    //$ads silent

    if (!$bp or $bp['user_id'] != get_userid()) return redirect_to_pager('manage-boost');
    $app->setTitle(lang('booster::activate-boost'));
    $action = input('action', 'method');
    switch($action) {
        case 'method':
            return $app->render(view('booster::activate', array('bp' => $bp)));
            break;
        case 'paypal':
            $type = input('type', 'request');
            switch($type) {
                case 'request':
                    $plan = get_plan($bp['plan_id']);
                    require_once(path('includes/libraries/paypal_class.php'));

                    $paypal = new \paypal_class();
                    $paypal->admin_mail = config('paypal-notification-email');
                    $paypal->add_field('business', config('paypal-corporate-email'));
                    $paypal->add_field('cmd', '_cart');
                    $paypal->add_field('return', url_to_pager('boost-activate', array('id' => $bp['pb_id'])).'?action=paypal&type=success');
                    $paypal->add_field('cancel_return', url_to_pager('boost-activate', array('id' => $bp['pb_id'])).'?action=paypal&type=cancel');
                    $paypal->add_field('notify_url', url_to_pager('boost-paypal-notify', array('id' => $bp['pb_id'])));
                    $paypal->add_field('currency_code', config('default-currency'));
                    $paypal->add_field('invoice', time().'-'.$bp['pb_id']);
                    $paypal->add_field('upload',  $bp['pb_id']);
                    $paypal->add_field('item_name_1', $bp['name']);
                    $paypal->add_field('item_number_1', $bp['pb_id']);
                    $paypal->add_field('quantity', $bp['quantity']);
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
                    return $app->render(view('booster::paypal/cancel'));
                    break;
                case 'success':
                    return $app->render(view('booster::paypal/success'));
                    break;
            }
            break;
    }


}

function boost_paypal_process_pager($app) {
    $id = segment(3);
    $bp = find_pb($id);
    $plan = get_plan($bp['pb_id']);

    require_once(path('includes/libraries/paypal_class.php'));

    $paypal = new \paypal_class();

    if($paypal->validate_ipn()) {
        //that means user has successfully paid
        return activate_page_boost($bp);
    }
}

function boost_stripe_process_pager($app) {
    $id = segment(3);
    $bp = find_pb($id);
    $plan = get_plan($bp['plan_id']);
    if (!$bp or $bp['user_id'] != get_userid()) return redirect_to_pager('manage-boost');
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
        activate_page_boost($bp);
        return redirect_to_pager('manage-boost');

    } catch (\Exception $e) {
        // Declined. Don't process their purchase.
        // Go back, and tell the user to try a new card
        return redirect_back();
    }
}

function boost_render($content){
    return app()->render(view('boost::layout',array('content'=>$content)));
}

function booster_clicked_pager($app) {
    $id = input('id');
    $pb = find_pb($id);

    if ($pb and is_loggedIn()) {
        $userClicks = get_privacy('booster-clicks', array());
        if (!in_array($pb['pb_id'], $userClicks)) {
            $clicks = $pb['click_stats'] + 1;
            $adsId = $pb['pb_id'];
           // print_r($pb); die();
           // echo $clicks; die();
            $quantity = $pb['quantity'];
            if ($pb['plan_type'] == 1) {
                $quantity -= config('ads-quantity-deduction-per-click', 5);
            }
            db()->query("UPDATE post_boost SET click_stats='{$clicks}',quantity='{$quantity}' WHERE pb_id='{$adsId}'");

            $userClicks[] = $pb['pb_id'];
            save_privacy_settings(array('booster-clicks' => $userClicks));
        }
    }
}
