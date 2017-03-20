<?php
function annoucement_pager($app)
{
    $message = '';
    load_functions('admin_annoucement_page');

    /*perform  actions based on verbose method*/
    if(get_request_method() == 'POST' && isset($_POST['_method']))
    {
        if(strtolower( $_POST['_method'] ) == 'delete'){
            $message =  deleteAnnoucement($_POST['token']);
            return redirect_to_pager("annoucement",array('message' => $message));
        }
    }

    if(isset($_POST) && !empty($_POST))
    {
        $message = createAnnoucement($_POST);
    }
    $annoucements = getAnnoucements();

    return $app->render(view('notification/annoucement',array('annoucements' => $annoucements,'message' => $message)));
}