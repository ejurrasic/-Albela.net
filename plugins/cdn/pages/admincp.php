<?php
get_menu("admin-menu", "tools")->setActive(true);
function lists_pager($app) {
    $app->setTitle(lang('cdn::cdn-servers'));

    return $app->render(view("cdn::admincp/lists", array("servers" => list_cdn_servers())));
}

function manage_pager($app) {
    $action = input('action');
    $id = input('id');

    switch($action) {
        case 'edit':
            $server = cdn_get_server($id);
            $app->setTitle(lang('cdn::cdn-servers'));
            $val = input('val');
            $message = null;
            if ($val) {
		    CSRFProtection::validate();
                $validator = validator($val, array(
                    'name' => 'required',
                    'engine' => 'required'
                ));
                if (validation_passes()) {
                    $engineStr = $app->cdnEngine[input('val.engine')]['engine'];
                    $engine = new $engineStr();
                    if ($engine->validateSettings(input('val.'.$engineStr))) {
                        cdn_save_server($val, $id);
                        forget_cache("cdn-lists");
                        forget_cache("cdn-server-". $id);
                        redirect(url('admincp/cdn/servers'));
                    } else  {
                        $message = $engine->validationError();
                    }
                } else  {
                    $message = validation_first();
                }
            }

            return $app->render(view("cdn::admincp/edit", array('server' => $server, 'message' => $message)));
            break;
        case 'enable':
            db()->query("UPDATE cdn_servers SET status='1' WHERE id='{$id}'");
            forget_cache("cdn-lists");
            forget_cache("cdn-server-". $id);
            redirect_back();
            break;
        case 'disable':
            db()->query("UPDATE cdn_servers SET status='0' WHERE id='{$id}'");
            forget_cache("cdn-lists");
            forget_cache("cdn-server-". $id);
            redirect_back();
            break;
        case 'delete':
            db()->query("DELETE FROM cdn_servers  WHERE id='{$id}'");
            forget_cache("cdn-lists");
            forget_cache("cdn-server-". $id);
            redirect_back();
            break;
    }
}
function add_pager($app) {
    $app->setTitle(lang('cdn::cdn-servers'));
    $val = input('val');
    $message = null;
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'name' => 'required',
            'engine' => 'required'
        ));
        if (validation_passes()) {
            $engineStr = $app->cdnEngine[input('val.engine')]['engine'];
            $engine = new $engineStr();
            if ($engine->validateSettings(input('val.'.$engineStr))) {
                cdn_add_server($val);
                redirect(url('admincp/cdn/servers'));
            } else  {
                $message = $engine->validationError();
            }
        } else  {
            $message = validation_first();
        }
    }
    return $app->render(view("cdn::admincp/add", array("message" => $message)));
}
 