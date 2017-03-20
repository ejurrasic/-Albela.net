<?php
get_menu('admin-menu', 'appearance')->setActive()->findMenu('emoticons')->setActive();
function lists_pager($app) {
    $app->setTitle(lang('emoticons::emoticons'));
    $type = input('type', 'emoticons');
    return $app->render(view('emoticons::list', array('type' => $type, 'lists' => list_emoticons($type))));
}

function add_pager($app) {
    $app->setTitle(lang('emoticons::add-emoticons'));
    $val = input('val');
    $message = null;
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'title' => 'required',
            'symbol' => 'required|unique:emoticons',
        ));
        if (validation_passes()) {
            if (input_file('icon')) {
                $uploader = new Uploader(input_file('icon'), 'image');
                $uploader->setPath('/emoticons/');
                if ($uploader->passed()) {
                    $image = $uploader->uploadFile()->result();
                    $val['icon'] = $image;
                } else {
                    $message = $uploader->getError();
                }
            }
            if (!$message) {
                add_emoticon($val);
                return redirect(url_to_pager("admincp-emoticons")."?type=".(($val['category'] == 1) ? 'emoticons' : 'stickers'));
            }
        } else {
            $message = validation_first();
        }

    }
    return $app->render(view('emoticons::add', array('message' => $message)));
}

function manage_pager($app) {
    $app->setTitle(lang('emoticons::edit-emoticon'));
    $type = input('type', 'edit');
    $id = input('id');
    $emoticon = get_emoticon($id);
    if (!$emoticon) return redirect_to_pager("admincp-emoticons");

    switch($type) {
        case 'remove':
            delete_emoticon($emoticon['id']);
            return redirect_to_pager("admincp-emoticons");
            break;
        default :
            $val = input('val');
            $message = null;
            if ($val) {
		CSRFProtection::validate();
                $validator = validator($val, array(
                    'title' => 'required',
                    'symbol' => 'required',
                ));
                if (validation_passes()) {
                    if (input_file('icon')) {
                        $uploader = new Uploader(input_file('icon'), 'image');
                        $uploader->setPath('/emoticons/');
                        if ($uploader->passed()) {
                            $image = $uploader->uploadFile()->result();
                            $val['icon'] = $image;
                        } else {
                            $message = $uploader->getError();
                        }
                    }
                    if (!$message) {
                        save_emoticon($val, $emoticon['id']);
                        return redirect(url_to_pager("admincp-emoticons")."?type=".(($val['category'] == 1) ? 'emoticons' : 'stickers'));
                    }
                } else {
                    $message = validation_first();
                }

            }
            return $app->render(view('emoticons::edit', array('emoticon' => $emoticon)));
            break;
    }
}
 