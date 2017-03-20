function show_message_dropdown(){
    var dropdown = $(".message-dropdown");
    var indicator = dropdown.find('#message-dropdown-indicator');
    var content = dropdown.find('.message-dropdown-result-container');
    if (dropdown.css('display') == 'none') {
        dropdown.fadeIn();
        indicator.show();
        $.ajax({
            url : baseUrl + 'chat/load/dropdown?csrf_token=' + requestToken,
            success : function(data) {
                content.html(data);
                indicator.hide();
                reloadInits();
            }
        })
    } else {
        dropdown.fadeOut();
    }
    $(document).click(function(e) {
        if(!$(e.target).closest("#message-dropdown-container").length) dropdown.hide();
    });
    return false;
}

function open_chat_onlines(obj) {
    var obj = $("#chat-boxes-container .opener-head");
    var o = $("#chat-boxes-container");
    if (obj.hasClass('opened')) {
        //close it
        o.find('.main').hide();
        obj.removeClass('opened');
    } else {
        obj.addClass('opened');
        o.find('.main').show();
    }
    return false;
}

function change_online_status(type, c) {
    var b = $(".onlines-container .opener-head .dropdown-button span");
    b.removeClass('online-icon').removeClass('busy-icon').removeClass('invisible-icon');
    b.addClass(c);
    $.ajax({
        url : baseUrl + 'chat/set/status?type=' + type + '&csrf_token=' + requestToken,
    })
    return false;
}

function switch_chat_onlines_type(type) {
    var o = $("#chat-boxes-container");
    var u = o.find('.main > ul');
    u.find('a').removeClass('active');
    o.find('.online-lists').find('.lists').hide();
    if (type == 'friend') {
        //we are showing friend list
        var c = o.find('.online-friend-list');
        u.find('.friend').addClass('active');
        c.show();
    } else {
        var c = o.find('.online-group-list');
        u.find('.group').addClass('active');
        c.show();
        if (c.html() == '') {
            $.ajax({
                url : baseUrl + 'chat/load/groups?csrf_token=' + requestToken,
                success : function(data) {
                    c.html(data);
                }
            })
        }
    }
    return false;
}

function switch_chat_send_button(e) {
    var b = $("#chat-send-button");
    var i = $("#chat-send-input");
    if ($(e).prop('checked')) {
        b.hide();
        v = 0;
    } else {
        b.show();
        v = 1;
    }
    $.ajax({
        url : baseUrl + 'chat/update/send/privacy?v=' + v + '&csrf_token=' + requestToken,
    })

}
/**
 * Chat Object
 * @type {{}}
 */
var Chat = {
    boxes : [],
    toLoad : [],
    tabs : 7, //holds number of tabs that can be opened
    messages : [],
    cidClosed : [],

    opened : function(cid, uid) {
        var div;
        if (cid != null && $("div[data-cid="+cid+"]").length > 0) {
            div = $("div[data-cid="+cid+"]");
        } else {
            div = $("div[data-uid="+uid+"]");
        }
        if (div != undefined) return true;
        return false;
    },

    getBox : function(cid, uid) {
        var div;
        if (cid != null && $("div[data-cid="+cid+"]").length > 0) {
            div = $("div[data-cid="+cid+"]");
        } else {
            div = $("div[data-uid="+uid+"]");
        }
        return div;
    },

    minimized : function(cid) {
        var box = this.getBox(cid);
        if (box.length > 0 && box.css('display') == 'none') return true;
        return false;
    },

    boxCreated : function(cid, uid) {
        if ((cid != null && $("div[data-cid="+cid+"]").length > 0) || (uid != null && $("div[data-uid="+uid+"]").length > 0) || $('.message-box-' + cid).length > 0) return true;
        return false;
    },

    registerBox: function(cid, uid) {
        $.ajax({
            url : baseUrl + 'chat/register/open?csrf_token=' + requestToken,
            data : {cid: cid, uid : uid}
        });

        var n = [];
        for(i=0;i<=this.cidClosed.length;i++) {
            if (this.cidClosed[i] != cid) n.push(this.cidClosed[i]);
        }

        this.cidClosed = n;

        //alert(this.cidClosed);
    },

    unRegisterBox : function(cid) {
        $.ajax({
            url : baseUrl + 'chat/register/open?action=delete&csrf_token=' + requestToken,
            data : {cid: cid}
        });
        this.cidClosed.push(cid);
    },

    validateAllowTabs: function() {
        var tabs = 1;
        $('.chat-tab').each(function() {
            tabs += 1;
        });

        if (tabs > this.tabs) {
            $('.chat-tab').each(function(i, e) {
                if (i == 0) {
                    var tab = $(this);
                    Chat.closeBox(tab.data('tab'));
                }
            });
        }
    },

    open : function(cid, uid, title, img, img2, reg, canStream) {
        var id,div;
        if ($('.message-box-' + cid).length > 0) {
            //we must not open that since we are on the message page
        } else {
            //
            $(".message-dropdown").hide();
            if (this.boxCreated(cid, uid)) {
                //alert(cid);
                if (cid != null && $("div[data-cid="+cid+"]").length > 0) {
                    div = $("div[data-cid="+cid+"]");
                    id = cid;
                } else {
                    div = $("div[data-uid="+uid+"]");
                    id = 'uid-' + uid;
                }
                this.showBox(div, id);
            } else {
                this.validateAllowTabs();
                if (cid != null) {
                    id = cid

                } else {
                    id = 'uid-' + uid;
                }

                this.boxes.push(id);
                boxType = (img2 == undefined) ? 'single' : 'multiple';
                div = $("<div data-typing='0' data-id='"+id+"' id='chat-box-"+id+"' class='chat-box chat-box-intact' data-cid='"+cid+"' data-uid='"+uid+"'></div>");
                div.html($(".chat-box-template").html());

                if (cid != null) div.attr('id', 'chat-box-' + cid);
                //chat header
                var head = div.find('.head');
                if (title != undefined) {
                    head.prepend("<span style='color:white'>"+title+"</span>");
                }
                if (uid != '') {
                    div.find('.editor form').prepend("<input type='hidden' name='val[user][]' value='"+uid+"' />");
                }

                if (cid != '') {
                    div.find('.editor form .message-cid-input').val(cid)
                }

               $("#chat-boxes-container .chat-boxes-wrapper").append(div);
                div.draggable({
                    containment : 'window',
                    cursor : 'move',
                    iframeFix: true,
                    appendTo : 'body',
                    handle : '.head',
                    scroll : false,
                    zIndex : 9999,
                    start : function(e, ui){
                        var o = $(ui.helper);
                        o.removeClass('chat-box-intact');
                    }
                });

                //do some event attachment
                head.find('.mediachat-video-call-init-button').prop('id', 'mediachat-video-call-init-button-' + uid);
                head.find('.mediachat-voice-call-init-button').prop('id', 'mediachat-voice-call-init-button-' + uid);
                head.find('.close-button').attr('data-id', id);
                head.find('.minimize-button').attr('data-id', id);
				if(canStream) {
					head.find('#mediachat-video-call-init-button-' + uid).click(function(e) {
						e.preventDefault();
						if (typeof mediaChat.call === "function") {
							var other = {identity: uid, name: title, avatar: img, type: 'video'};
							mediaChat.call(other);
						}
					});
					head.find('#mediachat-voice-call-init-button-' + uid).click(function(e) {
						e.preventDefault();
						if (typeof mediaChat.call === "function") {
							var other = {identity: uid, name: title, avatar: img, type: 'voice'};
							mediaChat.call(other);
						}
					});
				} else {
					head.find('.mediachat-video-call-init-button').attr('disabled', 'disabled');
					head.find('.mediachat-video-call-init-button').attr('title', 'This user is unavailable for video call');
					head.find('.mediachat-voice-call-init-button').attr('disabled', 'disabled');
					head.find('.mediachat-voice-call-init-button').attr('title', 'This user is unavailable for voice call');
				}
                head.find('.close-button').click(function() {
                    Chat.closeBox($(this).data('id'));
                    return false;
                });
                head.find('.minimize-button').click(function() {
                    Chat.hideBox($(this).data('id'));
                    return false;
                });

                var form = div.find('.editor form');
                form.attr('id', 'chat-box-form-' + id);
                div.find('.editor form').data('id', id);
                var textarea = div.find('.editor form textarea');
                textarea.attr('data-id', id);
                textarea.data('id', id);
                textarea.attr('id', id + '-textarea');
                textarea.keydown(function(e) {
                    var arBox = $("#chat-box-" + $(this).data('id'));
                    if (arBox.length && arBox.data('type') != undefined && arBox.data('type') == 'single') {
                        if ((e.keyCode || e.which ) != 13) {
                            if(arBox.data('typing') == 0) {
                                arBox.attr('data-typing', 1);
                                arBox.data('typing', 1);
                                $.ajax({
                                    url : baseUrl + 'chat/typing?cid=' + arBox.data('id') + '&csrf_token=' + requestToken,
                                })
                            }

                        } else {
                            //alert('here')
                            arBox.attr('data-typing', 0);
                            arBox.data('typing', 0);
                        }
                    }
                });

                textarea.blur(function(e) {

                    var arBox = $("#chat-box-" + $(this).data('id'));
                    arBox.attr('data-typing', 0);
                    arBox.data('typing', 0);
                    $.ajax({
                        url : baseUrl + 'chat/remove/typing?cid=' + arBox.data('id') + '&csrf_token=' + requestToken,
                    });
                });

                var emoticonButton = div.find('.editor form .emoticon-button');
                emoticonButton.attr('data-target', id + '-textarea').data('target', id + '-textarea');
                var form = div.find('.editor form');
                form.submit(function() {
                    var upload = false;
                    var form = $(this);
                    if (form.find('.chat-box-image-input').val() != '' || form.find('.chat-box-file-input').val() != '') {
                        upload = true;
                    }

                    $(this).ajaxSubmit({
                        url : baseUrl + 'chat/send',
                        uploadProgress : function(event, position, total, percent) {
                            if (!upload) return false;
                            var dBox = $("#chat-box-" + form.data('id'));
                            var messagesPane = dBox.find('.message-list');
                            dBox.find('.message-list').find('.typing-indicator').remove();
                            if (messagesPane.find('.message-upload-indicator').length > 0) {
                                var uI = messagesPane.find('.message-upload-indicator');
                            } else {
                                var uI = $("<div class='message-upload-indicator'>"+messagesPane.data('sending')+" <span></span></div>");
                                messagesPane.append(uI);

                            }
                            messagesPane.slimScroll({
                                scrollBy : messagesPane.prop('scrollHeight') + 'px'
                            });
                            uI.show();
                            uI.find('span').html(percent + '%');
                            if (percent == 100) {
                                uI.remove();
                            }
                        },
                        dataType : 'json',
                        success : function(data) {
                            var json = data;
                            if (json.status == 0) {
                                notifyError(json.error);
                            } else {
                                Chat.messages.push(json.messageid);
                                var box = $("#chat-box-" + form.data('id'));
                                if (box.data('cid') == null && reg == undefined) Chat.registerBox(json.cid,json.uid);
                                box.attr('data-cid', json.cid);
                                box.data('cid', json.cid);
                                box.attr('data-type', json.type);
                                box.data('type', json.type);

                                box.find('.editor form .message-cid-input').val(json.cid)
                                var container = box.find('.message-list');
                                container.append(json.message);
                                //container.animate({scrollTop : container.prop("scrollHeight")}, 200)
                                Chat.scrollerToBottom(box);
                                container.find('.seen').fadeOut();
                                reloadInits();
                            }
                        }
                    });
                    form.find('textarea').val('');
                    form.find('input[type=file]').val('');
                    return false;
                })
                div.find('.editor form textarea').keydown(function(e) {

                    if ((e.keyCode || e.which ) == 13) {

                        form.submit();
                        return false;
                    }
                });
                div.find('.editor form').submit(function() {

                });
                div.find('.editor form .chat-box-image-selector').click(function() {
                    div.find('.editor form .chat-box-image-input').click();
                    return false;
                });

                div.find('.editor form .chat-box-file-selector').click(function() {
                    div.find('.editor form .chat-box-file-input').click();
                    return false;
                });
                div.find('.editor form input[type=file]').change(function() {
                    if (div.find('.editor form textarea').val() == '') {
                        div.find('.editor form').submit();
                    }
                });

                //this.repositionBoxes();
                this.addTab(div,id, img, img2);

                if (cid != null && reg == undefined) Chat.registerBox(cid);

                //preload chat box details
                $.ajax({
                    url : baseUrl + 'chat/preload?csrf_token=' + requestToken,
                    data : {cid : cid, uid : uid},
                    success : function(data) {
                        var json = jQuery.parseJSON(data);
                        if (div.data('cid') == null && reg == undefined) Chat.registerBox(json.cid, json.uid);
                        div.attr('data-cid', json.cid);
                        div.data('cid', json.cid);
                        div.attr('data-type', json.type);
                        div.data('type', json.type);
                        var container = div.find('.message-list');
                        container.prepend(json.messages);
                        //container.animate({scrollTop : container.prop("scrollHeight")}, 200)
                        Chat.applyScroller(div);
                        div.attr('data-uid', json.uid);
                        div.find('.editor form .message-cid-input').val(json.cid)
                        reloadInits();
                    }
                })
            }

        }

        return false;
    },

    applyScroller : function(div) {
        var container = div.find('.message-list');
        container.slimScroll({
            height: '255px',
            start : 'bottom'
        });
        container.slimScroll().bind('slimscroll', function(e, pos) {
            if (pos == 'top') {
                if (container.find('.chat-message-indicator').length == 0) {
                    $.ajax({
                        url : baseUrl + 'chat/paginate?csrf_token=' + requestToken,
                        beforeSend : function() {
                            var i = $("<div class='chat-message-indicator'>"+indicator+"</div>");
                            container.prepend(i);
                        },
                        data : {offset: container.data('offset'),cid:div.data('cid')},
                        success : function(data) {
                            var data = jQuery.parseJSON(data);
                            if (data.messages != '') {
                                var d = $("<div></div>");
                                d.html(data.messages);

                                container.prepend(d).find('.chat-message-indicator').remove();;
                                reloadInits();
                                setTimeout(function() {
                                    container.slimScroll({
                                        scrollBy : (d.height() - 20) + 'px'
                                    })
                                }, 100)
                                container.attr('data-offset', data.offset);
                                container.data('offset', data.offset)

                            } else {
                                container.prepend(d).find('.chat-message-indicator').remove();;
                            }
                        }
                    });
                }

            }
        })
    },

    scrollerToBottom : function(div) {
        var container = div.find('.message-list');
        container.slimScroll({
            scrollBy : container.prop('scrollHeight') + 'px'
        })
    },

    addTab : function(d,id, img, img2) {
        var div = $("<div id='chat-box-tab-"+id+"'  data-tab='"+id+"' class='tab chat-tab active'><a class='toggle' href='#'></a><a class='close-button' href=''><i class='ion-android-close'></i></a> </div>");
        var toggle = div.find('.toggle');
        toggle.append("<span style='background-image:url("+img+")'></span>");
        if (img2 != undefined) {
            toggle.addClass('multiple');
            toggle.append("<span style='background-image:"+img2+"'></span>");
        }
        toggle.click(function() {
            var b = $("#chat-box-" + id);
            Chat.showBox(b, id);
            return false;
        });
        div.mouseover(function() {
            div.find('.close-button').show();
        });

        div.mouseout(function() {
            div.find('.close-button').hide();
        });

        div.find('.close-button').click(function() {
            Chat.closeBox(id);
            return false;
        });
        $(".chat-tabs-container").append(div);

        //hide all chat-box-intact and show this
        this.showBox(d, id);
    },

    getTab : function(cid) {
        var box = this.getBox(cid);
        var id = box.data('id');
        return $("#chat-box-tab-" + id);
    },

    showBox : function(div, id) {
        if (div.hasClass('chat-box-intact')) {
            $(".chat-box-intact").hide();
            $(".chat-tabs-container .tab").removeClass('active');
            $("div[data-tab='"+id+"']").addClass('active');
        }
        div.show();
        if (div.data('mids') != undefined && div.data('mids') != '') {
            ids = div.data('mids');
            div.attr('data-mids', '');
            div.data('mids', '');
            this.markRead(ids.split(','));
        }

        var id = div.data('id');
        var tab = $("#chat-box-tab-" + id);
        if (tab.find('.count').length > 0) tab.find('.count').remove();
        this.scrollDown(div);
    },

    scrollDown : function(box) {
        var container = box.find('.message-list');
        container.animate({scrollTop : container.prop("scrollHeight")}, 200)
    },

    closeBox : function(id) {
        var box = $("#chat-box-" + id);
        var tab = $("div[data-tab='"+id+"']");
        tab.remove();
        box.remove();
        var tab = '';
        $(".chat-tabs-container .tab").each(function() {
            tab = $(this);
        });

        if (tab) {
            div = $("#chat-box-" + tab.data('tab'));
            this.showBox(div, tab.data('tab'));
        }

        //alert(box.data('cid'));
        if (box.data('cid') != null) {
            this.unRegisterBox(box.data('cid'))
        }
        return false;
    },

    hideBox : function(id) {
        var box = $("#chat-box-" + id);
        box.fadeOut();
    },

    appendMessage : function(cid, html) {

        if ($('.message-box-' + cid).length > 0) {
            //we must not open that since we are on the message page
            var c = $('.message-box-' + cid);
            var container = c.find('.messages-pane');
            container.append(html).animate({scrollTop : container.prop("scrollHeight")}, 200);
        }
        //get chat box
        var box = this.getBox(cid);
        if (box.length > 0) {
            box.find('.message-list').find('.typing-indicator').remove();
            var container = box.find('.message-list');
            container.append(html).animate({scrollTop : container.prop("scrollHeight")}, 200);
            container.find('.seen').fadeOut();
        }
    },

    putToLoad : function(cid, ids) {
        this.toLoad.push([cid, ids]) ;
    },

    pushArrived: function() {
        //from here we can load the messages need

        if (this.toLoad.length > 0) {
            $.ajax({
                url : baseUrl + 'chat/load/messages?csrf_token=' + requestToken,
                type : 'POST',
                data : {data : this.toLoad},
                success : function(data) {
                    var json = jQuery.parseJSON(data);
                    Chat.toLoad = [];
                    $.each(json.messages, function(cid, html) {
                        Chat.appendMessage(cid, html);
                    });
                    reloadInits();

                }
            })
        }
    },
    markRead : function(mIds) {
        $.ajax({
            url : baseUrl + 'chat/mark/read?csrf_token=' + requestToken,
            data : {ids : mIds},
            success : function(c) {
                Chat.updateUnread(c);
            },
            error : function() {
                Chat.markRead(mIds);
            }
        })
    },
    updateUnread : function(d) {
        var c = $("#message-dropdown-container");
        var a = $("#message-dropdown-container > a");
        if (d > 0) {
            var span = a.find('span');
            if (!span.length) {
                var span = $("<span class='count'></span>")
                a.append(span);
            }
            span.html(d);
            Pusher.addCount(d);
        } else {
            if (a.find('span').length) {
                a.find('span').remove();
            }
        }
    }
}
window.chatPushRun = false;
function push_chat(type, d) {
    if (type == 'chat') {

        if (window.chatPushRun) {
            $.each(d, function(cid, cPushes) {
                if (Chat.boxCreated(cid)) {
                    var ids = [];
                    var mIds = [];
                    $.each(cPushes, function(push, pD) {
                        if (!Pusher.hasPushId(push)) {
                            Pusher.addPushId(push);
                            if (jQuery.inArray(pD.id, Chat.messages) == -1) {
                                ids.push(pD.id);
                                if (pD.user != Pusher.getUser()) mIds.push(pD.id);
                            }
                        }
                    });
                    if (mIds.length > 0) {
                        if (Chat.minimized(cid)) {
                            //get the tab toggle and attach the event to mark messages read
                            var tab = Chat.getTab(cid);
                            if (tab.length > 0) {
                                var count = tab.find('.count');
                                if (count.length < 1) {
                                    count = $("<span class='count'></span>");
                                    tab.append(count);
                                }
                                count.html(mIds.length);
                            }
                            var box = Chat.getBox(cid);
                            var m = (box.data('mids') != undefined) ? box.data('mids') : '';

                            for(i=0;i<mIds.length;i++) {
                                m += ',' + mIds[i];
                            }
                            box.attr('data-mids', m);
                            box.data('mids', m);

                        } else {
                            //mark necessary messages
                            Chat.markRead(mIds);
                        }
                        //if ($('.message-box-' + cid).length > 0) Chat.markRead(mIds);
                    }
                    if (ids.length > 0) Chat.putToLoad(cid, ids);
                } else {
                    // Chat.open(cid);
                    window.chatPushRun = true;
                    $.each(cPushes, function(push, pD) {
                        if (!Pusher.hasPushId(push)) Pusher.addPushId(push);
                    })
                }
            });
        } else {
            $.each(d, function(cid, cPushes) {
                window.chatPushRun = true;
                $.each(cPushes, function(push, pD) {
                    if (!Pusher.hasPushId(push)) Pusher.addPushId(push);
                })
            });
        }
        Chat.pushArrived();
    } else if(type == 'unread') {
        Chat.updateUnread(d);
    } else if (type == 'count-onlines') {
        $("#chat-boxes-container .opener-head > a span").html(d);
    } else if (type == 'onlines') {
        $("#chat-boxes-container .online-friend-list").html(d);
    } else if (type == 'chat-typing') {
        var currentTime = d.now - 20;
        var cids = d.cid;
        $.each(cids, function(cid, time) {
            var dBox = $("#chat-box-" + cid);

            if (dBox.length > 0 && time > currentTime) {
                var ty = d.typing;
                var img = d.img;
                if (dBox.find('.typing-indicator').length < 1) {
                    var ind = $("<div class='typing-indicator'><span class='arrow-left'></span><span class='content'><img src='"+img+"'/> "+ty+"...</span></div>")
                    dBox.find('.message-list').append(ind);
                    var container = dBox.find('.message-list');
                    container.animate({scrollTop : container.prop("scrollHeight")}, 200);
                }

            } else {
                if (dBox.find('.typing-indicator').length > 0) {
                    dBox.find('.typing-indicator').remove();
                }
            }
        })
    } else if(type == 'chat-seen') {
        $.each(d, function(cid, messageId) {
            $(".chat-message-" + messageId).find('.seen').fadeIn();
        });
    }
    else if(type == 'chat-opened') {

        if (d != '') {
            var ids = [];
            var allIds = [];

            $.each(d, function(i, c) {

                if (!Chat.boxCreated(i, c) && i != 0 && jQuery.inArray(i, Chat.cidClosed) == -1) {
                    ids.push(i);
                }
                allIds.push(i);
            });

            if (ids.length > 0) {
                $.ajax({
                    url : baseUrl + 'chat/get/conversations?csrf_token=' + requestToken,
                    data : {cids : ids},
                    success : function(data) {
                        var json = jQuery.parseJSON(data);
                        $.each(json, function(cid, v) {
                            if (jQuery.inArray(cid, Chat.cidClosed) == -1) {
                                if (v.title == null) return false;
                                Chat.open(cid, v.uid, v.title, v.avatar, v.avatar2, null, v.canStream);
                            }

                        })
                    }
                })
            }



        }
    }
}

function chat_new_page_loaded()  {
    var container = $('.messages-pane');
    if (container.length > 0) {

        container.slimScroll({start: 'bottom', height : '350px'});
        container.slimScroll().bind('slimscroll', function(e, pos) {
            if (pos == 'top') {
                if (container.find('.chat-message-indicator').length == 0) {
                    $.ajax({
                        url : baseUrl + 'chat/paginate?csrf_token=' + requestToken,
                        beforeSend : function() {
                            var i = $("<div class='chat-message-indicator'>"+indicator+"</div>");
                            container.prepend(i);
                        },
                        data : {offset: container.data('offset'),cid:$("#message-cid-input").val()},
                        success : function(data) {
                            var data = jQuery.parseJSON(data);
                            if (data.messages != '') {
                                var d = $("<div></div>");
                                d.html(data.messages);

                                container.prepend(d).find('.chat-message-indicator').remove();;
                                reloadInits();
                                setTimeout(function() {
                                    container.slimScroll({
                                        scrollBy : (d.height() - 20) + 'px'
                                    })
                                }, 100)
                                container.attr('data-offset', data.offset);
                                container.data('offset', data.offset)

                            } else {
                                container.prepend(d).find('.chat-message-indicator').remove();;
                            }
                        }
                    });
                }
            }
        })
    }
}
Pusher.addHook('push_chat');
addPageHook('chat_new_page_loaded');

function chat_message_upload() {
    var form = $('#message-chat-form');
    if (form.find('textarea').val() == '') {
        form.submit();
    }
}

function delete_conversation() {
    var cid = $("#message-cid-input").val();
    var url = baseUrl + 'chat/delete/conversation?cid=' +cid;
    return confirm.url(url);
}

function delete_chat_message(id) {
    var message = $('.chat-message-' + id);
    message.css('opacity', '0.5');
    $.ajax({
        url : baseUrl + 'chat/delete/message?id=' + id + '&csrf_token=' + requestToken,
        success : function() {
            message.slideUp().remove();
        },
        error : function() {
            message.css('opacity', 1);
        }
    });
    return false;
}

$(function() {
    chat_new_page_loaded();
   $(document).on('click', '#message-user-suggestion a', function() {
       var name = $(this).data('name');
       var id = $(this).data('id');
       if ($("#message-user-"+id).length > 0) {
           $("#message-user-suggestion").hide();
           return false;
       }
       var span = $("<span class='user' id='message-user-"+id+"'>"+name+"<input type='hidden' name='val[user][]' value='"+id+"'/><a href=''><i class='ion-close'></i></a> </span>");
       var container = $("#message-to-lists");
       var theInput = container.find('input[type=text]');
       span.insertBefore(theInput)
       span.find('a').click(function() {
            span.remove();
           return false;
       });
       $("#message-user-suggestion").hide();
       theInput.val('');
       return false;
   });

    $(document).on('submit', '#message-chat-form', function() {
        var form = $(this);
        var upload = false;

        if (form.find('#chat-message-image-input').val() != '' || form.find('#chat-message-file-input').val() != '' ){
            upload = true;
        }

        form.ajaxSubmit({
            url : baseUrl + 'chat/send',
            uploadProgress : function(event, position, total, percent) {
                if (!upload) return false;
                var messagesPane = form.find('.messages-pane');
                if (messagesPane.find('.message-upload-indicator').length > 0) {
                    var uI = messagesPane.find('.message-upload-indicator');
                } else {
                    var uI = $("<div class='message-upload-indicator'>"+messagesPane.data('sending')+" <span></span></div>");
                    messagesPane.append(uI);

                }
                messagesPane.slimScroll({
                    scrollBy : messagesPane.prop('scrollHeight') + 'px'
                });
                uI.find('span').html(percent + '%');
                if (percent == 100) {
                    uI.remove();
                }
            },
            success : function(data) {
                var json = jQuery.parseJSON(data);
                if (json.status == 0) {
                    notifyError(json.error);
                } else {
                    Chat.messages.push(json.messageid);
                    var form = $("#message-chat-form");
                    var toInputs = form.find('.message-to-container');
                    var title = form.find('.messages-pane-title');
                    if (toInputs.length > 0 && toInputs.css('display') != 'none') {
                        toInputs.hide();
                        title.find('.message-title').html(json.title);
                        document.title = json.sitetitle;
                        url = json.url;
                        window.history.pushState({},'New URL:' + url, url);
                        form.find('#message-cid-input').val(json.cid);
                        title.show();
                        $("#message-right-pane").addClass('message-box-' + json.cid);
                    }
                    var messagesPane = form.find('.messages-pane');
                    messagesPane.append(json.message);
                    reloadInits();
                    messagesPane.slimScroll({
                        scrollBy : messagesPane.prop('scrollHeight') + 'px'
                    });
                    form.find('textarea').val('');
                }
            }
        });
        form.find('textarea').val('');
        form.find('input[type=file]').val('');
        return false;
    });

    $(document).on('click', '.add-emoticon', function(e) {

        if ($('.messages-pane').length > 0) {
            var t = $(this).data('target');
            var s = $(this).data('symbol');
            var v = $(t).val() + " " + s + " ";
           // alert($("#message-chat-form").html());
            if ($("#message-chat-form").find('textarea').val() == '') {
                $(t).val(v);
                //alert('am here');
                $("#message-chat-form").submit();
            } else {
                $(t).val(v).focus();
            }
            $('.emoticon-box').fadeOut();//we need to hide
        }
        e.preventDefault();
        return false;
    });

    $(document).on('click', '.chat-box .add-emoticon', function(e) {
        var t = $(this).data('target');
        var s = $(this).data('symbol');
        var v = $(t).val() + " " + s + " ";
        var target = $(t);
        if (target.val() == '') {
            target.val(v);
            //alert('#chat-box-form-' + target.data('id'));
            $('#chat-box-form-' + target.data('id')).submit();
        } else {
            target.val(v).focus();
        }

        $('.emoticon-box').fadeOut();//we need to hide
        return false;
    })

    $(document).on('keydown',  '#message-editor-textarea', function(e) {

        if ((e.keyCode || e.which ) == 13) {
            if ($("#chat-send-input").prop('checked')) {
                $("#message-chat-form").submit();
            }
        }
    });


});