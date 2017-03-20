function process_follow(userid) {
    var o = $("#follow-button-" + userid);
    var status = o.attr('data-status');
    var follow = o.data('follow');
    var unfollow = o.data('unfollow');
    if (status == 1) {
        //user want to unfollow
        o.removeClass('followed').html(follow).attr('data-status', 0);
        type = 'unfollow';
    } else {
        //user want to follow
        o.addClass('followed').html(unfollow).attr('data-status', 1);
        type = 'follow'
    }

    $.ajax({
        url : baseUrl + 'relationship/follow?type=' + type + '&userid=' + userid + '&csrf_token=' + requestToken,
    })
    return false;
}

function process_friend(userid) {
    var o = $(".friend-button-" + userid);
    var status = o.attr('data-status');
    var addText = o.data('add');
    var sentText = o.data('sent');

    if (status == 0) {
        //adding user

        o.css('opacity', '0.4');

        $.ajax({
            url : baseUrl + 'relationship/add/friend?userid=' + userid + '&csrf_token=' + requestToken,
            success : function(data) {
                if (data == 1) {
                    o.find('span').html(sentText);
                    o.attr('data-status', 1);
                }
                o.css('opacity', 1);
            }
        })
    } else if(status == 1 || status == 2) {
        //canceling friend or request made
        message = (status == 1) ? o.data('cancel-warning') : o.data('remove-warning');
        confirm.action(function() {
            o.css('opacity', '0.4');
            $.ajax({
                url : baseUrl + 'relationship/remove/friend?userid=' + userid + '&csrf_token=' + requestToken,
                success: function(data) {
                    if (data == 1) {
                        o.find('span').html(addText);
                        o.removeClass('ready-friend').attr('data-status', 0);
                    }
                    o.css('opacity', 1);
                }
            })
        }, message)
    }
    return false;
}

function show_friend_request_dropdown() {
    var dropdown = $(".friend-request-dropdown");
    var indicator = dropdown.find('#friend-request-dropdown-indicator');
    var content = dropdown.find('.friend-request-dropdown-result-container');
    if (dropdown.css('display') == 'none') {
        dropdown.fadeIn();
        indicator.show();
        $.ajax({
            url : baseUrl + 'relationship/load/requests?csrf_token=' + requestToken,
            success : function(data) {
                content.html(data);
                indicator.hide();
            }
        })
    } else {
        dropdown.fadeOut();
    }
    $(document).click(function(e) {
        if(!$(e.target).closest("#friend-request-dropdown-container").length) dropdown.hide();
    });
    return false;
}

function confirm_friend_request(userid, b) {
    var c = $('#friend-request-' + userid);
    var requestButton = $("#friend-request-respond-button-" + userid);
    c.css('opacity', '0.4');
    requestButton.css('opacity', '0.4');
    $("#friend-requests-respond-dropdown-" + userid).hide();
    $.ajax({
        url : baseUrl + 'friend/request/confirm?userid=' + userid + '&csrf_token=' + requestToken,
        type : 'GET',
        success : function(data) {
            if (data == 'login') {
                login_required();
            } else {
                c.css('opacity', 1).find('.actions').fadeOut();
                requestButton.hide();
                var button = $('.friend-button-' + userid);
                var frTrans = button.data('friends');
                button.show().attr('data-status', '2').html(frTrans).addClass('ready-friend');
            }
        }
    })
    return false;
}

function delete_friend_request(userid, b) {
    var c = $('#friend-request-' + userid);
    var requestButton = $("#friend-request-respond-button-" + userid);
    c.css('opacity', '0.4');
    requestButton.css('opacity', '0.4');
    $("#friend-requests-respond-dropdown-" + userid).hide();
    $.ajax({
        url : baseUrl + 'relationship/remove/friend?userid=' + userid + '&csrf_token=' + requestToken,
        success: function(data) {
            if (data == 1) {
                c.slideUp();
                requestButton.hide();
                var button = $('.friend-button-' + userid);
                var frTrans = button.data('add');
                button.show().attr('data-status', '0').html(frTrans);
            } else {
                login_required();
            }

        }
    });
    return false;
}
function push_friend_requests(type, d) {
    if (type == 'friend-request') {
        var notyCounts = 0;
        var a = $("#friend-request-dropdown-container > a");
        if (!a.find('span').length) {
            a.append("<span class='count' style='display:none'></span>")
        }
        var span = a.find('span');
        var nIds = '';
        $.each(d, function(pushId, nId) {
            if (!Pusher.hasPushId(pushId)) {
                Pusher.addPushId(pushId);
                nIds += (nIds) ? ',' + nId : nId;
            }
            notyCounts +=1;
        });

        if (notyCounts > 0) {
            span.html(notyCounts).fadeIn();
            Pusher.addCount(notyCounts);
        }

        a.click(function() {
            Pusher.removeCount(notyCounts);
            span.hide();
        });
        if (nIds) {
            $.ajax({
                url : baseUrl + 'friend/requests/preload?csrf_token=' + requestToken,
                success : function(data) {
                    var c = $(".friend-request-dropdown-result-container");
                    c.prepend(data);
                    reloadInits();
                }
            })
        }
    }
}
Pusher.addHook('push_friend_requests');