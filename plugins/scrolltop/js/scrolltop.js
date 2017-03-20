$(window).scroll(function() {
   if ($(window).scrollTop() > 0) {
       $("#scroll-top").fadeIn();
   } else {
       $("#scroll-top").fadeOut();
   }
});

$(function() {
    $(document).on("click", '#scroll-top', function() {
        $("html,body").animate({scrollTop : '0px'},300);
        return false;
    });
})