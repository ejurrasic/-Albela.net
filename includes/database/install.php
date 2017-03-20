<?php
function core_install_database($free = null) {
    $db = db();

    //admin settings table

    $db->query("CREATE TABLE IF NOT EXISTS `settings` (
      `val` varchar(100) NOT NULL,
      `value` text NOT NULL,
      UNIQUE KEY `val` (`val`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    $db->query("CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) CHARACTER SET utf8 NOT NULL,
  `password` varchar(200) CHARACTER SET utf8 NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `social_email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT '0',
  `welcome_passed` int(11) NOT NULL DEFAULT '0',
  `completion` int(11) NOT NULL DEFAULT '50',
  `ip_address` varchar(100) NOT NULL,
  `timezone` varchar(255) NOT NULL,
  `verified` int(11) NOT NULL DEFAULT '0',
  `avatar` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `resized_cover` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `bannned` int(11) NOT NULL DEFAULT '0',
  `privacy_info` text NOT NULL,
  `design_details` text NOT NULL,
  `lang` varchar(100) NOT NULL,
  `online_status` int(11) NOT NULL DEFAULT '0',
  `online_time` int(11) NOT NULL DEFAULT '0',
  `hash` text NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `activated` int(11) NOT NULL DEFAULT '0',
  `birth_day` varchar(100) NOT NULL,
  `birth_month` varchar(255) NOT NULL,
  `birth_year` varchar(100) NOT NULL,
  `join_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_active_time` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");if(!$free) return false;

    $db->query("CREATE TABLE IF NOT EXISTS `user_savings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `type_id` int(11) NOT NULL,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `verification_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `type_id` int(11) NOT NULL,
      `ignored` int(11) NOT NULL DEFAULT '0',
      `time` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;
    ");


    $db->query("CREATE TABLE IF NOT EXISTS `user_details` (
      `user_id` int(11) NOT NULL,
      UNIQUE KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `show_in_form` int(11) NOT NULL DEFAULT '1',
  `required` int(11) NOT NULL DEFAULT '0',
  `field_type` varchar(100) NOT NULL,
  `field_data` text NOT NULL,
  `listorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `custom_field_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `slug` varchar(255) NOT NULL,
      `title` varchar(255) NOT NULL,
      `type`  varchar(100) NOT NULL,
      `listorder` int(11),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");

    /**
     * Themes table
     */
    $db->query("CREATE TABLE IF NOT EXISTS `themes` (
      `type` varchar(100) NOT NULL,
      `theme` varchar(255) NOT NULL,
      PRIMARY KEY (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    $db->query("INSERT INTO `themes` (`type`, `theme`) VALUES
    ('frontend', 'default'),
    ('backend', 'default'),
    ('mobile', 'default');
    ");


    /**
     * feeds table
     */
    $db->query("CREATE TABLE IF NOT EXISTS `feeds` (
  `feed_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(100) NOT NULL DEFAULT 'user',
  `to_user_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `type_data` text NOT NULL,
  `photos` text NOT NULL,
  `video` text NOT NULL,
  `files` text NOT NULL,
  `feed_content` text NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT '0',
  `link_details` text NOT NULL,
  `tags` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `can_share` int(11) NOT NULL DEFAULT '1',
  `shared` int(11) DEFAULT '0',
  `shared_id` int(11) NOT NULL,
  `shared_count` int(11) NOT NULL,
  `edited` int(11) NOT NULL,
  `time` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feed_id`),
  KEY `user_id` (`entity_id`,`type`,`type_id`),
  KEY `type` (`type`),
  KEY `entity_id` (`entity_id`),
  KEY `entity_type` (`entity_type`),
  KEY `type_id` (`type_id`),
  KEY `privacy` (`privacy`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=430 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `feed_pinned` (
      `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `type_id` int(11) NOT NULL,
      `feed_id` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `poll_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `answer_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voters` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=20 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `poll_results` (
      `user_id` int(11) NOT NULL,
      `poll_id` int(11) NOT NULL,
      `answer_id` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `static_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `tags` text COLLATE utf8_unicode_ci NOT NULL,
  `footer_link` int(11) NOT NULL,
  `explore_link` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;
");
    /**
     * Blocks table
     */
    $db->query("CREATE TABLE IF NOT EXISTS `blocks` (
  `id` varchar(255) NOT NULL,
  `page_id` varchar(255) NOT NULL,
  `block_view` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    //add_page_block("account/status", array("feed"), '21232323423');

    /**
     * Country management tables
     */
    $db->query("CREATE TABLE IF NOT EXISTS `countries` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `country_name` varchar(255) NOT NULL,
      `order` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

    //add_page_block("account/status", array("feed"));


    dump_countries_data();

    /**
     * language table
     */
    $db->query("CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` varchar(100) NOT NULL,
  `language_title` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `dir` varchar(50) NOT NULL DEFAULT 'ltr',
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_id` (`language_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;
    ");
    $db->query("INSERT INTO `languages` (`language_id`, `language_title`, `active`) VALUES
    ('english', 'English(US) &#x200E;', 1);");

    $db->query("CREATE TABLE IF NOT EXISTS `language_phrases` (
      `language_id` varchar(255) NOT NULL,
      `phrase_id` varchar(255) NOT NULL,
      `phrase_original` text NOT NULL,
      `phrase_translation` text NOT NULL,
      `plugin` varchar(255) NOT NULL DEFAULT 'core'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `plugins` (
      `id` varchar(255) NOT NULL,
      `active` int(11) NOT NULL DEFAULT '1',
      UNIQUE KEY `id` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE IF NOT EXISTS `email_templates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email_id` varchar(255) NOT NULL,
      `lang_id` varchar(255) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text NOT NULL,
      `placeholders` text NOT NULL,
      `subject` varchar(255) NOT NULL,
      `body_content` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

    dump_email_templates();

    $db->query("CREATE TABLE IF NOT EXISTS `user_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_title` varchar(255) NOT NULL,
  `access_admin` varchar(11) NOT NULL DEFAULT '0',
  `roles` text NOT NULL,
  `can_edit` int(11) NOT NULL DEFAULT '1',
  `can_delete` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `user_blocks` (
  `user_id` int(11) NOT NULL,
  `blocked_user_id` int(11) NOT NULL,
  UNIQUE KEY `blocked_user_id` (`blocked_user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


    $db->query("CREATE TABLE IF NOT EXISTS `user_tags` (
      `tagger_id` int(11) NOT NULL,
      `tagged_id` int(11) NOT NULL,
      `tag_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
      `tag_id` int(11) NOT NULL,
      `tag_data` text COLLATE utf8_unicode_ci NOT NULL,
      KEY `tagged_id` (`tagged_id`,`tag_type`),
      KEY `tagger_id` (`tagger_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `subscribers` (
      `user_id` int(11) NOT NULL,
      `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `type_id` int(11) NOT NULL,
      KEY `user_id` (`user_id`,`type`,`type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    add_user_role(array(
        'title' => 'Super Admin',
        'admin' => 1,
        'can_delete' => 0,
        'can_edit' => 0,
        'roles' => get_all_role_permissions()
    ));

    add_user_role(array(
        'title' => 'Default',
        'admin' => 0,
        'can_delete' => 0,
        'can_edit' => 1,
        'roles' => get_all_role_permissions()
    ));

    $db->query("CREATE TABLE IF NOT EXISTS `medias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `ref_id` varchar(100) NOT NULL,
  `ref_name` varchar(100) NOT NULL,
  `album_id` int(11) NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `path_2` (`path`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=422 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `mail_hash` (
      `hash_id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` varchar(255) NOT NULL,
      `hash_code` varchar(255) NOT NULL,
      `timestamp` int(11) NOT NULL,
      PRIMARY KEY (`hash_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `user_savings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `type_id` int(11) NOT NULL,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

}

function dump_email_templates() {
    add_email_template("header", array(
        'title' => 'Header',
        'description' => 'This can be added to other emails using the [header] placeholder'
    ));

    add_email_template("footer", array(
        'title' => 'Footer',
        'description' => 'This can be added to other emails using the [footer] placeholder'
    ));

    add_email_template("forgot-password", array(
        'title' => 'Forgot Password',
        'description' => 'This is forgot password email template sent when user want to reset password',
        'subject'   => 'Reset your password',
        'body_content' => '
            [header]

            Follow this link to reset your password <a href="[link]">[link]</a>

            [footer]
        ',
        'placeholders' => '[link]'
    ));

    add_email_template("signup-activate", array(
        'title' => 'Signup Activation',
        'description' => 'This is activation email template sent when user want first signup to your network',
        'subject'   => 'Welcome to [site-title] - Confirm your email address',
        'body_content' => '
            [header]

            Thank you for joining our social network. Please click the link or copy the code below to validate your email:
            Link: <a href="[link]">[link]</a>
            Code : [code]

            [footer]
        ',
        'placeholders' => '[link],[code],[site-title],[recipient-title],[recipient-link]'
    ));

    add_email_template("signup-welcome", array(
        'title' => 'Signup Welcome Email',
        'description' => 'This is welcome email template sent when user want first signup to your network',
        'subject'   => 'Welcome to [site-title]',
        'body_content' => '
            [header]
            Thank you for joining our social network. Click the following link and enter your information below to login:

            <a href="[login_link]">[login_link]</a>

            [footer]
        ',
        'placeholders' => '[login_link],[site-title],[recipient-title],[recipient-link]'
    ));
}

function dump_countries_data() {
    db()->query("INSERT INTO `countries` (`id`, `country_name`, `order`) VALUES
    (1, 'afghanistan', 0),
    (2, 'albania', 0),
    (3, 'algeria', 0),
    (4, 'american samoa', 0),
    (5, 'andorra', 0),
    (6, 'angola', 0),
    (7, 'anguilla', 0),
    (8, 'antarctica', 0),
    (9, 'antigua and barbuda', 0),
    (10, 'antilles, netherlands', 0),
    (11, 'argentina', 0),
    (12, 'armenia', 0),
    (13, 'aruba', 0),
    (14, 'australia', 0),
    (15, 'austria', 0),
    (16, 'azerbaijan', 0),
    (17, 'bahamas', 0),
    (18, 'bahrain', 0),
    (19, 'bangladesh', 0),
    (20, 'barbados', 0),
    (21, 'belarus', 0),
    (22, 'belgium', 0),
    (23, 'belize', 0),
    (24, 'benin', 0),
    (25, 'bermuda', 0),
    (26, 'bhutan', 0),
    (27, 'bolivia', 0),
    (28, 'bosnia and herzegovina', 0),
    (29, 'botswana', 0),
    (30, 'brazil', 0),
    (31, 'british indian ocean territory', 0),
    (32, 'british virgin islands', 0),
    (33, 'brunei darussalam', 0),
    (34, 'bulgaria', 0),
    (35, 'burkina faso', 0),
    (36, 'burundi', 0),
    (37, 'cambodia', 0),
    (38, 'cameroon', 0),
    (39, 'canada', 0),
    (40, 'cape verde', 0),
    (41, 'cayman islands', 0),
    (42, 'central african republic', 0),
    (43, 'chad', 0),
    (44, 'chile', 0),
    (45, 'china', 0),
    (46, 'christmas island', 0),
    (47, 'cocos (keeling) islands', 0),
    (48, 'colombia', 0),
    (49, 'comoros', 0),
    (50, 'congo', 0),
    (51, 'cook islands', 0),
    (52, 'costa rica', 0),
    (53, 'croatia', 0),
    (54, 'cuba', 0),
    (55, 'cyprus', 0),
    (56, 'czech republic', 0),
    (57, 'denmark', 0),
    (58, 'djibouti', 0),
    (59, 'dominica', 0),
    (60, 'dominican republic', 0),
    (61, 'east timor (timor-leste)', 0),
    (62, 'ecuador', 0),
    (63, 'egypt', 0),
    (64, 'el salvador', 0),
    (65, 'equatorial guinea', 0),
    (66, 'eritrea', 0),
    (67, 'estonia', 0),
    (68, 'ethiopia', 0),
    (69, 'falkland islands (malvinas)', 0),
    (70, 'faroe islands', 0),
    (71, 'fiji', 0),
    (72, 'finland', 0),
    (73, 'france', 0),
    (74, 'french guiana', 0),
    (75, 'french polynesia', 0),
    (76, 'gabon', 0),
    (77, 'gambia, the', 0),
    (78, 'georgia', 0),
    (79, 'germany', 0),
    (80, 'ghana', 0),
    (81, 'gibraltar', 0),
    (82, 'greece', 0),
    (83, 'greenland', 0),
    (84, 'grenada', 0),
    (85, 'guadeloupe', 0),
    (86, 'guam', 0),
    (87, 'guatemala', 0),
    (88, 'guernsey and alderney', 0),
    (89, 'guinea', 0),
    (90, 'guinea-bissau', 0),
    (91, 'guinea, equatorial', 0),
    (92, 'guiana, french', 0),
    (93, 'guyana', 0),
    (94, 'haiti', 0),
    (95, 'holy see (vatican city state)', 0),
    (96, 'holland', 0),
    (97, 'honduras', 0),
    (98, 'hong kong, (china)', 0),
    (99, 'hungary', 0),
    (100, 'iceland', 0),
    (101, 'india', 0),
    (102, 'indonesia', 0),
    (103, 'iran', 0),
    (104, 'iraq', 0),
    (105, 'ireland', 0),
    (106, 'isle of man', 0),
    (107, 'israel', 0),
    (108, 'italy', 0),
    (109, 'jamaica', 0),
    (110, 'japan', 0),
    (111, 'jersey', 0),
    (112, 'jordan', 0),
    (113, 'kazakhstan', 0),
    (114, 'kenya', 0),
    (115, 'kiribati', 0),
    (116, 'korea(north)', 0),
    (117, 'korea(south)', 0),
    (118, 'kosovo', 0),
    (119, 'kuwait', 0),
    (120, 'kyrgyzstan', 0),
    (121, 'latvia', 0),
    (122, 'lebanon', 0),
    (123, 'lesotho', 0),
    (124, 'liberia', 0),
    (125, 'libyan arab jamahiriya', 0),
    (126, 'liechtenstein', 0),
    (127, 'lithuania', 0),
    (128, 'luxembourg', 0),
    (129, 'macao, (china)', 0),
    (130, 'macedonia, tfyr', 0),
    (131, 'madagascar', 0),
    (132, 'malawi', 0),
    (133, 'malaysia', 0),
    (134, 'maldives', 0),
    (135, 'mali', 0),
    (136, 'malta', 0),
    (137, 'marshall islands', 0),
    (138, 'martinique', 0),
    (139, 'mauritania', 0),
    (140, 'mauritius', 0),
    (141, 'mayotte', 0),
    (142, 'mexico', 0),
    (143, 'micronesia', 0),
    (144, 'moldova', 0),
    (145, 'monaco', 0),
    (146, 'mongolia', 0),
    (147, 'montenegro', 0),
    (148, 'montserrat', 0),
    (149, 'morocco', 0),
    (150, 'mozambique', 0),
    (151, 'myanmar', 0),
    (152, 'namibia', 0),
    (153, 'nauru', 0),
    (154, 'nepal', 0),
    (155, 'netherlands', 0),
    (156, 'netherlands antilles', 0),
    (157, 'new caledonia', 0),
    (158, 'new zealand', 0),
    (159, 'nicaragua', 0),
    (160, 'niger', 0),
    (161, 'nigeria', 0),
    (162, 'niue', 0),
    (163, 'norfolk island', 0),
    (164, 'northern mariana islands', 0),
    (165, 'norway', 0),
    (166, 'oman', 0),
    (167, 'pakistan', 0),
    (168, 'palau', 0),
    (169, 'palestinian territory', 0),
    (170, 'panama', 0),
    (171, 'papua new guinea', 0),
    (172, 'paraguay', 0),
    (173, 'peru', 0),
    (174, 'philippines', 0),
    (175, 'pitcairn island', 0),
    (176, 'poland', 0),
    (177, 'portugal', 0),
    (178, 'puerto rico', 0),
    (179, 'qatar', 0),
    (180, 'reunion', 0),
    (181, 'romania', 0),
    (182, 'russia', 0),
    (183, 'rwanda', 0),
    (184, 'sahara', 0),
    (185, 'saint helena', 0),
    (186, 'saint kitts and nevis', 0),
    (187, 'saint lucia', 0),
    (188, 'saint pierre and miquelon', 0),
    (189, 'saint vincent and the grenadines', 0),
    (190, 'samoa', 0),
    (191, 'san marino', 0),
    (192, 'sao tome and principe', 0),
    (193, 'saudi arabia', 0),
    (194, 'senegal', 0),
    (195, 'serbia', 0),
    (196, 'seychelles', 0),
    (197, 'sierra leone', 0),
    (198, 'singapore', 0),
    (199, 'slovakia', 0),
    (200, 'slovenia', 0),
    (201, 'solomon islands', 0),
    (202, 'somalia', 0),
    (203, 'south africa', 0),
    (204, 's. georgia and s. sandwich is.', 0),
    (205, 'spain', 0),
    (206, 'sri lanka (ex-ceilan)', 0),
    (207, 'sudan', 0),
    (208, 'suriname', 0),
    (209, 'svalbard and jan mayen islands', 0),
    (210, 'swaziland', 0),
    (211, 'sweden', 0),
    (212, 'switzerland', 0),
    (213, 'syrian arab republic', 0),
    (214, 'taiwan', 0),
    (215, 'tajikistan', 0),
    (216, 'tanzania', 0),
    (217, 'thailand', 0),
    (218, 'timor-leste (east timor)', 0),
    (219, 'togo', 0),
    (220, 'tokelau', 0),
    (221, 'tonga', 0),
    (222, 'trinidad and tobago', 0),
    (223, 'tunisia', 0),
    (224, 'turkey', 0),
    (225, 'turkmenistan', 0),
    (226, 'turks and caicos islands', 0),
    (227, 'tuvalu', 0),
    (228, 'uganda', 0),
    (229, 'ukraine', 0),
    (230, 'united arab emirates', 0),
    (231, 'united kingdom', 0),
    (232, 'united states', 0),
    (233, 'us minor outlying islands', 0),
    (234, 'uruguay', 0),
    (235, 'uzbekistan', 0),
    (236, 'vanuatu', 0),
    (237, 'vatican city state (holy see)', 0),
    (238, 'venezuela', 0),
    (239, 'viet nam', 0),
    (240, 'virgin islands, british', 0),
    (241, 'virgin islands, u.s.', 0),
    (242, 'wallis and futuna', 0),
    (243, 'western sahara', 0),
    (244, 'yemen', 0),
    (245, 'zambia', 0),
    (246, 'zimbabwe', 0);");

}
