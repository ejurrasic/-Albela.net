function upload_group_profile_cover() {
    toggle_profile_cover_indicator(true);
    var id = $('#group-profile-container').data('id');
    $("#profile-cover-change-form").ajaxSubmit({
        url : baseUrl + 'group/change/cover?id=' + id,
        success: function(data) {
            var result = jQuery.parseJSON(data);
            if (result.status == 0) {
                notifyError(result.message);
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

function save_group_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var id = $('#group-profile-container').data('id');
    var width = $('#group-profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url : baseUrl + 'group/cover/reposition?pos=' + i + '&id=' + id+'&width=' + width + '&csrf_token=' + requestToken,
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

function remove_group_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    var id = $('#group-profile-container').data('id');
    $.ajax({
        url : baseUrl + 'group/cover/remove?id=' + id + '&csrf_token=' + requestToken,
    });
    return false;
}

function upload_group_logo() {
    var form = $("#group-profile-image-form");
    show_profile_image_indicator(true);
    var id = form.data('id');
    form.ajaxSubmit({
        url : baseUrl + 'group/change/logo?id=' + id,
        success : function(data) {
            data = jQuery.parseJSON(data);
            show_profile_image_indicator(false);
            if (data.status) {
                $(".profile-image").attr('src', data.image);
            } else {
                alertDialog(data.message);
            }
            form.find('input[type=file]').val('')
        },
        uploadProgress : function(event, position, total, percent) {
            var uI = $(".profile-image-indicator .percent-indicator");
            uI.html(percent + '%').fadeIn();

        },
        error : function() {
            show_profile_image_indicator(false);
            alertDialog("An error occurred");
            form.find('input[type=file]').val('')
        }
    })
}

function join_group(t) {
    var obj = $(t);
    var status = obj.data('status');
    var id = obj.data('id');
    obj.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'group/join?id=' + id + '&status=' + status + '&csrf_token=' + requestToken,
        success : function() {
            //only to reload the page
            window.location = window.location;
        },
        error : function() {
            obj.css('opacity', 1);
        }
    })

    return false;
}

function process_group_role(t, id, uid) {
    var obj = $(t);
    obj.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'group/member/role?id=' + id + '&uid=' + uid + '&v=' + obj.val() + '&csrf_token=' + requestToken,
        success : function(data) {
            notifySuccess(data);
            obj.css('opacity', 1);
        }
    })
}

$(function() {
    $(document).on('click', '#group-tags-suggestion a', function() {
        $('#group-tags-suggestion').hide();
        var uid = $(this).data('id');
        var id = $('#group-tags-suggestion').data('id');
        $('#group-tags-suggestion-input').val('')
        $.ajax({
            url : baseUrl + 'group/add/member?id=' + id + '&uid=' + uid + '&csrf_token=' + requestToken,
            success : function(data) {
                notifySuccess(data);
            }
        })
        return false;
    });
})


function group_set_list_type(type) {
    $.ajax({url: baseUrl + 'group/ajax?action=set_list_type&type=' + type + '&csrf_token=' + requestToken});
}