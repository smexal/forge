var ajaxlinks = {

    init : function() {
        $("a.ajax").each(function() {
            $(this).unbind("click").on("click", function(e) {
                e.preventDefault();
                setLoading($(this), "revert");
                if($(this).hasClass("confirm")) {
                    // load sidebar with confirm dialog
                    $(this).data("open", $(this).attr('href'));
                    var the_overlay = overlay.prepare();
                    overlay.open($(this), the_overlay);
                } else {
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr("href")
                    }).done(function(data) {
                        try {
                            json = $.parseJSON(data);
                            if(json.action == 'redirect') {
                                redirect(json.target);
                            }
                            if(json.action == 'refresh') {
                                if($('#'+json.target).length > 0) {
                                    $('#'+json.target).replaceWith(json.content);
                                } else {
                                    $('.'+json.target).replaceWith(json.content);
                                }
                                $(document).trigger("ajaxReload");
                            }
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