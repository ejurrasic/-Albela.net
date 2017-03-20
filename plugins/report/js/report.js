$(function() {
    $(document).on('click', '.report-btn', function() {
        var modal = $("#reportModal");
        modal.find('.link').val($(this).data('link'));
        modal.find('.type').val($(this).data('type'));
        modal.modal('show');

        return false;
    });

    $(document).on('submit', '#reportModal form', function() {
        $(this).css('opacity', '0.6');
        var sM = $("#reportModal").data('success');
        var eM = $("#reportModal").data('error');
        $(this).ajaxSubmit({
            url : baseUrl + 'report',
            success : function () {
                $("#reportModal").modal('hide');
                notifySuccess(sM);
                $(this).css('opacity', 1);
            },
            error : function () {
                $("#reportModal").modal('hide');
                notifyError(eM);
            }
        })
        return false;
    });
})