function membership_admin_change_form(s) {
    var s = $(s);
    var v = s.val();
    if (v == 'one-time' || v == 'recurring') {
        $(".plan-price").fadeIn();
        if (v == 'recurring') {
            $(".recurring-container").fadeIn();
        } else {
            $(".recurring-container").fadeOut();
        }
    } else {
        $(".plan-price").fadeOut();
        $(".recurring-container").fadeOut();
    }
}