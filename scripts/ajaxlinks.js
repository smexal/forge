var ajaxlinks = {

    init : function() {
        $("a.ajax").each(function() {
            $(this).unbind("click").on("click", function(e) {
                e.preventDefault();
                setLoading($(this), "transparent");
                if($(this).hasClass("confirm")) {
                    // load sidebar with confirm dialog
                    $(this).data("open", $(this).attr('href'));
                    var the_overlay = overlay.prepare();
                    overlay.open($(this), the_overlay);
                } else {
                    $.ajax({
                        method: 'POST',
                        confirm : $(this).hasClass("confirm"),
                        url: $(this).attr("href")
                    }).done(function(data) {
                        try {
                            json = $.parseJSON(data);
                            if(json.action == 'redirect') {
                                redirect(json.target);
                            }
                        } catch (e) {
                            console.error("requires json action");
                        }
                    });
                }
            });
        });
    }

};

$(document).ready(ajaxlinks.init);
$(document).on("ajaxReload", ajaxlinks.init);