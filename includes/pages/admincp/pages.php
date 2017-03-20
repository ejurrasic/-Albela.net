<?php

function manage_pager($app) {
    $app->setTitle(lang('static-pages'));

    return $app->render(view('pages/lists', array('pages' => get_static_pages())));
}

function edit_pager($app) {
    $app->setTitle(lang('static-pages'));
    $action = input('action');
    switch($action) {
        case 'edit':
            $id = input('id');
            $page = get_static_page($id);
            if (!$page) redirect_back();
            $message = null;
            $val = input('val', null, array('content'));

            if ($val) {
		        CSRFProtection::validate();
                save_static_page($val, $page);
                return  redirect_to_pager('manage-statics');
            }
            return $app->render(view('pages/edit', array('message' => $message, 'page' => $page)));
            break;
        case 'delete':
            delete_static_page(input('id'));
            return  redirect_back();
            break;
    }
}

function add_pager($app) {
    $app->setTitle(lang('add-new-page'));
    $message = null;
    $val = input('val', null, array('content'));
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'content' => 'required'
        ));

        if (validation_passes()) {
            $titles = input('val.title');
            $english = $titles['english'];
            if ($english) {
                $slug = toAscii($english);
                if (empty($slug)) $slug = md5(time());
                if (static_page_exists($slug)) {
                    $message = lang('page-already-exists');
                } else {
                    //we are good to go
                    $val['slug'] = $slug;
                    add_static_page($val);
                    return  redirect_to_pager('manage-statics');
                }
            } else {
                $message = lang('title-field-required');
            }

        } else {
            $message = validation_first();
        }
    }

    return $app->render(view('pages/add', array('message' => $message)));
}
 