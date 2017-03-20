<?php
function support_category_edit_pager($app){

    load_functions('support');
    $message = '';
    /*perform  actions based on verbose method*/
    if(isset($_POST['_method']))
    {
        if(strtolower( $_POST['_method'] ) == 'update'){
            $message =  updateCategory($_POST,$_POST['token']);
        }
        if(isset($_POST['_method']) && strtolower( $_POST['_method'] ) == 'delete'){
            $message =  deleteCategory($_POST['token']);
            return redirect_to_pager("support",array('message' => $message));
        }
    }

    $token   = segment(4);
    $category =  editCategory($token);
    $categories = getCategories();

    return $app->render(view('support/support-category-edit',array('detail' => $category,'message' => $message,'Categories' => $categories)));

}