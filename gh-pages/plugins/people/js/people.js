
function people_submit_search() {
    var url = baseUrl + 'people';
    var params = [];
    if(document.getElementById('people-keywords-input') && document.getElementById('people-keywords-input').value != '') params.push('keywords=' + document.getElementById('people-keywords-input').value);
    if(document.getElementById('people-gender-select') && document.getElementById('people-gender-select').value != 'both') params.push('gender=' + document.getElementById('people-gender-select').value);
    if(document.getElementById('people-online-select') && document.getElementById('people-online-select').value != 'both') params.push('online_status=' + document.getElementById('people-online-select').value);
    if(params.length > 0) url += '?' + params.join('&');
    loadPage(url);
    return false;
}

function people_set_list_type(type) {
    $.ajax({url: baseUrl + 'people/ajax?action=set_list_type&type=' + type + '&csrf_token=' + requestToken});
}