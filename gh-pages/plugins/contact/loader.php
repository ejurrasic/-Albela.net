<?php
load_functions('contact::contact');
register_asset("contact::css/contact.css");
register_pager("contact", array('as' => 'contact-page', 'use' => 'contact::contact@contact_pager'));
add_available_menu('contact::contact-us', 'contact', 'ion-android-mail');
