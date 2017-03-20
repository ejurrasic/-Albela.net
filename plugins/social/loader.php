<?php
load_functions("social::social");
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("social::css/social.css");
        register_asset("social::js/social.js");
    }
});

register_pager('social/auth/facebook', array('as' =>  'facebook-auth', 'use' => 'social::social@facebook_auth_pager'));

register_pager('social/auth/twitter', array('as' =>  'twitter-auth', 'use' => 'social::social@twitter_auth_pager'));

register_pager('social/auth/twitter/data', array('as' =>  'twitter-auth-data', 'use' => 'social::social@twitter_auth_data_pager'));

register_pager('social/auth/vk', array('as' =>  'vk-auth', 'use' => 'social::social@vk_auth_pager'));
register_pager('social/auth/vk/data', array('as' =>  'vk-auth-data', 'use' => 'social::social@vk_auth_data_pager'));

register_pager('social/auth/google', array('as' =>  'googleplus-auth', 'use' => 'social::social@google_auth_pager'));


register_pager('social/import/gmail', array('as' => 'social-import-gmail', 'use' => 'social::social@gmail_import_pager'));

register_pager('social/import/facebook', array( 'as' => 'social-import-facebook', 'use' => 'social::social@facebook_import_pager'));

register_pager('social/confirm/import', array( 'use' => 'social::social@social_import_confirm_pager'));

register_pager('social/get/imports', array( 'use' => 'social::social@social_get_imports_pager'));

register_pager('social/invite/user', array( 'use' => 'social::social@social_invite_user_pager'));

register_hook('account.settings.menu', function() {
    add_menu("account-menu", array("id" => "invite", "link" => url_to_pager("account").'?action=invite', "title" => lang("find-friends")));
});

register_hook('account.settings', function($action) {
    if($action == 'invite') {
        app()->setTitle(lang('social::invite-friends'));
        $emails = input('emails');
        $message = null;
        if ($emails) {
            $emails = explode(',',  $emails);
            if (count($emails) > 0) {
                $newEMails = array();
                foreach($emails as $email) {
                    $newEMails[] = array('email' => $email, 'avatar' => '', 'name' => '');
                    mailer()->setAddress($email, '')->template('social-invite-member', array(
                        'link' => url('signup'),
                        'site-title' => config('site_title'),
                        'inviter' => get_user_name(),
                        'inviter-link' => profile_url(),
                        'inviter-avatar' => get_avatar(75),
                        'reg-link' => url_to_pager('signup')
                    ))->send();
                }
                social_add_imports($newEMails, 'mail');
                $message = lang('social::invite-mail-sent');
            }
        }
        return view('social::invite', array('message' => $message));
    }
});