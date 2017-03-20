function get_my_content(div,type){
   //document.getElementById('boost-content').innerHTML = div;
   $('#current_boost_id').val(div);

    $.ajax({
        url: baseUrl+'boostgetpost?pdi='+div+"&type="+type,
        success: function(data){
           //alert(data);
            document.getElementById('booster-header').innerHTML = type;
          document.getElementById('boost-content').innerHTML = data;

        }

    })


}

function admin_delete_pb(id){

    $.ajax({
        url: baseUrl + "admincp/booster/delete?id="+id,
        type:"GET",
        success: function(data){
           // alert(data);
           document.getElementById(id).style.display="none";
        }
    });

}


function boost_change_display(t,type) {
    var obj = $(t);
    var c = obj.data('class');
    $('.ads-vertical-display').hide();
    $('.ads-horizontal-display').hide();
    $(c).fadeIn();
    $('.ads-nav-tabs a').each(function() {
        $(this).removeClass('active')
    })
    if(type=='mobile'){
        $('.ads-horizontal-display').css({'max-width':"250px",'margin':"10px auto"});
    }else{
        $('.ads-horizontal-display').css({'max-width':"100%"});
    }
    obj.addClass('active');

    return false;
}

function boost_enable_activate() {
    $("#ads-activate-input").val(1);
    $("#boost-form").submit();
    return false;
}

function boost_process(t) {
    var form = $(t);
    var l;
    var indicator = $("#boost-indicator");
    indicator.fadeIn();

    var iC = $("#ads-form-input-container");
    iC.css('opacity', '0.4');
    form.ajaxSubmit({
        url : baseUrl + 'boost/create',
        success: function(data) {
            var json = jQuery.parseJSON(data);

            if (json.status == 0) {
                indicator.hide();
                iC.css('opacity', 1);
                l.fadeIn();
                l.css({"display":"block"});
                l.html(json.message);
                //notifyError(json.message);

            } else{

                window.location = json.link;
                notifySuccess(json.message);
            }

        },
        error : function() {

            indicator.hide();
            iC.css('opacity', 1);

        }
    });
    return false;
}

$(function(){
    $(document).on('click', '.booster-click', function() {

        $.ajax({
            url : baseUrl + 'booster/clicked?id=' + $(this).data('id'),
            type:"GET",
            success: function(data){

            }
        });
    });
    var feed_id = $('#add-id-of-this-feed').val();
    if(feed_id != "undefined"){
        $('#feed-wrapper-'+feed_id).css({"display":"none"});
    }
});


