function social_switch_service() {

    var importedContacts = $("#social-imported-contacts-pane");
    importedContacts.html('').hide();
    var servicesContainer = $("#social-import-services-pane");
    servicesContainer.show();
    return false;
}

function social_invite_user(t) {
    var button = $(t);
    var email = button.data('email');
    button.css('opacity', '0.6');
    $.ajax({
        url : baseUrl + 'social/invite/user?email=' + email + '&csrf_token=' + requestToken,
        success : function(d) {
            button.fadeOut();
        }
    });
}

$(function() {
    $(document).on('click', '.social-invite-all-button', function() {
        $('.social-invite-button').each(function() {
            social_invite_user(this);
        }) ;
        $(this).hide();
        return false;
    });
    $(document).on('click', '.facebook-send-dialog', function() {
        width = 700;
        height = 500;
        leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
        //Allow for title and status bars.
        topPosition = (window.screen.height / 2) - ((height / 2) + 50);
        var link  = "http://www.facebook.com/dialog/send?app_id=" + $(this).data('app-id') + "&link=" + $(this).data('link') + "&redirect_uri=" + $(this).data('link');
         window.open(link, "getcontacts", "resizable=no,scrollbar=no,left="+leftPosition+",top="+topPosition+",height=500,width=700");

        return false;
    });

    $(document).on("click", ".social-search-contact-button", function() {
        width = 700;
        height = 500;
        leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
        //Allow for title and status bars.
        topPosition = (window.screen.height / 2) - ((height / 2) + 50);
        window.gmailContact = window.open($(this).data('url'), "getcontacts", "resizable=no,scrollbar=no,left="+leftPosition+",top="+topPosition+",height=500,width=700");
        window.contactLoading = true;
        window.gmailInterval = setInterval(function() {
            if (window.gmailContact.closed && window.contactLoading) {
                window.contactLoading = false;
                //do other
                $.get(baseUrl + 'social/confirm/import', function(data) {
                    if (data == 1) {

                        $.get(baseUrl + 'social/get/imports', function(data) {
                            var servicesContainer = $("#social-import-services-pane");
                            servicesContainer.hide();
                            var importedContacts = $("#social-imported-contacts-pane");
                            importedContacts.html(data).fadeIn();
                        });

                    }  else {
                        //alert(data)
                    }
                });
                clearInterval(window.gmailInterval)
            }
        }, 100)
        return false;
    });
});