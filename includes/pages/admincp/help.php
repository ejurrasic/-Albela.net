<?php
function support_pager($app){
    $message = '';
    load_functions('support');
    if(isset($_POST) && !empty($_POST))
    {
        if(isset($_POST['_which']) && $_POST['_which'] ==  'category')
        {
            if(isset($_POST['_method']) && $_POST['_method'] == 'DELETE')
            {
                $message = deleteCategory($_POST['token']);
            }
            else{ $message = createCategory($_POST); }
        }
        if(isset($_POST['_which']) && $_POST['_which'] ==  'support')
        {
            $message = createSupport($_POST);
        }
    }

    $Categories = getCategories();
    return $app->render(view('support/main',array('message' => $message,'Categories' => $Categories)));
}