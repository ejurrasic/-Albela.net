<?php

 function home_pager($app) {
     //$design = config('home-design', 'splash');
     if (is_loggedIn()) redirect_to_pager('feed');
     $app->onHeader = (config('hide-homepage-header', false)) ? false : true;
     $app->setTitle(lang('welcome-to-social'));
     return $app->render();
 }

function translate_pager($app) {
    CSRFProtection::validate(false);
    $content = $_POST['text'];

    try{
        require_once(path("includes/libraries/bingtranslator.php"));
        $BingTranslator = new BingTranslator(config('bing-id'), config('bing-secret'));

//Uebersetzen eines Worts.
        $translation = $BingTranslator->getTranslation('', 'en', $content);

//Ausgeben des uebersetzten Worts (Hallo).
        echo format_output_text($translation);
    } catch(Exception $e) {}
}