var ajaxlinks = {

    init : function() {
        ajaxlinks.updateContainer();
        ajaxlinks.ajaxLinks();
    },

    updateContainer : function() {
        $(".update-container").each(function() {
            if($(this).hasClass("done"))
                return;
            var container = $(this);
            container.css({
                "maxHeight": $(window).height()/1.6,
                "overflow": "auto"
            });
            var xhr = new XMLHttpRequest();
            xhr.open('GET', $(this).data('url') + "?forceAjax=true", true);
            xhr.send(null);
            var timer;
            timer = window.setInterval(function() {
                var data = $(xhr.responseText);
                var updater = data.filter(".bar-updater:last");
                if(updater.length > 0) {
                    updateProgressBar(updater);
                }
                container.html(data);

                // done
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    container.addClass("done");
                    window.clearTimeout(timer);
                    var target = container.data('finished-target');
                    if(typeof target !== 'undefined') {
                        redirect(target);
                    }
                }
            }, 500);
        });
    },

    ajaxLinks : function() {
        $("a.ajax").each(function() {
            $(this).unbind("click").on("click", function(e) {
                e.preventDefault();
                setLoading($(this), "revert");
                if($(this).hasClass("slidein") || $(this).hasClass("confirm")) {
                    // load sidebar with confirm dialog
                    $(this).data("open", $(this).attr('href'));
                    var button = $(this);
                    // because if it's a link in a overlay replacing its content,
                    // we have to save the required button data to a new object.
                    var fakeButton = $("<button>");
                    fakeButton.data('open', $(this).attr('href'));
                    if($(this).hasClass('big-overlay')) {
                        fakeButton.addClass('big-overlay');
                    }
                    var the_overlay = overlay.prepare();
                    overlay.open(fakeButton, the_overlay);
                } else {
                    var formdata = {};
                    if($(this).hasClass('form')) {
                        tinyMCE.triggerSave();
                        var formdata = $('#' + $(this).attr('data-form')).serialize();
                    }
                    var btn = $(this);
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr("href"),
                        data : formdata
                        }).done(function(data) {
                            try {
                                if(data.indexOf("<") == 0) {
                                    var data = $(data);
                                    // just replace the "ajax container"
                                    $("body").find(".ajax-reload-container").replaceWith(data.find(".ajax-reload-container"));
                                    $(document).trigger("ajaxReload");
                                }
                                if(typeof(data) !== 'object') {
                                    json = $.parseJSON(data);
                                } else {
                                    json = data;
                                }
                                if(json.action == 'redirect') {
                                    redirect(json.target);
                                }
                                if(json.action == 'refresh' || json.action == "update") {
                                    if($('#'+json.target).length > 0) {
                                        if(json.action == "update") {
                                            $('#'+json.target).html(json.content);
                                        } else {
                                            $('#'+json.target).replaceWith(json.content);
                                        }
                                    }
                                    $(document).trigger("ajaxReload");
                                }
                                hideLoading(btn);
                            } catch (e) {
                                console.error("requires json action", e);
                            }
                        });
                    }
            });
        });
    }
};

$(document).ready(ajaxlinks.init);
$(document).on("ajaxReload", ajaxlinks.init);
