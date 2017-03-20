function toogleCommentIndicator(c) {
    var i = c.find('.comment-editor-indicator');
    if (i.css('display') == 'none') {
        i.fadeIn();
    } else {
        i.fadeOut();
    }
}
function delete_comment(id) {
    var c = $(".comment-" + id);
    var b = $("#comment-remove-button-" + id);
    c.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'comment/delete?id=' + id + '&csrf_token=' + requestToken,
        type: 'GET',
        success : function(data) {
            if (data == 1) {
                c.fadeOut();
            } else {
                c.css('opacity', 1);
            }
        },
        error : function() {
            c.css('opacity', 1);
        }
    })
    return false;
}

function resent_comment_form(form) {
    form.find('textarea').val('').css('height', '30px');
    form.find('input[type=file]').val('');
    form.find('.comment-editor-footer').fadeOut();
    //form.find('.comment-editor-indicator').hide();

}

function show_comment_add_error(form, message) {

    var o = form.find('.alert-warning');
    if (message == 'default') message = o.data('error');
    notifyError(message);
}

function show_more_comment(type, typeId, indicator) {
    var c = $('.comment-lists-' + type + '-' + typeId);
    var indicator = $('#' + indicator);
    indicator.fadeIn();
    var offset = c.data('offset');
    var limit = c.data('limit');
    $.ajax({
        url : baseUrl + 'comment/more?type=' + type + '&type_id=' + typeId + '&offset=' + offset + '&limit=' + limit + '&csrf_token=' + requestToken,
        type : 'GET',
        dataType :'html',
        success : function(data) {
            json = jQuery.parseJSON(data);
            c.each(function() {
                c.data('offset', json.offset);
            })
            if (json.comments == '') {
                indicator.hide();
                $(".comment-view-more-button-" +type+'-' + typeId).hide();
            } else {
                c.prepend(json.comments);
                c.each(function() {

                })
                indicator.hide();
                reloadInits();
            }
        }
    })
    return false;
}

function edit_comment(id) {
    var c = $(".comment-" + id);
    var form = c.find('.comment-edit-form');
    if (form.css('display') == 'none') {
        form.fadeIn();
    } else {
        form.fadeOut();
    }
    return false;
}

function save_comment(id, gid) {
    var c = $(".comment-" + id);
    var cG = $(".comment-" + gid);
    var form = cG.find('.comment-edit-form');
    var indicator = form.find('.comment-edit-form-indicator');
    form.ajaxSubmit({
        url : baseUrl + 'comment/save?id=' + id,
        type : 'POST',
        beforeSend : function() {
            indicator.fadeIn();
            form.css('opacity', '0.5');
        },
        success : function(r) {
            if (r != '0') {
                c.find('.comment-text-content').html(r);
                form.hide();
            }
            indicator.hide();
            form.css('opacity', 1);
        },
        error : function() {
            indicator.hide();
            form.css('opacity', 1);
        }
    })
    return false;
}

function show_comment_replies(id, gId) {
    var container = $(".comment-replies-" + gId);
    var repliesLink = $(".comment-replies-" + gId + " .load-replies-link");
    if (repliesLink.length > 0) {
        repliesLink.find('img').fadeIn();
    }

    var editor = container.find('.comment-editor');
    editor.fadeIn();
    $.ajax({
        url : baseUrl + 'comment/load/replies?id=' + id + '&csrf_token=' + requestToken,
        success : function(data) {
            container.find('.comment-lists').html(data).css("padding", "10px 0");
            container.find('.comment-view-more-button').show();
            if (repliesLink.length > 0) {
                repliesLink.remove();
            }
            reloadInits();
        }
    })
    return false;
}

$(function() {

    $(document).on('focus', ".comment-editor  textarea", function() {
        $(this).css('height', '50px').data('height', '50px');
        var target = $($(this).data('target'));
        target.find('.comment-editor-footer').fadeIn();
    });

    $(document).on('submit', ".comment-editor", function() {
        var text = $(this).find('textarea');
        var imageInput = $(this).find('input[type=file]');
        var form = $(this);
        if (text.val() == '' && imageInput.val() == '') {
            show_comment_add_error(form, 'default');
            return false
        };
        var commentList = $(".comment-lists-" + $(this).data('type') + '-' +$(this).data('type-id'));
        toogleCommentIndicator(form);

        $(this).ajaxSubmit({
            url : baseUrl + 'comment/add',
            type : 'POST',
            dataType : 'json',
            success : function(data) {
                var json = data;
                if (json.status == 0) {
                    show_comment_add_error(form,json.message);
                } else {
                    div = $("<div style='display: none'></div>");
                    div.html(json.comment);
                    //commentList.append(div);
                    $(".comment-lists-" + form.data('type') + '-' + form.data('type-id')).each(function() {
                        $(this).append(json.comment);
                        //alert(".comment-lists-" + form.data('type') + '-' + form.data('type-id'))
                    });
                    $(".comment-count-"+form.data('type') + '-' + form.data('type-id')).each(function() {
                        $(this).html(json.count);
                    })
                    notifySuccess(json.message);

                    resent_comment_form(form);
                    reloadInits();
                }

                toogleCommentIndicator(form);
            },
            error : function() {
                toogleCommentIndicator(form);
            }
        });
        return false;
    });

    $(document).on('mouseover', '.comment', function () {
        var commentId = $(this).data('id');
        $('.comment-actions-button-' + commentId).each(function() {
            $(this).show();
        })
    });

    $(document).on('mouseout', '.comment', function () {
        var commentId = $(this).data('id');
        $('.comment-actions-button-' + commentId).each(function() {
            $(this).hide();
        })
    });
})