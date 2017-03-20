<?php
function support_edit_pager($app){

    load_functions('support');
    $message = '';
    /*perform  actions based on verbose method*/
    if(isset($_POST['_method']))
    {
        if(strtolower( $_POST['_method'] ) == 'update'){
            $message =  updateSupport($_POST,$_POST['token']);
        }
        if(isset($_POST['_method']) && strtolower( $_POST['_method'] ) == 'delete'){
            $message =  deleteSupport($_POST['token']);
            return redirect_to_pager("support",array('message' => $message));
        }
    }

    $token   = segment(3);
    $support =  editSupport($token);
    $categories = getCategories();

    return $app->render(view('support/support-edit',array('support' => $support,'message' => $message,'Categories' => $categories)));

}