function close_announcement(id) {
    $.ajax({
        url : baseUrl + 'announcement/close?id=' + id + '&csrf_token=' + requestToken,
    })
}