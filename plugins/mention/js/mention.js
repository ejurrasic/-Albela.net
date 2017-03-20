$(function() {
    $(document).on('keyup', '.mention-input', function() {
        var str = $(this).val();
        var aStr = str.split(' ');
        var o = $(this);
        var container = $(o.data('mention'));
        container.find('.listing').html('').show();
        container.find('.indicator').fadeIn();
        if (aStr.length > 0) {
            var lStr = aStr[aStr.length - 1];
            var nStr = lStr.split('');
            if (lStr.length > 2 && nStr.length > 0) {
                var char = nStr[0];
                if (char.toLowerCase() == '@') {
                    container.fadeIn();
                    $(document).click(function(e) {
                        if(!$(e.target).closest(o.data('mention')).length) container.fadeOut();
                    });
                    $.ajax({
                        url : baseUrl + 'mention/find?csrf_token=' + requestToken,
                        data : {text: lStr.replace('@', '')},
                        case : false,
                        success : function(r) {
                            //window.mentionSuggestion = false;
                            container.find('.indicator').fadeOut();
                            if (r != '') {
                                container.find('.listing').html(r).find('a').click(function() {
                                    var name = $(this).data('tag');
                                    aStr[aStr.length - 1] = name;
                                    var s = '';
                                    for(i = 0; i < aStr.length; i++) {
                                        s += aStr[i] + ' ';
                                    }
                                    o.select().val(s + ' ');

                                    container.fadeOut();
                                    return false;
                                })
                            } else{
                                container.fadeOut();
                            }
                        },
                        error : function() {
                            container.fadeOut();
                        }
                    })
                }
            }
        }
    })
})