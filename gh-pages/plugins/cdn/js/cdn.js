function change_cdn_engine(s) {
    var $s = $(s);
    var $v = $s.val();
    if ($v == '') {
        $('.cdn-settings').fadeOut();
    } else {
        $('.cdn-settings').fadeOut();
        $("#" + $v + "-server").fadeIn();
    }
}