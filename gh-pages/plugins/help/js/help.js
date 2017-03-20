function help_open_menu(t) {
    var o = $(t);
    var ul = o.next();
    if (ul.css('display') == 'none') {
        ul.slideDown();
    } else {
        ul.slideUp();
    }

    return false;
}