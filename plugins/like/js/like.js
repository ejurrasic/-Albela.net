function like_item(type, typeId) {
    var o = $(".like-button-" + type + '-' + typeId);
    var count = $(".like-count-" + type + '-' + typeId);
    var status = o.attr('data-status');
    var dislike = $(".dislike-button-" + type + '-' + typeId);
    var dislikeCount = $(".dislike-count-" + type + '-' + typeId);
    if (status == 0) {
        //we want to like
        dislike.removeClass('disliked');
        dislike.attr('data-status', 0);
        o.addClass('liked').attr('data-status', 1);
        w = 1;
    } else {
        //we want to dislike
        o.removeClass('liked');
        o.attr('data-status', 0);
        w = 0;
    }

    $.ajax({
        url : baseUrl + 'like/item?type=' + type + '&type_id=' + typeId + '&w=' + w + '&action=like&csrf_token=' + requestToken,
        type : 'GET',
        success : function(data) {
            var json = jQuery.parseJSON(data);
            count.html(json.likes);
            dislikeCount.html(json.dislikes);
        }
    });
    return false;
}

function dislike_item(type, typeId) {
    var o = $(".dislike-button-" + type + '-' + typeId);
    var count = $(".dislike-count-" + type + '-' + typeId);
    var likeO = $(".like-button-" + type + '-' + typeId);
    var likeCount = $(".like-count-" + type + '-' + typeId);
    var status = o.attr('data-status');

    if (status == 0) {
        //we want to dislike
        likeO.removeClass('liked');
        likeO.attr('data-status', 0);
        o.addClass('disliked').attr('data-status', 1);
        w = 1;
    } else {
        //we want to dislike
        o.removeClass('disliked');
        o.attr('data-status', 0);
        w = 0;
    }

    $.ajax({
        url : baseUrl + 'like/item?type=' + type + '&type_id=' + typeId + '&w=' + w + '&action=dislike&csrf_token=' + requestToken,
        type : 'GET',
        success : function(data) {
            var json = jQuery.parseJSON(data);
            count.html(json.dislikes);
            likeCount.html(json.likes);
        }
    })

    return false
}

function show_likes(type, typeId) {
    var modal = $('#photoViewer');
    modal.modal('hide'); //hide photo viewer is open to prevent collission
    var m = $("#likesModal");
    var modal = $('#photoViewer');
    modal.modal('hide');
    var title = m.find('.modal-title');
    title.html(title.data('like'));
    m.modal("show");
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url : baseUrl + 'like/load/people?type='+type + "&id=" + typeId+'&action=1&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}

function show_dislikes(type, typeId) {
    var m = $("#likesModal");
    var modal = $('#photoViewer');
    modal.modal('hide');
    var title = m.find('.modal-title');
    title.html(title.data('dislike'));
    m.modal("show");
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url : baseUrl + 'like/load/people?type='+type + "&id=" + typeId+'&action=0&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}

function show_reactors(t,type, typeId) {
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
        url : baseUrl + 'like/load/people?type='+type + "&id=" + typeId+'&action=3&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}

function react(type, typeId, t) {
    var ob = $('.reactors-' + type + '-' + typeId);
    ob.css("opacity",'0.6');

    $.ajax({
        url : baseUrl + 'like/react?type=' +type + '&id=' + typeId + '&code=' + t + '&csrf_token=' + requestToken,
        success : function(data) {
            //we can refresh the stats
            ob.css("opacity",1).html(data);
            reloadInits();
        }
    });
}

$(function() {
    $(document).on('mouseover', '.react-button', function() {
        var t = $(this);
        var target = t.data('target');
        var pane = $(".react-items-" +target);
        pane.fadeIn();
    });

    $(document).on('click', '.react-items a', function() {
        var $obj = $(this);
        var $type = $obj.data('type');
        var $typeId = $obj.data('target');
        var $code = $obj.data('code');
        var $b = $(".react-button-" + $type + '-' + $typeId);
        $b.addClass('liked');
        react($type, $typeId, $code);
        $('.react-items').fadeOut();
        return false;
    });

    $(document).on('click', '.react-button', function() {
        var $obj = $(this);
        var $type = $obj.data('type');
        var $typeId = $obj.data('target')
        if ($obj.hasClass('liked')) {
            react($type,$typeId, 0);
            $obj.removeClass('liked');
        } else {
            $obj.addClass('liked');
            react($type,$typeId, 1);
        }
        return false;
    });

    $(document).on('mousemove', 'body', function(e) {
        if(!$(e.target).closest($('.feed-react')).length ) $('.react-items').fadeOut();
    });
});
