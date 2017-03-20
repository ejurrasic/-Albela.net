<?php
function submit_contact($val) {
    $subject = sanitizeText($val['subject']);
    $message = sanitizeText($val['message']);
    $message = lawedContent(stripslashes($message));
    mailer()->setAddress(config('email-from-address'))->setSubject($subject)->setMessage($message)->send();
    return true;
}