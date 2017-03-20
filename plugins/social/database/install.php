<?php
function social_install_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `social_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` text COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1995 ;
");

    add_email_template("social-invite-member", array(
        'title' => 'Social Invite Members',
        'description' => 'This is the email sent to invited social accounts like gmail , facebook imports',
        'subject'   => '[inviter] invited you to [site-title]',
        'body_content' => '
            [header]

            [inviter] invited you to [site-title] you can follow this link to register <a href="[link]">[link]</a>

            [footer]
        ',
        'placeholders' => '[link],[reg-link],[inviter][inviter-link],[inviter-avatar]'
    ));
}
 