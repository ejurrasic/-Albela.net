<?php
function membership_install_database() {
    $db = db();


    $db->query("CREATE TABLE IF NOT EXISTS `membership_invoices` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `invoice_id` varchar(255) NOT NULL,
      `user_id` int(11) NOT NULL,
      `amount` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `membership_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_role` int(11) NOT NULL,
  `recommend` int(11) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL,
  `expire_no` int(11) NOT NULL,
  `expire_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;");

    $db->query("ALTER TABLE  `users` ADD  `membership_type` VARCHAR( 255 ) NOT NULL AFTER  `birth_year` ,
    ADD  `membership_expire_time` INT NOT NULL AFTER  `membership_type` ,
    ADD  `membership_plan` INT NOT NULL AFTER  `membership_expire_time` ;
    ");
}
 