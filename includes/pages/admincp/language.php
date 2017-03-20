<?php
get_menu('admin-menu', 'cms')->setActive();
get_menu('admin-menu', 'cms')->findMenu('admin-language')->setActive();
function language_pager($app) {
    $action = input("action", "list");
    $content = null;
    $message = null;

    switch($action) {
        case 'create':
            $app->setTitle(lang('create-language-pack'));
            $val = input("val");
            if ($val) {
		CSRFProtection::validate();
                $added = add_language($val);
                if ($added) redirect_to_pager("admin-languages");
                $message = "Failed to create language";
            }
            $content = view("language/create", array("message" => $message));
            break;
        case 'edit':
            $app->setTitle(lang('edit-language-pack'));
            $id = input("id");
            $language = get_language($id);
            if (!$language) redirect_to_pager("admin-languages");
            $title = input("title");
            if ($title) {
                save_language($title, $id);
                redirect_to_pager("admin-languages");
            }

            $content = view("language/edit", array("language" => $language));
            break;
        case 'import':
            //$file = path('english.xml');
            $app->setTitle(lang('import-language'));
            $message = null;
            if (input_file('file')) {
                $uploader = new Uploader(input_file('file'), 'file');
                $uploader->setFileTypes(array('xml'))->setPath('temp/xml/');
                if ($uploader->passed()) {
                    $result = $uploader->uploadFile()->result();
                    $file = path($result);
                    try{
                        $doc = new DOMDocument();
                        //$doc->validateOnParse = true;
                        $doc->loadXML(file_get_contents($file));
                        $languages = $doc->getElementsByTagName('language');
                        foreach($languages as $l) {
                            $languageName = $l->getAttribute('name');
                            $languageDir = $l->getAttribute('direction');
                            $languageId = $l->getAttribute('id');
                            $phrases = array();
                            foreach($l->getElementsByTagName('translation') as $phrase) {
                                $phrases[$phrase->getAttribute('id')] = $phrase->nodeValue;
                            }


                            //lets create the language if not exists
                            if (!language_exist($languageId)) {
                                add_language(array(
                                    'id' => $languageId,
                                    'title' => $languageName,
                                    'dir' => $languageDir,
                                    'from' => ''
                                ));
                            }
                            //ok we are good to update the phrases now
                            $langPhrases = get_phrases($languageId);
                            $temp = array();
                            foreach($phrases as $id => $phrase) {
                                $phrase = mysqli_escape_string(db(), $phrase);
                                if (isset($langPhrases[$id])) {
                                    update_language_phrase($id, $phrase, $languageId);
                                } else {
                                    $temp[] = $id;
                                    add_language_phrase($id, $phrase, $languageId, 'core');
                                }
                            }

                            return redirect_to_pager('admin-languages');
                        }
                    } catch(Exception $e) {
                        $message = "Language file not supported, only xml file allowed";
                        $message = $e->getMessage();
                    }

                } else {
                    $message = $uploader->getError();
                }

            }
            $content = view('language/import', array('message' => $message));
            break;
        case 'export':
            $id = input("id");
            $phrases = get_phrases($id);
            $language = get_language($id);
            $content = "<?xml version='1.0' encoding='utf-8'?>\n";
            $content .= "<language name='".$language['language_title']."' id='".$language['language_id']."' direction='".$language['dir']."'>\n";
            foreach($phrases as $phraseId => $phrase) {
                $content .= "<translation id='".$phraseId."'><![CDATA[".$phrase."]]></translation>\n";
            }
            $content .= "</language>\n";
            $fileName = path('storage/temp/xml/'.time().'generated-language.xml');
            if (!is_dir(path('storage/temp/xml/'))) mkdir(path('storage/temp/xml/'), 0777, true);
            $r = fopen($fileName, 'w+');
            fwrite($r, $content);
            fclose($r);

            return download_file($fileName);
            break;
        case 'delete':
            $id = input("id");
            delete_language($id);
            redirect_to_pager("admin-languages");
            break;
        case 'activate':
            $id = input("id");
            activate_language($id);
            redirect_to_pager("admin-languages");
            break;
        default:
            $app->setTitle(lang('manage-language-packs'));
            $content = view("language/list");
            break;
    }
    return $app->render($content);
}

function phrases_pager($app) {
    $action = input("action", "list");
    $content = "";
    $app->setTitle(lang('manage-language-phrases'));
    switch($action) {
        case 'update':
            update_all_language_phrases();
            redirect_back();
            break;
        default:
            $id = input('id', get_active_language());
            $val = input("val");
            $term = input('term');
            if ($val) {
		CSRFProtection::validate();
                save_language_phrases($val, input('lang_id'));
            }
            $content = view("language/phrase/content", array("id" => $id, 'phrases' => get_all_language_phrases($id, $term)));
            break;
    }
    return $app->render($content);
}
 