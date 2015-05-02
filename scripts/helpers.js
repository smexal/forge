function setLoading(element, type) {
    element.addClass("loading");
    var loading = $('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
    element.html(loading);
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
            overlay.hide();
            container.html($(data).find(".ajax-reload-container").html());
            container.append($(data).find(".message-container"));
            $(document).trigger("ajaxReload");
        });
    });
}