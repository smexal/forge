var table = {

    init : function() {
        $(document).find("table tr td").each(function() {
            var action = $(this).attr('data-main-action');
            if(typeof(action) === 'undefined' || action.length == 0) {
                return;
            }
            $(this).closest('tr').addClass('hasAction');
            $(this).closest('tr').unbind('click').on('click', function() {
                if($(this).data('action') === true)
                    return;

                $(this).data('action', true);
                $(this).find("a[href='"+action+"']").get(0).click();
            });
        });
    },
};

$(document).ready(table.init);
$(document).on("ajaxReload", table.init);