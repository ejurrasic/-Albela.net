function selectFile(id, button) {
    $("#" + id).click();
}
function updateAvatar(id) {
    var imageInput = document.getElementById("change-picture-input");
    if (imageInput.files) {
        for (i = 0; i < imageInput.files.length; i++) {
            if (typeof FileReader != "Underfined") {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $("#" + id).attr('src', e.target.result);
                };
                reader.readAsDataURL(imageInput.files[i]);
            }
        }
    }
}

function open_quick_post() {
    var modal = $("#quick-post-modal");
    modal.modal('toggle');
    return false;
}

function process_user_save(t, type, typeId) {
    var o = $(t);
    o.css('opacity', '0.4');
    var s = o.data('status');
    $.ajax({
        url : baseUrl + 'user/save?type=' + type + '&id=' + typeId + '&status=' + s + '&csrf_token=' + requestToken,
        success: function (data) {
            var json = jQuery.parseJSON(data);
            o.find('span').html(json.text);
            o.data('status', json.status);
            o.css('opacity', 1);
            notifySuccess(json.message);
        }
    })
    return false;
}

function read_more(t, id) {
    var o = $(t);
    var container = $('#' + id);
    container.find('span').hide();
    container.find('.text-full').fadeIn();
    if (container.find('.text-full').find('span').length > 0) {
        container.find('.text-full').find('span').fadeIn();
    }
    o.hide();
    return false;
}
/**
 * function to search a string and return first link found
 * @param str
 * @return string
 */
function searchTextForLink(str) {
    pattern = /(^|[\s\n]|<br\/ ?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi;
    pattern = /(^|\s)((https?:\/\/|www\.)[\w-]+(\.[\w-]{2,})+\.?(:\d+)?(\/\S*)?)/gi;
    //pattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    pattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;

    //test for links without http|s://
    matches = pattern.exec(str);
    if (matches && matches[2]) return matches[2];
    return '';
}

function login_required() {
    notifyError($('body').data('general-error'))
}

function show_login_dialog() {
    $("#loginModal").modal('toggle');
    return false;
}

function file_chooser(id) {
    var input = $(id);
    input.click();
    return false;
}

function slidersInit(UC, slide) {
    if (typeof UC == 'undefined') {
        console.log('Unique class not defined');
        return false;
    }
    if ($("#" + UC + " #slides").length < 1) {
        console.log('Unique class selector: .' + UC + ' #slides does not exist');
        return false;
    }
    if ($("#" + UC + " #slides").length > 1) {
        console.log('Multiple unique class selector');
        return false;
    }
    var maxSlide = $("#" + UC + " #slides .slide").length;
    window.cSlide = window.cSlide || {};
    window.sliderInterval = window.sliderInterval || {};
    if (typeof slide !== 'undefined') {
        if(typeof sliderInterval[UC] !== 'undefined') {
            clearInterval(sliderInterval[UC]);
        }
        var cS = slide;
        var pS = window.cSlide[UC];
        window.cSlide[UC] = cS;
        $('#' + UC + ' #slides .slide-' + pS).css('z-index', 0);
        $('#' + UC + ' #slides .slide-' + pS).fadeOut('fast');
        $('#' + UC + ' #slides .slide-' + cS).css('z-index', 1);
        $('#' + UC + ' #slides .slide-' + cS).fadeIn('fast');
    } else {
        window.cSlide[UC] = 1;
    }
    var slider = document.getElementById(UC);
    if(/auto/.test(slider.className)) {
        sliderInterval[UC] = setInterval(function () {
            var cS = window.cSlide[UC] == maxSlide ? 1 : window.cSlide[UC] + 1;
            var pS = window.cSlide[UC];
            window.cSlide[UC] = cS;
            $('#' + UC + ' #slides .slide-' + pS).css('z-index', 0);
            $('#' + UC + ' #slides .slide-' + pS).fadeOut('slow');
            $('#' + UC + ' #slides .slide-' + cS).css('z-index', 1);
            $('#' + UC + ' #slides .slide-' + cS).fadeIn('slow');
        }, 10000);
    }
}

function translateText(t) {
    var a = $(t);
    var c = $("#" + a.data('id') + '-translation');
    c.css("opacity", "0.4");
    var text = c.find('input[type=hidden]').val();
    $.ajax({
        url: baseUrl + 'translate/text?csrf_token=' + requestToken,
        type: 'POST',
        data: {text: text},
        success: function (data) {
            if (data == '') {
                c.fadeOut();
            } else {
                c.html(data).css("opacity", 1).addClass('translated');
            }
        }
    });
    return false;
}

function open_sidebar_menu() {
    var menu = $("#sidebar-menu");
    var main = $("#main-wrapper");
    if (menu.css('display') == 'none') {
        main.css("position", 'relative').css('overflow', 'hidden').css('left', '260px');
        menu.show();
    } else {
        main.css("position", 'relative').css('overflow', 'hidden').css('left', '0');
        menu.hide();
    }
    return false;
}

function hide_side_bar_menu() {
    $("#sidebar-menu").hide();
    $("#main-wrapper").css("position", 'relative').css('overflow', 'hidden').css('left', '0');
}

function reloadInits() {
    $(".timeago").timeago();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimscroll").each(function () {
        $(this).slimScroll({
            height: $(this).data('height')
        });
    });

    if ($(window).width() > 1000) {
        if ($(".profile-container").length > 0) {
            $('.middle-container .right-col-content').stick_in_parent({
                offset_top: 200,
                bottoming: false,
                parent: $(".middle-container")
            });
            $('.middle-container .left-col-content').stick_in_parent({
                offset_top: 200,
                bottoming: false,
                parent: $(".middle-container")
            });

        } else {
            //$('.right-column').stick_in_parent({offset_top: 90,bottoming:false,parent:$("#main-wrapper")});
            $('.middle-container .right-col-content').stick_in_parent({
                offset_top: 46,
                bottoming: false,
                parent: $(".middle-container")
            });
            $('.middle-container .left-col-content').stick_in_parent({
                offset_top: 46,
                bottoming: false,
                parent: $(".middle-container")
            });
        }
        //$('.middle-container .right-col').stick_in_parent({offset_top: 130,bottoming:false,parent:$("#main-wrapper")});
        $('#explore-menu').stick_in_parent({offset_top: 40, bottoming: false});
    }

    tinymce.init({
        selector: '.ckeditor',
        height: 250,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code textcolor colorpicker spellchecker imgupload'
        ],
        toolbar: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist table forecolor code | link image imgupload',
        relative_urls: false,
        document_base_url: baseUrl
    });

    $("#bgcolor").spectrum({
        showPalette: true,
        showSelectionPalette: true,
        showInput: true,
        showInitial: true,
        showButtons: false,
        showAlpha: true,
        maxPaletteSize: 10,
        preferredFormat: "hex",
        palette: [
            ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/
                "rgb(204, 204, 204)", "rgb(217, 217, 217)", /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)"],
            ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
            ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
                "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
        ],
        move: function (color) {
            color = color.toHexString();
            $("#main-wrapper").css('background-color', color);
            $("#explore-menu .container > ul .arrow-up").css("border-bottom-color", color);
            $("#explore-menu").css("border-color", color);
        },
        change: function (color) {
            color = color.toHexString();
            $("#main-wrapper").css('background-color', color);
            $("#explore-menu .container > ul .arrow-up").css("border-bottom-color", color);
            $("#explore-menu").css("border-color", color);
            $("#bgcolor").val(color);

        }

    });


    $("#linkcolor").spectrum({
        showPalette: true,
        showSelectionPalette: true,
        showInput: true,
        showInitial: true,
        showButtons: false,
        showAlpha: true,
        maxPaletteSize: 10,
        preferredFormat: "hex",
        palette: [
            ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/
                "rgb(204, 204, 204)", "rgb(217, 217, 217)", /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)"],
            ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
            ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
                "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
        ],
        change: function (color) {
            color = color.toHexString();
            $("#main-wrapper > .container a").css('color', color);

            $("#linkcolor").val(color);

        },
        move: function (color) {
            color = color.toHexString();
            $("#main-wrapper > .container a").css('color', color);
        }
    });

    $("#containercolor").spectrum({
        showPalette: true,
        showSelectionPalette: true,
        showInput: true,
        showInitial: true,
        showButtons: false,
        showAlpha: true,
        maxPaletteSize: 10,
        preferredFormat: "rgb",
        allowEmpty: true,
        palette: [
            ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/
                "rgb(204, 204, 204)", "rgb(217, 217, 217)", /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)"],
            ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
            ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
                "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
        ],
        change: function (color) {

            if (color == null) {
                $("#main-wrapper > .container").css("background", 'none')
                    .css("padding", "0  important");
                $("#linkcolor").val('');
            } else {
                c = color.toRgb();
                color = "rgba(" + c.r + ', ' + c.g + ', ' + c.b + ',0.5)';
                $("#main-wrapper > .container").css("background", color)
                    .css("padding", "0 10px important");
                //alert(color)
                $("#containercolor").val(color);
            }


        },
        move: function (color) {
            if (color == null) {
                $("#main-wrapper > .container").css("background", 'none')
                    .css("padding", "0  important");
            } else {
                c = color.toRgb();
                color = "rgba(" + c.r + ', ' + c.g + ', ' + c.b + ',0.5)';
                $("#main-wrapper > .container").css("background", color)
                    .css("padding", "0 10px important");
            }
        }
    });

    $(document).ready(function () {

        //RTLText.setText($textarea.get(0), $textarea.val());
    });
    $('textarea').each(function () {
        $(this).on('keyup', RTLText.onTextChange);
        $(this).on('keydown', RTLText.onTextChange);
    })

}

function toggle_profile_cover_indicator(t) {
    var i = $(".profile-cover-indicator");
    if (t) {
        i.fadeIn();
    } else {
        i.fadeOut();
    }
}
function upload_user_profile_cover() {
    toggle_profile_cover_indicator(true);
    $("#profile-cover-change-form").ajaxSubmit({
        url: baseUrl + 'user/change/cover',
        success: function (data) {
            var result = jQuery.parseJSON(data);
            if (result.status == 0) {
                notifyError(result.message);
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

function reposition_user_profile_cover() {
    var rWrapper = $('.profile-cover-wrapper');
    var oWrapper = $('.profile-resize-cover-wrapper');
    if ($(window).width() <= 750) return false;
    if (oWrapper.find('img').attr('src') == '') return false;
    rWrapper.hide();
    oWrapper.show();
    window.show_profile_cover_button = false;
    $('.profile-cover-reposition-button').show();
    oWrapper.find('img').draggable({
        scroll: false,
        axis: "y",
        cursor: "s-resize",
        drag: function (event, ui) {
            y1 = $('#profile-cover').height();
            y2 = oWrapper.find('img').height();

            if (ui.position.top >= 0) {
                ui.position.top = 0;
            }
            else if (ui.position.top <= (y1 - y2)) {
                ui.position.top = y1 - y2;
            }

        },

        stop: function (event, ui) {
            //alert(ui.position.top);
            $('#profile-cover-resized-top').val(ui.position.top);
        }
    });
    return false;
}

function save_user_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var width = $('.profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url: baseUrl + 'user/profile/cover/reposition?pos=' + i + '&width=' + width + '&csrf_token=' + requestToken,
            success: function (data) {
                $('.profile-cover-wrapper img').attr('src', data);
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            },
            error: function () {
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            }
        })
    }
    return false;
}

function refresh_profile_cover_positioning() {
    var rWrapper = $('.profile-cover-wrapper');
    var oWrapper = $('.profile-resize-cover-wrapper');
    oWrapper.hide();
    rWrapper.show();
    window.show_profile_cover_button = true;
    $('.profile-cover-reposition-button').hide();
    $('#profile-cover-resized-top').val('0');
}

function cancel_profile_cover_position() {
    refresh_profile_cover_positioning();
    return false;
}

function remove_user_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    $.ajax({
        url: baseUrl + 'user/cover/remove?csrf_token=' + requestToken,
    });
    return false;
}

function upload_user_avatar() {
    var form = $("#user-profile-image-form");
    show_profile_image_indicator(true);

    form.ajaxSubmit({
        url: baseUrl + 'user/change/avatar',
        success: function (data) {
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
        uploadProgress: function (event, position, total, percent) {
            var uI = $(".profile-image-indicator .percent-indicator");
            uI.html(percent + '%').fadeIn();
        },
        error: function () {
            show_profile_image_indicator(false);
            alertDialog("An error occurred");
            form.find('input[type=file]').val('');
        }
    })
}

function process_user_tag_suggestion(i) {
    var target = $(i.data('target'));
    $(document).click(function (e) {
        if (!$(e.target).closest(i.data('target')).length) target.hide();
    });

    if (i.val().length > 0) {
        //alert(i.data('friend-only'));
        var friend = (i.data('friend-only') == undefined) ? 0 : i.data('friend-only');
        $.ajax({
            url: baseUrl + 'user/tag/suggestion?csrf_token=' + requestToken,
            data: {term: i.val(), friend: friend},
            success: function (data) {
                target.html(data);
                target.fadeIn();
            }
        })
    } else {
        target.hide();
    }
}

var confirm = {
    open: function (m) {
        $('#confirmModal').modal({show: true})
        if (m) {
            $("#confirmModal").find('.modal-body').html(m);
        } else {
            var body = $("#confirmModal").find('.modal-body');
            body.html(body.data('message'));
        }
    },
    url: function (url, m) {
        this.open(m);
        $('#confirm-button').unbind().click(function () {
            window.location = url;
            confirm.close();
        });
        return false;
    },
    action: function (f, m) {
        this.open(m);
        $('#confirm-button').unbind().click(function () {
            f.call();
            confirm.close();
        });
    },
    close: function () {
        $('#confirmModal').modal('hide');
    }
}

function alertDialog(m) {
    $('#alertModal').modal({show: true});
    if (m) $("#alertModal").find('.modal-body').html(m);
}

function notify(m, t, time) {
    var c = $('#site-wide-notification');
    var cM = c.find('.message');
    var time = (time == undefined) ? 8000 : time;
    c.fadeOut();
    c.removeClass('error').removeClass('success').removeClass('info').removeClass('warning').addClass(t);
    cM.html(m);
    c.fadeIn('slow');
    setTimeout(function () {
        c.fadeOut('slow');
    }, time);
}
function notifyError(m, time) {
    notify(m, 'error', time);
}
function notifySuccess(m, time) {
    notify(m, 'success', time);
}
function notifyInfo(m, time) {
    notify(m, 'info', time);
}
function notifyWarning(m, time) {
    notify(m, 'warning', time);
}
function closeNotify() {
    $('#site-wide-notification').fadeOut();
    return false;
}

function show_profile_image_indicator(m) {
    if (m) {
        $(".profile-image-indicator").fadeIn();
    } else {
        $(".profile-image-indicator").fadeOut();
    }
}

function initLoading() {
    $("#loading-line").show();
    $("#loading-line").width((50 + Math.random() * 30) + "%");
}

function stopLoading() {
    $("#loading-line").width("100%").delay(200).fadeOut(500, function () {
        $(this).width('0%');
    })
}

window.pageLoadHooks = [];
function addPageHook(hook) {
    window.pageLoadHooks.push(hook);
}

function runPageHooks() {
    for (i = 0; i <= window.pageLoadHooks.length - 1; i++) {
        f = window.pageLoadHooks[i];
        r = null;
        eval(window.pageLoadHooks[i])();
    }
}

function display_design(image, repeat, color, position, link, container, id) {
    var m = $("#main-wrapper");
    m.css("background-color", color);

    //css('background-image', 'url('+bgImg+')');
    //alert(color);
    if (image == '') {
        //alert('d')
        m.css("background-image", "none");
    } else {
        m.css("background-image", "url(" + image + ")");
    }

    //m.css("background-attachment", attachment);
    m.css("background-position", 'top ' + position);
    m.css("background-repeat", repeat);
    $("#explore-menu .container > ul .arrow-up").css("border-bottom-color", color);
    $("#explore-menu").css("border-color", color);
    if (container != '') {
        $("#main-wrapper > .container").css("background", container)
            .css("padding", "0 10px important");
    } else {
        $("#main-wrapper > .container").css("background", 'none')
            .css("padding", "0 important");
    }
    $("#main-wrapper > .container a").css("color", link);

    if (id != undefined) {
        $("#design-active").val(id);
        $("#design-image-input").val(image);
        $('.design-position').prop('checked', '');
        $('.design-position-' + position).prop('checked', 'checked');
        $("#bgcolor").val(color);
        $("#bgcolor").data('color', color);

        $('.design-repeat').prop('checked', '');
        $('.design-repeat-' + repeat).prop('checked', 'checked');

        $("#linkcolor").val(link);
        $("#linkcolor").data('color', link);

        $("#containercolor").val(container);
        $("#containercolor").data('color', container);
        reloadInits();
    }
    return false;
}

function design_change_image(i) {
    var image = i;
    for (i = 0; i < image.files.length; i++) {
        if (typeof FileReader != "undefined") {

            var reader = new FileReader();
            reader.onload = function (e) {
                $("#main-wrapper").css("background-image", "url(" + e.target.result + ")");
            }
            reader.readAsDataURL(image.files[i]);
        }
    }
}

function design_bg_repeat(t) {
    var input = $(t);
    var repeat = input.val();
    $("#main-wrapper").css("background-repeat", repeat);
}

function design_bg_position(t) {
    var input = $(t);
    $("#main-wrapper").css("background-position", "top " + input.val());
}

function open_designer() {
    var pane = $("#design-pane");
    if (pane.css('display') == 'none') {
        pane.slideDown(300);
    } else {
        pane.slideUp(300);
    }

    return false;
}

function hide_design_pane() {
    $("#design-pane").slideUp(300);
    return true;
}

function change_listing_layout(target, type, callback) {
    var c = $(target);
    var t = (type == 'list') ? 'list-listing-container' : 'grid-listing-container';

    c.removeClass('list-listing-container').removeClass('grid-listing-container').addClass(t);
    if (callback != undefined) {
        window[callback](type);
    }
    return false;
}

function run_global_filter() {
    window.filter_url = $(".global-filter-container").data('url') + '?f=1';
    $('.filter-input').each(function () {
        window.filter_url += '&' + $(this).data('name') + '=' + $(this).val();
    });

    //alert(window.filter_url);
    loadPage(window.filter_url);
    return false;
}
function loadPage(url, f) {
    window.onpopstate = function (e) {
        loadPage(window.location, true);
    }
    //if (url == window.location && f == undefined) return false;
    initLoading();


    $('[data-emoticon="popover"]').popover('hide');
    $.ajax({
        url: url,
        data : {csrf_token:requestToken},
        cache: false,
        type: 'GET',
        success: function (data) {
            if (data == 'login') {
                show_login_dialog();
                stopLoading();
            } else {
                data = jQuery.parseJSON(data)
                var content = data.content;
                var container = data.container;
                var title = data.title;
                $(container).html(content);
                document.title = title;
                Pusher.setPageTitle(document.title);
                $('#explore-container > a span').html(data.menu);
                window.history.pushState({}, 'New URL:' + url, url);
                $(window).scrollTop(0);
                if (data.design) {
                    display_design(data.design.image,
                        data.design.repeat,
                        data.design.color,
                        data.design.position,
                        data.design.link,
                        data.design.container
                    );
                }
                reloadInits();
                stopLoading();
                runPageHooks();
                hide_side_bar_menu();
                if ($('.side-footer').length > 0) {
                    $('.footer-content').hide();
                } else {
                    $('.footer-content').show();
                }
                $('body').click();
            }

        },
        error: function () {
            stopLoading();
            login_required();
        }
    });
    return false;
}

/**
 * Realtime update push final process
 */
var Pusher = {
    hooks: [],
    alert: false,
    pushIds: [],
    titleCount: 0,
    pageTitle: '',
    userid: '',
    onAlert: function () {
        this.alert = true;
    },
    offAlert: function () {
        this.alert = false;
    },

    finish: function () {
        //final steps to take like sound alert if on
        if (this.alert) document.getElementById('update-sound').play();
        this.alert = false;
        this.refreshPageTitle();
    },

    setPageTitle: function (t) {
        this.pageTitle = t;
        this.refreshPageTitle();
    },

    refreshPageTitle: function () {
        if (this.titleCount > 0) {
            pageTitle = this.pageTitle;
            pageTitle = '(' + this.titleCount + ') ' + pageTitle;
            document.title = pageTitle;
            this.titleCount = 0;
        } else {
            document.title = this.pageTitle;
        }
    },

    setUser: function (userid) {
        this.userid = userid;
    },
    getUser: function () {
        return this.userid;
    },
    addCount: function (c) {
        this.titleCount = parseInt(this.titleCount) + parseInt(c);
    },

    removeCount: function (c) {
        this.titleCount -= c;
        this.refreshPageTitle();
    },

    addHook: function (hook) {
        this.hooks.push(hook);
    },

    run: function (type, d) {
        for (i = 0; i <= this.hooks.length - 1; i++) {
            f = this.hooks[i];
            r = null;
            eval(this.hooks[i])(type, d);

        }
    },

    addPushId: function (id) {
        this.pushIds.push(id);
    },
    hasPushId: function (id) {
        if (jQuery.inArray(id, this.pushIds) != -1) return true;
        return false;
    }
}

//functions to manage cookies
function setCookie(cname, cvalue, exdays) {
    if (exdays == undefined) exdays = 365;
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function deleteCookie(cname) {
    document.cookie = cname + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
}

$(window).on('unload', function () {
    $(window).scrollTop(0);
});

$(function () {
    reloadInits();

    $(window).resize(function () {
        if ($(window).width() > '750') {
            if ($("#sidebar-menu").css('display') != 'none') {
                hide_side_bar_menu();
            }
        }
    });
    Pusher.setPageTitle(document.title);
    $(document).on("keyup", ".auto-grow-input", function () {
        var obj = $(this);
        var height = '20px';
        if (obj.attr("data-height")) {
            height = obj.data('height');
        }
        this.style.height = height;
        this.style.height = (this.scrollHeight) + 'px';
    });

    $(document).on("keyup", '.user-tag-input', function () {

        process_user_tag_suggestion($(this));
    });

    $(document).on('click', '.confirm', function () {
        confirm.url($(this).attr('href'));
        return false;
    });

    $(document).on('keyup', '.textarea-limit', function () {
        var o = $(this);
        var limit = o.data('text-limit');
        var countTarget = $(o.data('text-limit-count-target'));
        var text = o.val();
        if (text.length > limit) {
            text = text.substr(o, limit);
        }
        o.val(text);
        countTarget.html(limit - text.length);
    });

    $(document).on("click", "a[ajax='true']", function () {
        if (typeof loadAjax !== 'undefined' && !loadAjax) return true;
        return loadPage($(this).attr('href'));
    });

    $(document).on("submit", "#header-search", function () {
        var term = $(this).find('input[type=text]').val();

        $(this).find('input[type=text]').blur();
        if (term != '') {
            url = $(this).attr('action') + '?term=' + term;
            $('#search-dropdown').fadeOut('fast', function () {

            });
            loadPage(url);
        }
        return false;
    });


    $(document).on('mouseover', '.preview-card', function () {
        var card = $(this).find('.profile-card');
        if (card.length == 0) {
            card = $("<div class='profile-card box'>" + indicator + "</div>");
            card.find('img').css('width', '10px');
            card.find('img').css('display', 'block');
            card.find('img').css('margin', '10px auto');
            $(this).append(card);
            $.ajax({
                url: baseUrl + 'preview/card?type=' + $(this).data('type') + '&id=' + $(this).data('id') + '&csrf_token=' + requestToken,
                success: function (c) {
                    card.html(c);
                }
            })
        }
        var obj = $(this);
        $(document).click(function (e) {
            if (!$(e.target).closest(obj).length) card.hide();
        });

        $(document).mouseout(function (e) {
            if (!$(e.target).closest(obj).length) card.hide();
        });
        card.fadeIn();
    });


    if ($("#popup-language-selection").length > 0) {
        $("#popup-language-selection").fadeIn(500);
        document.body.style.overflow = 'hidden';
    }

});