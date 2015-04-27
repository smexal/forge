function setLoading(element, type) {
    if(typeof(type) == 'undefined') {
        element.addClass("loading");
        var loading = $('<img src="../images/default-loading.gif" />');
        element.html(loading);
        var top = element.outerHeight(true)/2
        if(top < 50) {
            top = element.parent().outerHeight(true)/2;
        }
        loading.css({
            'position' : 'absolute',
            'left': '50%',
            'marginLeft' : loading.width()/2*-1,
            'top' : top,
            'marginTop' : loading.parent().height()/2*-1
        });
    }
}

function hideLoading(element, callback) {
    element.find("img").addClass("fadeOut");
    setTimeout(callback, 250);
}