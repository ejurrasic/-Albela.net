window.last_search = '';
function process_search_dropdown() {
    var input = $("#header-search-input");
    var dropdown = $("#search-dropdown");
    var fullSearch = $('#search-dropdown-full-search-button');
    var dContent  = $('.search-dropdown-result-container');
    var indicator = $('#search-dropdown-indicator');
    if (input.val().length > 1) {
        dropdown.fadeIn();
        fullSearch.find('span').html(input.val());
        fullSearch.prop('href', baseUrl + 'search?term=' + encodeURIComponent(input.val()) + '&csrf_token=' + requestToken);
        if (window.last_search != input.val()) {
            indicator.fadeIn();
            $.ajax({
                url : baseUrl + 'search/dropdown?csrf_token=' + requestToken,
                data : {term:input.val()},
                success : function(data) {
                    dContent.html(data);
                    indicator.fadeOut();
                }
            })
        }
        window.last_search = input.val();
    } else {
        dropdown.fadeOut();
    }
}

$(function() {
    $(document).click(function(e) {
        if (!$(e.target).closest("#header-search").length){
            $("#search-dropdown").fadeOut();
        }
    });
})