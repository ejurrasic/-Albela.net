function upload_page_profile_cover() {
    toggle_profile_cover_indicator(true);
    var id = $('#page-profile-container').data('id');
    $("#profile-cover-change-form").ajaxSubmit({
        url : baseUrl + 'page/change/cover?id=' + id,
        success: function(data) {
            var result = jQuery.parseJSON(data);
            if (result.status == 0) {
                alert(result.message);
            } else {
                var img = result.image;
                $('.profile-cover-wrapper img').attr('src', img);
                $('.profile-resize-cover-wrapper img').attr('src', result.original);
                $("#profile-cover-viewer").data('id', result.id);
                $("#profile-cover-viewer").data('image', result.original);
                $("#profile-cover-viewer").addClass('photo-viewer');
                reposition_user_profile_cover();
            }
            toggle_profile_cover_indicator(false);
        }
    })
}

function save_page_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var id = $('#page-profile-container').data('id');
    var width = $('#page-profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url : baseUrl + 'page/cover/reposition?pos=' + i + '&id=' + id+'&width=' + width + '&csrf_token=' + requestToken,
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

function remove_page_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    var id = $('#page-profile-container').data('id');
    $.ajax({
        url : baseUrl + 'page/cover/remove?id=' + id + '&csrf_token=' + requestToken,
    });
    return false;
}

function upload_page_logo() {
    var form = $("#page-profile-image-form");
    show_profile_image_indicator(true);
    var id = form.data('id');
    form.ajaxSubmit({
        url : baseUrl + 'page/change/logo?id=' + id,
        success : function(data) {
            data = jQuery.parseJSON(data);
            show_profile_image_indicator(false);
            if (data.status) {
                $(".profile-image").attr('src', data.image);
                $("#profile-image-viewer").data('id', data.id);
                $("#profile-image-viewer").data('image', data.large);
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

function page_invite_friend(l, u, p) {
    var obj = $(l);
    obj.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'page/invite/friend?page=' + p + '&user=' + u + '&csrf_token=' + requestToken,
        success : function(data) {
            obj.remove();
        },
        error : function() {
            obj.css('opacity', 1);
        }
    });
    return false;
}

function page_search_invite_friend(i) {
    var input = $(i);
    var container = $(".invite-friends-list");
    if (input.val().length > 1) {
        $.ajax({
            url : baseUrl + 'page/invite/search?page=' + container.data('id') + '&term=' + input.val() + '&csrf_token=' + requestToken,
            success : function(data) {
                container.html(data);
            }
        })
    }
}

function page_hook_page_loaded() {
    var container = $(".invite-friends-list");
    //container.slimScroll({height: '200px'});
    container.slimScroll().bind('slimscroll', function(e, pos) {
        if (pos == 'bottom') {
            $.ajax({
                url : baseUrl + 'page/more/invite?offset=' + container.data('offset') + '&id=' + container.data('id') + '&csrf_token=' + requestToken,
                success : function(data) {
                    var json = jQuery.parseJSON(data);
                    container.append(json.users);
                    container.attr('data-offset', json.offset);
                    container.data('offset', json.offset);
                }
            })
        }
    });
}

addPageHook('page_hook_page_loaded');
$(function() {
    page_hook_page_loaded()
    $(document).on("click", "#page-user-role-suggestion a", function() {
        var o = $(this);
        var userid = o.data('id');
        if ($("#role-" + userid).length) {
            $("#page-user-role-suggestion").hide();
            return false;
        }
        var div = $("<div data-saved='false' id='role-"+userid+"' style='display: none' class='media media-lg'></div>");
        div.html($("#page-role-template").html())
        div.find(".media-object").html(o.find('.media-object'));
        div.find(".media-heading").html(o.find('.media-heading'));
        div.find('select').prop('name', 'val['+ o.data('id')+']');
        div.find('.role-delete-button').attr('data-userid', userid);
        $("#page-role-lists").append(div);
        div.fadeIn(500);
        $("#page-user-role-suggestion").hide();
        return false;
    });

    $(document).on('click', ".role-delete-button", function() {
        var pageId = $(this).data('page-id');
        var userid = $(this).attr('data-userid');
        var role = $("#role-" + userid);
        if (role.attr('data-saved') == 'false') {
            role.slideUp().remove();
            return false;
        }
        confirm.action(function() {
            role.css('opacity', '0.7');
            $.ajax({
                url : baseUrl + 'page/role/remove?csrf_token=' + requestToken,
                data: {page:pageId,user:userid},
                success : function() {
                    role.slideUp().remove();
                },
                error : function() {
                    role.css('opacity', 1);
                }
            })
        });
        return false;
    })
});

function page_set_list_type(type) {
    $.ajax({url: baseUrl + 'page/ajax?action=set_list_type&type=' + type + '&csrf_token=' + requestToken});
}