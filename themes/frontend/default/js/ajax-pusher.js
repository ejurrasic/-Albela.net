function ajax_poll_check() {
    $.ajax({
        url : baseUrl + 'ajax/push/check?csrf_token=' + requestToken,
        success : function(r) {
            //process data
            if (r) {
                var json = jQuery.parseJSON(r);
                if (json.seen == 0) Pusher.onAlert();
                Pusher.setUser(json.userid);
                if (json.types) {
                    $.each(json.types, function (i, d) {
                        Pusher.run(i, d);
                    });
                }

                Pusher.finish();
            }
            //re-initiate again
            setTimeout(function() {
                if (loggedIn) ajax_poll_check();
            }, ajaxInterval);
        },
        error : function() {
            setTimeout(function() {
                if (loggedIn) ajax_poll_check();
            }, ajaxInterval);
        }
    })
}

$(function() {
    if (loggedIn) ajax_poll_check();
})