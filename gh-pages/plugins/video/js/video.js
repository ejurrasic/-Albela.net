function change_video_source(t) {
    var o = $(t);
    var v = o.val();
    $(".video-source-selector .source").hide();
    $(".video-source-selector  ." + v).fadeIn();
    if(v == 'upload') {
        $('.video-details-container').show();
    } else {
        $('.video-details-container').hide();
    }
    return true;
}


function video_form_list_url() {
    var form = $('#video-list-search');
    var v = form.find('input[type=text]').val();
    var cat = $("#video-category-list").val();

    var type = form.find('.video-type-input').val();
    var filter = $("#video-filter-select").val();
    var url = baseUrl + 'videos?term=' + v + "&category=" + cat + "&type=" + type + '&filter=' + filter;

    return url;
}
function video_submit_search(t) {
    url = video_form_list_url();
    loadPage(url);
    return false;
}

function video_list_change_category(t) {
    url = video_form_list_url();
    loadPage(url);
}