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
    if(type == 'transparent') {
        element.addClass("loading");
        var loading = $('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
        element.html(loading);
    }
}

function hideLoading(element, callback) {
    element.find("img").addClass("fadeOut");
    setTimeout(callback, 250);
}

function redirect(target) {
    var container = $(".ajax-reload-container");
    setLoading(container);
    $.ajax({
        method: 'POST',
        url: target
    }).done(function(data) {
        hideLoading(container, function() {
            $(".overlay-right").removeClass("show");
            container.html($(data).html());
            $(document).trigger("ajaxReload");
        });
    });
}