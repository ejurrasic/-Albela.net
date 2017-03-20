function update_ads_image_changed(image) {
    for(i = 0; i < image.files.length; i++) {
        if (typeof FileReader != "undefined") {

            var reader = new FileReader();
            reader.onload = function(e) {
                var img = $(".ads-create-img");
                img.each(function() {
                    $(this).attr('src', e.target.result);
                    $(this).fadeIn();
                })

            }
            reader.readAsDataURL(image.files[i]);
        }
    }
}

function ads_load_bid_plans(t, type) {
    var input = $(t);
    if (input.prop('checked')) {
        var s = $(".ads-plan-list");
        s.css('opacity', '0.6');
        $.ajax({
            url : baseUrl + 'ads/load/plan?type=' + type + '&csrf_token=' + requestToken,
            success : function(data) {
                var json = jQuery.parseJSON(data);
                s.html(json.content);
                $('.ads-plan-description').html(json.description);
                s.css('opacity', 1);
            }
        })
    }
}

function ads_update_plan_description(t) {
    var select = $(t);
    var id = select.val();
    $.ajax({
        url : baseUrl + 'ads/load/description?id=' + id + '&csrf_token=' + requestToken,
        success: function(data) {
            $('.ads-plan-description').html(data);
        }
    })
}

function ads_update_title(t) {
    var obj = $(t);
    $('.ads-title').each(function() {
        $(this).html(obj.val())
    })
}

function ads_update_description(t) {
    var obj = $(t);
    $('.ads-description').each(function() {
        $(this).html(obj.val())
    })
}

function ads_change_display(t) {
    var obj = $(t);
    var c = obj.data('class');
    $('.ads-vertical-display').hide();
    $('.ads-horizontal-display').hide();
    $(c).fadeIn();
    $('.ads-nav-tabs a').each(function() {
        $(this).removeClass('active')
    })
    obj.addClass('active');

    return false;
}

function ads_load_page(t) {
    var obj = $(t);
    var v = obj.val();
    if (v) {
        $.ajax({
            url : baseUrl + 'ads/load/page?id=' + v + '&csrf_token=' + requestToken,
            success : function(data) {
                var json = jQuery.parseJSON(data);
                $("#ads-title-input").val(json.title);
                $("#ads-desc-input").val(json.description);
                $('.ads-title').each(function() {
                    $(this).html(json.title)
                });

                $('.ads-description').each(function() {
                    $(this).html(json.description)
                });

                var img = $(".ads-create-img");
                img.each(function() {
                    $(this).attr('src', json.avatar);
                    $(this).fadeIn();
                });
                $("#ads-link-input").val(json.link);

            }
        })
    }
}

function ads_toggle_countries(t) {
    var obj = $(t);
    if (obj.prop('checked')) {
        $('.ads-country-lists-container .country-lists input').each(function() {
            $(this).prop('checked', 'checked');
        });
    } else {
        $('.ads-country-lists-container .country-lists input').each(function() {
            $(this).prop('checked', '');
            $(this).removeAttr('checked')
        });
    }
}

function ads_enable_activate() {
    $("#ads-activate-input").val(1);
    $("#ads-form").submit();
    return false;
}

function ads_process(t) {
    var form = $(t);
    var indicator = $("#ads-indicator");
    indicator.fadeIn();

    var iC = $("#ads-form-input-container");
    iC.css('opacity', '0.4');
    form.ajaxSubmit({
        url : form.attr('action'),
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if (json.status == 0) {
                notifyError(json.message);
                indicator.hide();
                iC.css('opacity', 1);
            } else{
                notifySuccess(json.message);
                window.location = json.link;
            }
        },
        error : function() {
            indicator.hide();
            iC.css('opacity', 1);
        }
    });
    return false;
}

function ads_process_save(t){
    var form = $(t);
    var indicator = $("#ads-indicator");
    indicator.fadeIn();

    var iC = $("#ads-form-input-container");
    iC.css('opacity', '0.4');
    form.ajaxSubmit({
        url : form.attr('action'),
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if (json.status == 0) {
                notifyError(json.message);
                indicator.hide();
                iC.css('opacity', 1);
            } else{
                notifySuccess(json.message);
                window.location = json.link;
            }
        },
        error : function() {
            indicator.hide();
            iC.css('opacity', 1);
        }
    });
    return false;
}

$(function() {
    $(document).on('click', '.ads-click', function() {
        $.ajax({
            url : baseUrl + 'ads/clicked?id=' + $(this).data('id') + '&csrf_token=' + requestToken,
        });
    });
})