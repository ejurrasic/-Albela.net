function upload_game_profile_cover() {
    toggle_profile_cover_indicator(true);
    var id = $('#game-profile-container').data('id');
    $("#profile-cover-change-form").ajaxSubmit({
        url : baseUrl + 'game/change/cover?id=' + id,
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

function save_game_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var id = $('#game-profile-container').data('id');
    var width = $('#game-profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url : baseUrl + 'game/cover/reposition?pos=' + i + '&id=' + id+'&width=' + width + '&csrf_token=' + requestToken,
            success: function(data) {
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

function remove_game_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    var id = $('#game-profile-container').data('id');
    $.ajax({
        url : baseUrl + 'game/cover/remove?id=' + id + '&csrf_token=' + requestToken,
    });
    return false;
}

$(function() {

})
