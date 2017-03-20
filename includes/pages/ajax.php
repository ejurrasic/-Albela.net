<?php
function check_pager($app) {
    CSRFProtection::validate(false);
    return pusher()->result();
}
 