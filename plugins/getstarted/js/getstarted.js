function getstarted_show_avatar() {
    var image = document.getElementById("getstarted-image-input");
    for(i = 0; i < image.files.length; i++) {
        if (typeof FileReader != "undefined") {

            var reader = new FileReader();
            reader.onload = function(e) {
                var img = $("#getstarted-avatar");
                img.css('background-image', 'url(' + e.target.result + ')');
            }
            reader.readAsDataURL(image.files[i]);
        }
    }
}