function marketplace_file_chooser(id) {
    var input = $(id);
    input.click();
    return false;
}

function tagscroll() {
    if(!document.getElementById('listing-tags')){return false;}
    this.e = document.getElementById('listing-tags');
    this.psint = 1000;
    this.scrlint = 17;
    this.scrollleft = function (){
        e.scrollLeft--;
        if(e.scrollLeft == 0){
            this.t = setTimeout(scrollright, psint);
        }
        else {
            this.t = setTimeout(scrollleft, scrlint);
        }
    }
    this.scrollright = function (){
        e.scrollLeft++;
        if(e.scrollLeft >= (e.scrollWidth - e.clientWidth)){
            this.t = setTimeout(scrollleft, psint);
        }
        else{
            this.t = setTimeout(scrollright, scrlint);
        }
    }
    this.t = setTimeout(scrollright, psint);
}

tagscroll();