<?php
function relationship_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `relationship` (
  `relationship_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_userid` int(11) NOT NULL,
  `to_userid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `confirm` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY (`relationship_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=83;
");
    

    add_email_template("follow-you", array(
        'title' => 'New Follower Email',
        'description' => 'This is welcome email template sent when user get new followers',
        'subject'   => '[follower-name] started following yon on [site-title]',
        'body_content' => '
            [header]
            You have new follower from [follower-name] on [site-title]
            [login_link] Login to see your new folllowers

            [footer]
        ',
        'placeholders' => '[login-link],[site-title],[recipient-title],[recipient-link],[follower-name],[follower-link]'
    ));
}
 