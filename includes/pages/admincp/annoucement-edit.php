<?php
function annoucement_edit_pager($app)
{
    load_functions('admin_annoucement_page');
    $message = '';
    /*perform  actions based on verbose method*/
    if(isset($_POST['_method']))
    {
        if(strtolower( $_POST['_method'] ) == 'update'){
            $message =  updateAnnoucement($_POST,$_POST['token']);
        }
    }

    $token   = segment(3);
    $annoucement =  editAnnoucement($token);

    return $app->render(view('notification/annoucement-edit',array('annoucement' => $annoucement,'message' => $message)));
}