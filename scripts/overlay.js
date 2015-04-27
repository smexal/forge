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
        if($(".overlay").length > 0) {
            the_overlay = $(".overlay");
        } else {
            the_overlay = $("<div class='overlay-right'></div>").appendTo("body");
        }
        the_overlay.html('');
        the_overlay.height($(window).height());
        $(window).resize(function() {
            the_overlay.height($(window).height());
        })
        return the_overlay;
    },


    open : function(button, the_overlay) {
        the_overlay.addClass('loading');
        the_overlay.addClass('show');
        setTimeout(function() {
            $.ajax({
              url: button.data('open'),
              context: the_overlay
            }).done(function(data) {
              $( this ).html( data );
            });
        }, 1500);
    }
};
$(document).ready(function() {
    overlay.init();
})