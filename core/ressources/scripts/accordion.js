var accordion = {
    init : function() {
        
        $('.panel-group.ajax').each(function() {
            accordion.ajaxLoad($(this));
            $(this).on('shown.bs.collapse', function () {
                accordion.ajaxLoad($(this));
            });
        });
    },

    ajaxLoad : function(panelGroup) {
        panelGroup.find(".collapse.in").each(function() {
            accordion.loadFor($(this).attr("aria-labelledby"), $(this).find(".panel-body").filter(":first"));
        });
    },

    loadFor: function(id, target) {
        console.log(id, target);
    }
}

$(document).ready(accordion.init);
$(document).on("ajaxReload", accordion.init);
