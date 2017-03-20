/**
 * Feed Editor scripting
 *
 * **/
var feedEditor = {
    hasUpload : false,
    uploadType : '',
    actionCount : 0,
    hasLink: false,
    processedLink : '',
    processingLink : false,

    init : function() {
        this.processEditorPrivacyDropdown();
        $(document).on("submit", "#feed-editor-form", function(e) {
            //e.preventDefault();
            feedEditor.post_feed($(this));
            return false;
        });

        $(document).on('click', '#feed-editor-menu-item-image', function() {
            return file_chooser('#feed-editor-image-input');
        });

        $(document).on('click', '#feed-editor-menu-item-video', function() {
            return file_chooser('#feed-editor-video-input');
        });

        $(document).on('click', '#feed-tags-suggestion a', function() {
            feedEditor.addTag($(this));
            return false;
        });

        $(document).on('click', '#feed-editor-tags-container .user a', function() {
            feedEditor.removeTag($(this));
            return false;
        });

        $(document).on('click', '.feed-privacy-toggle', function() {
            var p = $(this).data('id');
            var feed = $(this).data('feed');
            var icon = $(this).data('icon');
            var link = $("#feed-privacy-icon-" + feed);
            link.html("<i class='"+icon+"'></i>");
            link.dropdown("toggle");
            //$("#feed-privacy-dropdown-" + feed).hide();
            $.ajax({
                url: baseUrl + 'feed/update/privacy?id=' + feed + '&privacy=' + p + '&csrf_token=' + requestToken,
            })
            return false;
        });

        $(document).on('click', '.feed-feeling-trigger', function(e) {
            e.stopPropagation();
            var c = $(".feed-editor-feeling-container");
            if (c.css('display') == 'none') {
                c.fadeIn();
                c.find("#dropdown-link").dropdown("toggle");
                c.find(".feeling-right input[type=text]").focus();
            } else {
                c.fadeOut();
            }
            return false;
        })

        $(document).on('keyup', '#feed-editor-textarea', function() {
            var str = $(this).val();
            if (str == '') {
                feedEditor.hasLink = false;
                feedEditor.processedLink = '';
                feedEditor.processingLink = false;
            }
            if (feedEditor.hasLink || feedEditor.processingLink) return false;

            var container = $("#feed-editor-link-container");
            var indicator = container.find('.link-indicator');
            var content = container.find('.link-content');
            content.html('');
            var split = str.split(" ");
            if (split.length > 0) {
                var foundLink = searchTextForLink(str);
                if (foundLink != '' && foundLink != feedEditor.processedLink) {
                    feedEditor.processingLink = true;
                    container.fadeIn();
                    indicator.fadeIn();
                    $.ajax({
                        url : baseUrl + 'feed/link/get?csrf_token=' + requestToken,
                        type : 'POST',
                        cache : false,
                        data : {link : foundLink},
                        success : function(data) {
                            if (data) {
                                feedEditor.processingLink = false;
                                feedEditor.hasLink = true;
                                feedEditor.processedLink = foundLink;
                                indicator.hide();
                                content.html(data);
                            } else {
                                feedEditor.hasLink = false;
                                feedEditor.processedLink = '';
                                feedEditor.processingLink = false;
                            }
                        },
                        error : function() {
                            container.hide();
                            indicator.hide();
                            feedEditor.hasLink = false;
                            feedEditor.processedLink = '';
                            feedEditor.processingLink = false;
                        }
                    })
                }
            }
        });
    },

    addOptions: function() {
        $(".poll-options-container").append('<div class="options"><i class="ion-ios-plus-outline"></i> <input type="text" name="val[poll_options][]"/><a href="" onclick=" return feedEditor.remove_poll_option(this)" class="close"><i class="ion-android-close"></i></a></div>');
        return false;
    },

    remove_poll_option : function(t) {
        var c = $(t).parent();
        c.remove();
        return false;
    },

    openPoll: function(th) {
        var c = $(".feed-editor-poll-container");
        var i = $("#feed-poll-enable-input");
        var t = $("#feed-editor-textarea");
        var o = $(th);
        if (c.css('display') == 'none') {
            c.fadeIn();
            i.val(1);
            t.val("").prop("placeholder", o.data('holder'));
        } else {
            c.fadeOut();
            i.val(0);
            t.val("").attr("placeholder", o.data('revert'));
        }
        return false;
    },

    loadFeeling : function(t) {
        t = $(t);
        var type = t.data('type');
        var clone = t.clone();
        clone.find('i').remove();
        var content = clone.html();
        //alert(content);
        var c = $(".feed-editor-feeling-container");
        c.find("#dropdown-link").html(content)
        c.find(".feeling-right input[type=text]").focus().val("");
        $("#feed-editor-feeling-type").val(type);
        c.fadeIn();
        $('.feed-editor-feeling-container input[type=text]').val('').show().focus();
        $("#feed-feeling-selected-suggestion").html('');
        return false;

    },

    listenMediaFeeling: function(t) {
        var i = $(t);
        var type = $("#feed-editor-feeling-type").val();
        if (type == 'watching' || type == 'listening-to') {
            if (i.val().length > 0 ) {
                $.ajax({
                    url : baseUrl + 'feed/search/media?type=' + type + '&term=' + i.val() + '&csrf_token=' + requestToken,
                    success : function(data) {
                        if (data) {
                            $("#feed-feeling-suggestion").html(data).fadeIn();
                        } else {
                            $("#feed-feeling-suggestion").hide();
                        }

                        $(document).click(function(e) {
                            if(!$(e.target).closest($("#feed-feeling-suggestion")).length) $("#feed-feeling-suggestion").hide();
                        });
                    }
                })
            }
        }
    },

    insertFeelingMedia: function(t) {
        var l = $(t);
        var c = l.data('content');
        $("#feed-editor-feeling-data").val(c);
        var o = $("<div class='media media-sm'><div class='media-left'><div class='media-object'><img style='width: 30px;height: 20px !important;background: #d3d3d3;border-radius: 3px' src='"+l.data('image')+"'/> </div> </div><div class='media-body'><h6 class='media-heading'>"+ l.data('title')+"</h6><a class='close' onclick='return feedEditor.removeFeelingMedia()' href=''><i class='ion-android-close'></i></a> </div></div> ")
        $("#feed-feeling-selected-suggestion").html(o);
        $("#feed-feeling-suggestion").fadeOut();
        $("#feed-editor-feeling-text").val(l.data('title')).hide();
        return false;
    },

    removeFeeling: function(t) {
        var i = $(t);
        if (i.val() != '')return false;
        //$(".feed-editor-feeling-container").fadeOut();
    },

    removeFeelingMedia: function() {
        $("#feed-feeling-selected-suggestion").html('');
        $("#feed-editor-feeling-text").val('').show().focus();
        $("feed-editor-feeling-data").val("");
        return false;
    },

    removeLinkDetails : function(all) {
        var container = $("#feed-editor-link-container");
        var content = container.find('.link-content');
        feedEditor.hasLink = false;
        if (all) feedEditor.processedLink = '';
        feedEditor.processingLink = false;
        container.fadeOut();
        content.html('');
        return false;
    },
    processEditorPrivacyDropdown : function() {
        $(document).on('click', "#feed-privacy-dropdown li a", function() {
            var h = $(this).find('i').clone();

            var input = $("#feed-editor-privacy");
            //alert($(this).data('id'))
            input.val($(this).data('id'));
            $("#feed-editor-privacy-toggle").html(h);
            $.ajax({
                url : baseUrl + 'feed/editor/privacy?v=' + $(this).data('id') + '&csrf_token=' + requestToken,
            });
        });
    },

    addActionCount : function() {
        this.actionCount = this.actionCount + 1;
    },

    removeActionCount : function() {
        this.actionCount = this.actionCount - 1;
        if (this.actionCount < 0) this.actionCount = 0;
    },

    choose : function(id, type) {
        if (this.hasUpload && this.uploadType != type) return false;
        return file_chooser(id)
    },
    processMedia : function(type) {
        if (type == 'image') {
            var selector = $("#feed-editor-image-selector");
            var span = selector.find('span');
            var imageInput = document.getElementById("feed-editor-image-input");
            if (imageInput.files.length > maxPhotosUpload) {
                alert('Max no of images allowed is ' + maxPhotosUpload);
                return false;
            }
            if (!imageInput.files.length) return  this.removeImage();
            span.html(imageInput.files.length).fadeIn();
            var info = $("#photo-feed-media-selected-info");
            info.find('.count').html(imageInput.files.length);
            info.fadeIn();
            this.hasUpload = true;
            this.uploadType = 'image';
        }  else if (type == 'video') {
            var videoInput = document.getElementById("feed-editor-video-input");
            if (!videoInput.files.length) return  this.removeVideo();
            var selector = $("#feed-editor-video-selector");
            var span = selector.find('span');
            span.html(videoInput.files.length).fadeIn();
            var info = $("#video-feed-media-selected-info");
            info.find('.count').html(videoInput.files.length);
            info.fadeIn();
            this.hasUpload = true;
            this.uploadType = 'video';
        } else if(type == 'file') {
            var fileInput = document.getElementById("feed-editor-file-input");
            if (!fileInput.files.length) return  this.removeFile();
            var selector = $("#feed-editor-file-selector");
            selector.addClass('active');
            var info = $("#file-feed-media-selected-info");
            info.find('.count').html(fileInput.files.length);
            info.fadeIn();
            this.hasUpload = true;
            this.uploadType = 'file';
        }
    },
    removeImage : function() {
        $("#feed-editor-image-input").val('');
        $("#feed-editor-image-selector span").html('').hide();
        $("#photo-feed-media-selected-info").fadeOut();
        this.hasUpload = false;
        this.uploadType = '';
        return false;
    },
    removeVideo : function() {
        $("#feed-editor-video-input").val('');
        $("#feed-editor-video-selector span").html('').hide();
        $("#video-feed-media-selected-info").fadeOut();
        this.hasUpload = false;
        this.uploadType = '';
        return false;
    },
    removeFile : function() {
        $("#feed-editor-file-input").val('');
        $("#feed-editor-file-selector").removeClass('active');
        $("#file-feed-media-selected-info").fadeOut();
        this.hasUpload = false;
        this.uploadType = '';
        return false;
    },
    toggleCheckIn : function() {
        var container = $("#feed-editor-check-in-input-container");
        var selector = $("#feed-editor-check-in-input-selector");
        if (container.css('display') == 'none') {
            container.slideDown();
            selector.addClass('active');
            container.find('input').focus();
        } else {
            container.slideUp();
            if (container.find('input').val() == '') {
                selector.removeClass('active')
            }
        }

        return false;
    },
    removeCheckIn : function() {
        var container = $("#feed-editor-check-in-input-container");
        var selector = $("#feed-editor-check-in-input-selector");
        container.find('input').val('');
        container.slideUp();
        selector.removeClass('active');
        return false;
    },
    showTags : function() {
        var c = $("#feed-editor-tags-container");
        var selector = $("#feed-editor-tags-input-selector");

        if (c.css('display') == 'none') {
            c.slideDown();
            //alert(container.css('display'))
            selector.addClass('active');
            c.find('input[type=text]').focus();
        } else {
            c.slideUp();
            if (c.find('.user').length < 1) {
                selector.removeClass('active')
            }
        }
        return false;
    },
    addTag : function(o) {
        var id = o.data('id');
        var name = o.data('name');
        if ($('#feed-editor-tags-container #user-' + id).length > 0) return false;
        var span = $("<span id='user-"+id+"' class='user'>"+name+"<input type='hidden' name='val[tags][]' value='"+id+"'/><a href=''><i class='ion-close'></i></a></span>");

        var input = $("#feed-editor-tags-container .input-field");
        input.before(span);
        input.find('#feed-tags-suggestion').fadeOut();
        input.find('input[type=text]').val('').focus();

    },

    removeTag : function(o) {
        o.parent().remove();
        var input = $("#feed-editor-tags-container .input-field");
        input.find('input[type=text]').focus();
    },
    formatFeedActivity : function() {

    },

    validateEditor: function() {
        if (this.actionCount > 0 || this.hasUpload) return false;
        if ($("#feed-editor-textarea").val() != '') return false;
        if ($(".feed-editor-feeling-container").css('display') != 'none'  && $(".feed-editor-feeling-container input[type=text]").val() != "") return false;
        if ($("#feed-geocomplete").val() != '') return false;
        return true;
    },

    post_feed : function(form) {
        if (this.validateEditor()) {
            this.show_error();
            return false;
        }
        form.ajaxSubmit({
            url : baseUrl + 'feed/add',
            dataType : 'json',
            type : 'POST',
            beforeSend : function() {
                feedEditor.toggleIndicator();
                //feedEditor.show_error(false);
            },
            success : function(data) {
                var json = data;

                if (json.status == 0) {
                    feedEditor.show_error(json.message);
                } else {
                    feedEditor.reset(form);
                    var feed = $("<div></div>");
                    feed.html(json.feed).hide();
                    $("#feed-lists").prepend(feed);
                    feed.fadeIn('fast');
                    notifySuccess(json.message);
                    reloadInits();
                    if ($('.feed-empty').length > 0) {
                        $('.feed-empty').fadeOut().remove();
                    }
                }

                feedEditor.toggleIndicator();
            },
            uploadProgress : function(event, position, total, percent) {
                if (!feedEditor.hasUpload) return false;
                var uI = $("#feed-media-upload-indicator");
                uI.html(percent + '%').fadeIn();
                if (percent == 100) {
                    uI.fadeOut().html("0%")
                }
            },
            error : function() {
                feedEditor.toggleIndicator();
            }
        });
    },
    show_error : function(message) {
        var o = $("#feed-editor-error");
        if (!message) {
            message = o.data('error');
        }
        notifyError(message);
    },

    reset : function() {
        $("#feed-editor-textarea").val('').css('height', $("#feed-editor-textarea").data('height'));
        $("#feed-editor-image-input").val('');
        $("#feed-editor-video-input").val('');
        $("#feed-editor-file-input").val('');
        //hide image and video selector span
        $("#feed-editor-image-selector").find('span').hide();
        $("#feed-editor-video-selector").find('span').hide();
        $("#feed-editor-tags-container").hide();
        $("#feed-editor-tags-container .user").each(function() {
            $(this).remove();
        });
        $("#feed-editor-check-in-input-container").hide().find('input[type=text]').val('');
        $(".feed-editor-footer li").each(function() {
            $(this).removeClass('active')
        });
        $('.feed-media-selected-info').hide();
        this.actionCount = 0;
        this.hasUpload = false;
        this.uploadType = ''
        this.removeLinkDetails(true);

        //recent feeling input and hide it
        $('.feed-editor-feeling-container').hide();
        $('.feed-editor-feeling-container input[type=text]').val('').show();
        $("#feed-feeling-selected-suggestion").html('');
        $("#feed-feeling-suggestion").fadeOut();
        $("feed-editor-feeling-data").val("");

        //remove poll posting
        $("#feed-poll-enable-input").val(0);
        $(".poll-options-container input[type=text]").val('');
        $(".feed-editor-poll-container").hide();
        var b = $("#feed-editor-poll-toggle");
        $("#feed-editor-textarea").prop('placeholder', b.data('revert'));

    },
    toggleIndicator : function() {
        var obj = $("#post-editor-indicator");
        if (obj.css('display') == 'none') {
            obj.fadeIn();
        } else {
            obj.fadeOut();
        }
    }
}

function delete_feed(id) {
    var c = $("#feed-wrapper-" + id);
    confirm.action(function() {
        c.css('opacity', '0.5');
        $.ajax({
            url : baseUrl + 'feed/delete?id=' + id + '&csrf_token=' + requestToken,
            success : function(r) {
                if (r == 1) {
                    c.slideUp('slow');
                    c.remove();
                    //alert('na me')
                } else {
                    c.css('opacity', 1);
                }
            },
            error: function() {
                c.css('opacity', 1);
            }
        })
    });
    return false;
}
function pin_feed(t) {
    var o = $(t);
    $.ajax({
        url : o.attr('href') + '?csrf_token=' + requestToken,
        success : function(data) {
            window.location = window.location;
        }
    })

    return false;
}
function show_feed_edit_form(id) {
    var c = $("#feed-edit-form-" + id);
    if (c.css('display') == 'none') {
        c.fadeIn(500).find('textarea').focus();
    } else {
        c.slideUp();
    }
    return false;
}

function save_feed(id) {
    var form = $("#feed-edit-form-" + id);
    var indicator = form.find('.feed-edit-form-indicator');
    form.ajaxSubmit({
        url : baseUrl + 'feed/save?id=' + id,
        type : 'POST',
        beforeSend : function() {
            indicator.fadeIn();
            form.css('opacity', '0.5');
        },
        success : function(data) {
            if (data == '0') {

            } else {
                $("#feed-content-" + id).find('.content').html(data);
                form.slideUp();
            }
            indicator.fadeOut();
            form.css('opacity', 1);
        },
        error : function () {
            indicator.fadeOut();
            form.css('opacity', 1);
        }
    })
    return false;
}

window.feed_paginating = false;
function paginate_feed() {
    if (window.feed_paginating) return false;
    window.feed_paginating = true;
    var c = $("#feed-lists");
    var limit = c.data('limit');
    var offset = c.data('offset');
    var type = c.data('type');
    var typeId = c.data('type-id');

    toggle_feed_paginate_indicator();
    $.ajax({
        url : baseUrl + 'feed/more?csrf_token=' + requestToken,
        dataType : 'html',
        type : 'GET',
        data : {offset:offset,type:type,type_id:typeId},
        success : function(data) {
            window.feed_paginating = false;
            var json = jQuery.parseJSON(data);
            if (json.feeds == '') {
                $('.feed-load-more').fadeOut();
            } else {
                var div = $("<div style='display: none'></div>");
                div.html(json.feeds);
                c.append(div).data('offset', json.offset);
                setTimeout(function() {
                    div.fadeIn(300);
                    reloadInits();
                    toggle_feed_paginate_indicator();

                }, 500)


            }

        },
        error : function() {
            window.feed_paginating = false;
            toggle_feed_paginate_indicator();
        }
    })

}

function toggle_feed_paginate_indicator() {
    var  m = $('.feed-load-more img');
    if (m.css('display') == 'none') {
        m.css('display', 'block').fadeIn();
    } else {
        m.hide();
    }
    //alert()
    //$(document.body).trigger("sticky_kit:recalc");
}

function share_feed(id, m) {
    confirm.action(function() {
        $.ajax({
            url : baseUrl + 'feed/share?id=' + id + '&csrf_token=' + requestToken,
            success : function(data) {
                json = jQuery.parseJSON(data);
                if(json.count != '') $("#feed-share-count-" + id).html(json.count);
                notifySuccess(json.message)
            },
            error : function() {

            }
        })
    }, m);

    return false;
}

function toggle_feed_notification(id) {
    var o = $("#feed-notifications-" + id);
    var onT = o.data('on');
    var offT = o.data('off');
    var turned = o.attr('data-turned');

    if (turned == 1) {
        o.attr('data-turned', '0').html(onT);
        w = 0;
    } else {
        o.attr('data-turned', '1').html(offT);
        w = 1;
    }
    $.ajax({
        url : baseUrl + 'feed/notification?type=' + w+'&id='+id + '&csrf_token=' + requestToken,
    });

    return false;
}

function hide_feed(id) {
    var c = $("#feed-hide-container-" + id);
    var w = $("#feed-wrapper-" + id);
    $.ajax({
        url : baseUrl + 'feed/hide?id=' + id + '&csrf_token=' + requestToken,
        success : function() {
            w.fadeOut();
            c.fadeIn();
        },
        error : function() {
            notifyError(c.data('error'));
        }
    });
    return false;
}

function unhide_feed(id) {
    var c = $("#feed-hide-container-" + id);
    var w = $("#feed-wrapper-" + id);
    $.ajax({
        url : baseUrl + 'feed/unhide?id=' + id + '&csrf_token=' + requestToken,
        success : function() {
            w.fadeIn();
            c.fadeOut();
        },
        error : function() {
            notifyError(c.data('error'));
        }
    });
    return false;
}

function init_feed_realtime_update() {
    if (feedUpdate && loggedIn) {
        var c = $('#feed-lists');
        var type = 'feed';
        var typeId = '';
        var container = 0;
        if (c.length) {
            type = c.data('type');
            typeId = c.data('type-id');
            container = 1;

            $.ajax({
                url : baseUrl + 'check/new/feeds?csrf_token=' + requestToken,
                type : 'POST',
                data :{type:type,typeId:typeId, container: container},
                success : function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.count > 0) {
                        if (c.length > 0) {
                            var div = $("<div></div>");
                            ///alert('d')
                            if (json.feeds != '') {
                                div.html(json.feeds).hide();
                                c.prepend(div);

                            }
                            if (document.body.scrollTop > 50) {

                                var a = $("#feed-top-update-alert");
                                a.find('span').html(json.count);
                                a.fadeIn().click(function() {
                                    $('body').click().animate({scrollTop : 0}, 200);
                                    div.fadeIn();
                                    reloadInits();
                                    $(this).fadeOut();
                                    return false;
                                });
                            } else {
                                setTimeout(function() {
                                    div.fadeIn();
                                    reloadInits();
                                }, 300);
                            }
                        } else {

                        }

                    }


                },
                error : function() {

                }
            })
        }
    }

}


function show_poll_submit_button(id) {
    var c = $("#feed-poll-" + id);
    c.find('.poll-button').fadeIn();
}

function hide_poll_submit_button(id) {
    var c = $("#feed-poll-" + id);
    c.find('.poll-button').fadeOut();
    c.find("input[type=radio]").prop('checked', "");
}

function submit_feed_poll(f) {
    var f = $("#poll-form-" + f);
    f.css('opacity', '0.5');
    //alert("working")
    f.ajaxSubmit({
        url : baseUrl + 'feed/submit/poll',
        success : function(data) {
            $("#feed-poll-" + f.data('id')).html(data);
        }
    })
    return false;
}
$(function() {
    feedEditor.init();
    setInterval(function() {
        init_feed_realtime_update();
    }, feedUpdateInterval);

    $(window).scroll(function() {
        /**if (document.body.scrollHeight ==
            document.body.scrollTop +
                window.innerHeight ) {
            if ($('#feed-lists').length) {
                paginate_feed();
            };
        }**/
        if ($(this).scrollTop() > $(document).height() - $(this).height() - 800) {
            //alert('this ')
            paginate_feed();
        }
    });

    $(document).on('click','.feed-load-more', function() {
        if ($('#feed-lists').length) {
            paginate_feed();
        };
        return false;
    });


    try{
        $("#feed-geocomplete").geocomplete()
            .bind("geocode:result", function(event, result){
                //$("#feed-geocomplete").val(result.formatted_address)
            });
    } catch(e) {}

});


function show_voters(t, answer_id, page) {
    page = page || 1;
    var modal = $('#photoViewer');
    modal.modal('hide');
    var m = $("#likesModal");
    var o = $(t);
    var title = m.find('.modal-title');
    title.html(o.data('otitle'));
    m.modal("show");
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url : baseUrl + 'feed/load/voters?answer_id=' + answer_id + '&page=' + page + '&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}

function paginate_voters(answer_id, page) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    var m = $("#likesModal");
    m.modal("show");
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url : baseUrl + 'feed/load/voters?answer_id=' + answer_id + '&page=' + page + '&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}