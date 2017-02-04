var messages = {
    fadeAfter : 2500,

    init : function() {
        messages.containerFade();
    },

    containerFade : function() {
        $(".message-container .alert").each(function() {
            var message = $(this);
            setTimeout(function() {
                message.fadeOut(400);
            }, messages.fadeAfter);
        });
    }
};

$(document).ready(messages.init);
$(document).on("ajaxReload", messages.init);