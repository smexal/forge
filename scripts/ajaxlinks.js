var ajaxlinks = {

    init : function() {
        $("a.ajax").each(function() {
            $(this).unbind("click").on("click", function(e) {
                e.preventDefault();
                var target = $(this).closest($(this).data('target'));
                setLoading($(this), "transparent");
                /*
                var form_data = $(this).serialize();
                $.ajax({
                    method: 'POST',
                    data: form_data,
                    url: $(this).attr("action")
                }).done(function(data) {
                    hideLoading(target, function() {
                        try {
                            json = $.parseJSON(data);
                            if(json.action == 'redirect') {
                                redirect(json.target);
                            }
                        } catch (e) {
                            target.addClass("fadeIn");
                            target.html(data);
                            $(document).trigger("ajaxReload");
                            setTimeout(function() {
                                target.addClass("now");
                            }, 30);
                        }
                    });
                });*/
            });
        });
    }

};

$(document).ready(ajaxlinks.init);
$(document).on("ajaxReload", ajaxlinks.init);