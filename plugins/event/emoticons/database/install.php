<?php
function emoticons_install_database() {
    $db = db();
    load_functions('emoticons::emoticon');

    $db->query("CREATE TABLE IF NOT EXISTS `emoticons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
  `symbol` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=34 ;");


    add_emoticon(array(
        'title' => 'face',
        'symbol' => '):',
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-1_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'horn',
        'symbol' => '%:',
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-2_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'crying',
        'symbol' => ":‑(",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-3_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'shock',
        'symbol' => ":$",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-4_24.png',
        'category' => 1,
    ));
    add_emoticon(array(
        'title' => 'no-expression',
        'symbol' => ":‑|",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-5_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'cool',
        'symbol' => "|‑O",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-6_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'eye broken',
        'symbol' => ":‑&",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-8_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Drunk',
        'symbol' => "%)",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-9_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Love Face',
        'symbol' => "*<|",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-10_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Happy',
        'symbol' => "B^D",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-11_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Sleeping',
        'symbol' => "(-_-)zzz",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-17_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Rose',
        'symbol' => "@}‑;‑",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-16_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Devil',
        'symbol' => "}:‑",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-19_24.png',
        'category' => 1,
    ));

    add_emoticon(array(
        'title' => 'Crying',
        'symbol' => "(/_;)",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/emoticons/Smiley-24_24.png',
        'category' => 1,
    ));

    //stickers
    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$1",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/1.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$2",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/2.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$3",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/3.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$4",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/4.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$5",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/5.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$6",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/6.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$7",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/7.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$8",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/8.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$9",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/9.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-10",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/10.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-11",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/11.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-12",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/12.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-13",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/13.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-14",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/14.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-15",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/15.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-16",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/16.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-17",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/17.png',
        'category' => 2,
    ));



    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-19",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/19.png',
        'category' => 2,
    ));

    add_emoticon(array(
        'title' => '---',
        'symbol' => "sti-ck$-20",
        'icon' => 'themes/frontend/default/plugins/emoticons/images/stickers/20.png',
        'category' => 2,
    ));
}
 