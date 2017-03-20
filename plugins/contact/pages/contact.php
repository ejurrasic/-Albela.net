<?php
function contact_pager($app) {
    $app->setTitle(lang('contact-us'));
    $message = null;
    $val = input('val', null, array('message'));
    if ($val) {
		CSRFProtection::validate();
        $message = submit_contact($val) ? lang('contact::message-sent') : lang('contact::message-not-sent');
    }
    return $app->render(view('contact::contact', array('message' => $message)));
}