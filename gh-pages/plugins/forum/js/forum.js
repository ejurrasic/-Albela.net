function forumReply(repliedId){
    forumInitEditor('#postbox-' + repliedId);
    forumInitEditor('#postbox-' + repliedId);
    var replyFormWrapper = $('#forum-reply-form-wrapper-' + repliedId);
    var replyDashboard = $('#forum-reply-dashboard-' + repliedId);
    $(replyDashboard).fadeToggle("fast");
    $(replyFormWrapper).fadeToggle("fast");
}

function forumEditReply(replyId){
    forumInitEditor('#editpostbox-' + replyId);
    forumInitEditor('#editpostbox-' + replyId);
    var EditReplyFormWrapper = $('#forum-edit-reply-form-wrapper-' + replyId);
    var replyDashboard = $('#forum-reply-dashboard-' + replyId);
    var post = document.querySelector('#forum-post-' + replyId).innerHTML;
//    document.querySelector('#editpostbox-' + replyId).innerHTML = post;
//    document.querySelector('#editpostbox-' + replyId + '_ifr').contentWindow.document.body.innerHTML = post;
    $(replyDashboard).fadeToggle("fast");
    $(EditReplyFormWrapper).fadeToggle("fast");
}

function forumDeleteReply(replyId, threadId){
    $.get(baseUrl + 'forum/ajax?action=post',
        {
            val: true,
            thread_id: threadId,
            id: replyId,
            postbox: document.querySelector('#forum-post-' + replyId).innerHTML,
            type: 'delete_post'
        },
        function(data, status){
            //alert("Data: " + data + "\nStatus: " + status);
            $('#forum-reply-wrapper-' + replyId).fadeOut('fast');
        });
    }

function forumAjaxSubmitForm(postForm){
	var id = postForm.elements['id'].value;
	var type = postForm.elements['type'].value;
    var formWrapper = $('#forum-reply-form-wrapper-' + id);
    if(id == 0){
        postBox = 'postbox';
        contentId = 'forum-replies';
    }
    else if (type == 'reply_thread'){
        postBox = 'postbox';
        contentId = 'forum-sub-replies-' + id;
    }
    else if (type == 'edit_post'){
        postBox = 'editpostbox';
        contentId = 'forum-post-' + id;
    }
    postForm.elements['postbox'].value = document.querySelector('#' + postBox + '-' + id + '_ifr').contentWindow.document.body.innerHTML;
	$(postForm).ajaxSubmit({
        url : baseUrl + 'forum/ajax?action=post',
        type : 'POST',
        beforeSend : function() {
            $(postForm).css('opacity', '0.5');
        },
        success : function(r) {
		    $('#' + contentId).html(r);
            if (type == 'reply_thread'){
                forumReply(id);
                postForm.elements['postbox'].value = "";
                postForm.elements['postbox'].innerHTML = "";
                document.querySelector('#' + postBox + '-' + id + '_ifr').contentWindow.document.body.innerHTML = "";
            }
            else if (type == 'edit_post'){
                forumEditReply(id);
            }
            $(postForm).css('opacity', '1.0');
            forumRefreshPagination();
        },
        error : function(x) {
			alert('Error submitting form');
            $(postForm).css('opacity', '1.0');
        }
    })
    return false;
}

function forumSubmitForm(postForm){
	postForm.submit();
}

function makeRequest(url, id){
    url += '&csrf_token=' + requestToken;
	$("#"+ id).load(url);
}