var messages = {
    fadeAfter : 2500,
    fadeoutTimer : false,

    init : function() {
        messages.containerFade();
    },

    containerFade : function() {
        $(".message-container .alert").each(function() {
            var message = $(this);
            messages.fadeoutTimer = setTimeout(function() {
                message.fadeOut(400);
            }, messages.fadeAfter);
        });
        $(".message-container .alert").each(function() {
            $(this).hover(function() {
                clearTimeout(messages.fadeoutTimer);
                if(! $(this).data('hasClose')) {
                    $(this).data('hasClose', true);
                    var close = $('<i class="close material-icons">close</i>');
                    $(this).append(close);
                    close.on('click', function() {
                        $(this).closest('.alert').fadeOut(200);
                    });
                }
            })
        });
    }
};

$(document).ready(messages.init);
$(document).on("ajaxReload", messages.init);