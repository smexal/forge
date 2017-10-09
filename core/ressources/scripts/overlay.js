var overlay = {
    init : function($context) {
        $context = typeof $context == 'undefined' ? $('body') : $context;
        $context.find(".open-overlay").each(function() {
            $(this).unbind("click").on("click", function() {
                var the_overlay = overlay.prepare();
                overlay.open($(this), the_overlay);
            });
        });

        $context.find(".close-overlay").each(function() {
            $(this).unbind("click").on("click", function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var value = $("#" + $(this).attr('data-open')).val();
                var target = $("input#" + $(this).attr('data-target')).val(value);
                target.triggerHandler('overlay.change', {value: value, target: target, source: this})
                overlay.hide();
            });
        })

        overlay.fullscreen($context);
    },

    fullscreen : function($context) {
        $context = typeof $context == 'undefined' ? $('body') : $context;

        $context.find("a.fullscreen-overlay").each(function() {
            $(this).unbind('click').on("click", function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var ov = false;
                if($(".full-overlay-container").length > 0) {
                    ov = $(".full-overlay-container");
                } else {
                    ov = $("<div class='full-overlay-container show'><div class='cover'></div><div class='overlay-full'>"+
                            "<div class='content'></div>"+
                        "</div></div>").appendTo("body");
                }


                $.ajax({
                  url: $(this).attr('href')
                }).done(function(data) {
                    ov.find(".content").html(data);
                    $(document).trigger("ajaxReload");

                    // bind "escape" key to close the overlay
                    $(document).keyup(function(e) {
                        if(e.keyCode == 27) {
                            overlay.hide();
                        }
                    });
                    ov.find(".cover").on('click', function() {
                        overlay.hide();
                    });
                });

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
                    "<i class='material-icons close'>close</i>"+
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
        if(button.hasClass("big-overlay")) {
            the_overlay.find(".overlay-right").addClass("big");
        }
        $.ajax({
          url: button.data('open'),
          context: the_overlay.find(".content")
        }).done(function(data) {
            try {
                json = $.parseJSON(data);
                if(json.action == 'redirect') {
                    redirect(json.target, "in_overlay");
                }
            } catch(e) {
                overlay.setContent($(data), the_overlay);
            } 
        });
        // bind "escape" key to close the overlay
        $(document).keyup(function(e) {
            if(e.keyCode == 27) {
                overlay.hide();
            }
        });
        the_overlay.find(".cover").on('click', function() {
            overlay.hide();
        });
    },

    setContent : function(content, overlay) {
        hideLoading(overlay, function() {
            content.addClass("fadeIn");
            overlay.find(".content").html(content);
            $(document).trigger("ajaxReload");
            setTimeout(function() {
                content.addClass("now");
            }, 30);
        });
    },

    /* searches for overlay and hides all. */
    hide : function() {
        var full = false;
        $(".full-overlay-container").each(function() {
            full = true;
            var container = $(this);
            container.removeClass("show");
            setTimeout(function() {
                container.remove();
            }, 300);
        });
        if(full)
            return;
        $(".overlay-container").each(function() {
            var container = $(this);
            container.removeClass("show");
            setTimeout(function() {
                container.remove();
            }, 300);
        });
    }
};

$(document).ready(function() {
    overlay.init();
});
$(document).on("ajaxReload", function()  {
    overlay.init();
});
