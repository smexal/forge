var overlay = {
    init : function() {
        $(".open-overlay").each(function() {
            $(this).unbind("click").unbind("click").on("click", function() {
                var the_overlay = overlay.prepare();
                overlay.open($(this), the_overlay);
            });
        });
    },

    prepare : function() {
        var the_overlay = false;
        if($(".overlay-container").length > 0) {
            the_overlay = $(".overlay-container");
        } else {
            the_overlay = $(
                "<div class='overlay-container'><div class='cover'></div><div class='overlay-right'>"+
                    "<span class='close glyphicon glyphicon-menu-right' aria-hidden='true'></span>"+
                    "<div class='content'></div>"+
                "</div></div>").appendTo("body");
        }
        the_overlay.find(".content").html('');
        the_overlay.find(".overlay-right > .close").unbind("click").on("click", overlay.hide);
        return the_overlay;
    },

    open : function(button, the_overlay) {
        setLoading(the_overlay.find(".content"));
        setTimeout(function() {
            the_overlay.addClass('show');
        }, 50);
        $.ajax({
          url: button.data('open'),
          context: the_overlay.find(".content")
        }).done(function(data) {
            var content = $(data);
            var context = $(this);
            hideLoading(the_overlay, function() {
                content.addClass("fadeIn");
                context.html(content);
                $(document).trigger("ajaxReload");
                setTimeout(function() {
                    content.addClass("now");
                }, 30);
            });
        });
    }, 
    
    /* searches for overlay and hides all. */
    hide : function() {
        $(".overlay-container").each(function() {
            var container = $(this);
            container.removeClass("show");
            setTimeout(function() {
                container.remove();
            }, 300);
        });
    }
};
$(document).ready(overlay.init);
$(document).on("ajaxReload", overlay.init);