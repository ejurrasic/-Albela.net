<?php
function sharer_pager($app){
    start:
    $app->setTitle(lang('sharer::post-on-feed'));
    $link = input('url') ? input('url') : url();
    $link = urldecode($link);
    $linkDetails = feed_process_link(perfect_url($link));
    $val = input('val');
    if(!$linkDetails) return view('sharer::sharer_close');
    if(is_loggedIn()) {
        if ($val) {
		CSRFProtection::validate();
            $expected = array(
                "privacy" => "",
                "type" => "",
                "type_id" => "",
                "type_data" => "",
                "entity_id" => "",
                "entity_type" => "",
                "to_user_id" => "",
                "content" => "",
                "link_details" => "",
                "location" => "",
                "feeling_type" => "",
                "feeling_data" => "",
                "feeling_text" => "",
                "poll" => "0",
                "poll_options" => array("", "")
            );
            $val['content'] = $link."\n".$val['content'];
            add_feed(array_merge($expected, $val));
            return view('sharer::sharer_close');
        }
        load_functions("feed::feed");
        return view('sharer::sharer', array('details' => $linkDetails, 'keywords' => $app->keywords, 'description' => $app->description));
    }
    $message = '';
    if ($val) {
		CSRFProtection::validate();
        extract($val);
        if ($username and $password) {
            $login = login_user($username, $password, input("val.remember"));
            if ($login) return view('sharer::sharer', array('details' => $linkDetails, 'keywords' => $app->keywords, 'description' => $app->description));
        }
        $message = lang('login-error');
    }
    return view('sharer::sharer_login', array('message' => $message));
}