<?php
function logout_pager($app) {
    logout_user();
    redirect(url());
}