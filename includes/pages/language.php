<?php
function language_pager($app) {
    $language = segment(2);
    //store the language in session for later use
    //session_put("sv_language", $language);
    setcookie("sv_language", $language, time() + 30 * 24 * 60 * 60, config('cookie_path'));
    redirect_back(array('language-changed' => lang('new-languaged-selected', array('lang' => $language))));
}
 