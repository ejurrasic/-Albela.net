<?php
function choose_plan_pager($app) {
    $app->setLayout("layouts/blank")->setTitle(lang('membership::choose-plan'))->onHeaderContent = false;

    return $app->render(view("membership::choose-plan"));
}

function payment_pager($app) {
    $id = input('plan');
    $plan = get_membership_plan($id);
    if(!$plan) redirect(url("membership/choose-plan"));

    if($plan['type'] == 'free') {
        $userid = get_userid();
        $role = $plan['user_role'];

        db()->query("UPDATE users SET membership_type='free',membership_plan='{$id}',role='{$role}' WHERE id='{$userid}'");
        redirect(go_to_user_home(null));
    }
    $app->setLayout("layouts/blank")->setTitle(lang('membership::select-payment-method'))->onHeaderContent = false;

    return $app->render(view("membership::payment", array("plan" => $plan)));
}

function payment_paypal_pager($app) {
    $id = segment(3);
    $type = input('type', 'request');
    switch($type) {
        case 'request':
            $plan = get_membership_plan($id);
            require_once(path('includes/libraries/paypal_class.php'));

            $paypal = new \paypal_class();
            $paypal->admin_mail = config('paypal-notification-email');
            $paypal->add_field('business', config('paypal-corporate-email'));
            $paypal->add_field('cmd', '_cart');
            $paypal->add_field('return', url_to_pager('membership-paypal', array('id' => $plan['id'])).'?action=paypal&type=success');
            $paypal->add_field('cancel_return', url_to_pager('membership-paypal', array('id' => $plan['id'])).'?action=paypal&type=cancel');
            $paypal->add_field('notify_url', url_to_pager('membership-paypal-notify', array('id' => $plan['id'])));
            $paypal->add_field('currency_code', config('default-currency'));
            $paypal->add_field('invoice', time().'-'.$plan['id']);
            $paypal->add_field('upload',  $plan['id']);
            $paypal->add_field('item_name_1', lang($plan['title']));
            $paypal->add_field('item_number_1', $plan['id']);
            $paypal->add_field('quantity', $plan['id']);
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
            return redirect(url("membership/choose-plan"));
            break;
        case 'success':
            membership_activate($id);
            redirect(go_to_user_home(null));
            break;
    }

}

function payment_stripe_pager($app) {
    $id = segment(3);
    $plan = get_membership_plan($id);
    if (!$plan) return redirect(url("membersahip/choose-plan"));
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
        membership_activate($id);
        redirect(go_to_user_home(null));

    } catch (\Exception $e) {
        // Declined. Don't process their purchase.
        // Go back, and tell the user to try a new card
        return redirect_back();

    }
}
 