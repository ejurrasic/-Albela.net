function upload_event_profile_cover() {
    toggle_profile_cover_indicator(true);
    var id = $('#event-profile-container').data('id');
    $("#profile-cover-change-form").ajaxSubmit({
        url : baseUrl + 'event/change/cover?id=' + id,
        success: function(data) {
            var result = jQuery.parseJSON(data);
            if (result.status == 0) {
                alert(result.message);
            } else {
                var img = result.image;
                $('.profile-cover-wrapper img').attr('src', img);
                $('.profile-resize-cover-wrapper img').attr('src', result.original);
                reposition_user_profile_cover();
            }
            toggle_profile_cover_indicator(false);
        }
    })
}

function save_event_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var id = $('#event-profile-container').data('id');
    var width = $('#event-profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url : baseUrl + 'event/cover/reposition?pos=' + i + '&id=' + id+'&width=' + width + '&csrf_token=' + requestToken,
            success: function(data) {
                console.log(data);
                $('.profile-cover-wrapper img').attr('src', data);
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            },
            error : function() {
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            }
        })
    }
    return false;
}

function remove_event_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    var id = $('#event-profile-container').data('id');
    $.ajax({
        url : baseUrl + 'event/cover/remove?id=' + id + '&csrf_token=' + requestToken,
    });
    return false;
}

function event_invite_friend(t, userid, id) {
    var o = $('.event-invite-user-' + userid);
    o.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'event/invite/user?id=' + id + '&userid=' + userid + '&csrf_token=' + requestToken,
        success : function(data) {
            $('.event-invited-stats').html(data);
            o.fadeOut();
        },
        error : function() {
            o.css('opacity', 1);
        }
    })
    return false;

}

function event_search_invite_friend(i) {
    var input = $(i);
    var container = $(".event-invite-friends-list");

    if (input.val().length > 1) {

        $.ajax({
            url : baseUrl + 'event/invite/search?id=' + container.data('id') + '&term=' + input.val() + '&csrf_token=' + requestToken,
            success : function(data) {
                container.html(data);
            }
        })
    }
}

function event_rsvp(t, id) {
    var s = $(t);
    s.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'event/rsvp?id=' + id + '&v=' + s.val() + '&csrf_token=' + requestToken,
        success : function(d) {
            var json = jQuery.parseJSON(d);
            $(".event-going-stats").html(json.going);
            $(".event-maybe-stats").html(json.maybe);
            $(".event-invited-stats").html(json.invited);
            s.css('opacity', 1);
        },
        error : function() {
            s.css('opacity', 1);
        }
    })
}