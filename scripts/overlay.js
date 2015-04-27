var overlay = {
    init : function() {
        $(".open-overlay").each(function() {
            $(this).unbind("click").on("click", function() {
                var the_overlay = overlay.prepare();
                overlay.open($(this), the_overlay);
            });
        });
    },

    prepare : function() {
        var the_overlay = false;
        if($(".overlay-right").length > 0) {
            the_overlay = $(".overlay-right");
        } else {
            the_overlay = $(
                "<div class='overlay-right'>"+
                    "<span class='close glyphicon glyphicon-menu-right' aria-hidden='true'></span>"+
                    "<div class='content'></div>"+
                "</div>").appendTo("body");
        }
        the_overlay.find(".content").html('');
        the_overlay.find("> .close").on("click", function() {
            the_overlay.removeClass("show");
        });
        the_overlay.height($(window).height());
        $(window).resize(function() {
            the_overlay.height($(window).height());
        })
        return the_overlay;
    },

    open : function(button, the_overlay) {
        setLoading(the_overlay.find(".content"));
        the_overlay.addClass('show');
        $.ajax({
          url: button.data('open'),
          context: the_overlay.find(".content")
        }).done(function(data) {
            var content = $(data);
            var context = $(this);
            hideLoading(the_overlay, function() {
                content.addClass("fadeIn");
                context.html( content );
                setTimeout(function() {
                    content.addClass("now");
                }, 30);
            });
        });
    }
};
$(document).ready(function() {
    overlay.init();
})