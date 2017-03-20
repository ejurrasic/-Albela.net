<?php
function report_pager($app) {
    CSRFProtection::validate(false);
    add_report(input('val'));
}
 