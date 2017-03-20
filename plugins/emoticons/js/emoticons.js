function change_emoticon_list(id, type) {
    var input = $("#search-emoticon-" + id);
    if (type == 0) {
        input.fadeIn().focus();
    } else {
        input.fadeOut();
    }
    $(".emoticon-box ."+id+"-list").hide('fast', function() {
        $("#"+id+"-list-" + type).show();
    });

    return false;
}
function add_emoticon(s, t) {
    var v = $(t).val() + " " + s + " ";
    $(t).val(v).focus();

    $('.emoticon-box').fadeOut();//we need to hide
    return false;
}

$(function () {

    $(document).on('click', '.emoticon-button', function() {
        //let get where the emoticon container is
        var e = $(this).next();
        if (e.length > 0 && e.hasClass('emoticon-box')) {

        } else {
            e = $(this).prev();
        }

        $('body').click(function(ev) {
            if(!$(ev.target).closest('.emoticon-box').length) {
                //alert($(e.target).attr('class'))
                if (!$(ev.target).hasClass('emoticon-button')) {
                    $('.emoticon-box').fadeOut();
                }
                //alert('kola')
            }
        });

        if (e.css('display') == 'none') {
            if (e.html() == '') {
                $.ajax({
                    url : baseUrl + 'emoticon/load?target=' + $(this).data('target') + '&action=' + $(this).data('action') + '&csrf_token=' + requestToken,
                    success : function(data) {
                        e.html(data);
                        reloadInits();
                    }
                })
            }
            e.fadeIn();
        } else {
            e.fadeOut();
        }

        return false;
    });


    $(document).on("click", '.add-emoticon', function() {
        if ($(this).data('action') == 1) return false;
       return add_emoticon($(this).data('symbol'), $(this).data('target'))
    });

    $(document).on('click', ".emoticon-box .switch", function() {
       return change_emoticon_list($(this).data('id'), $(this).data('type'))
    });



    $(document).on("keyup", ".search-emoticon", function() {
        var id = $(this).data('id');
        var term = $(this).val();
        var c = $("#" + id + '-list-0');
        var a = $("." + id + '-list');
        if (term.length > 0) {
            c.show().html('');
            $.ajax({
                url : baseUrl + 'emoticon/search?csrf_token=' + requestToken,
                data : {term:term, target : $(this).data('target')},
                success : function(d){
                    c.html(d);
                    if (d) {
                        a.hide();
                        c.show();
                    }
                }
            })
        }
    });
})