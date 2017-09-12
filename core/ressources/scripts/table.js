var table = {

    init : function() {
        $(document).find("table tr td").each(function() {
            var links = $(this).find('a');
            if (links.length === 0) {
                var action = $(this).parent().attr('data-main-action');
                if (typeof(action) === 'undefined' || action.length === 0) {
                    return;
                }
                $(this).addClass('hasAction');
                $(this).unbind('click').on('click', function () {
                    var links = $(document).find("table tr td a[href='" + action + "'][class*='btn']");
                    links.get(0).click();
                });
            }
        });
    }
};

$(document).ready(table.init);
$(document).on("ajaxReload", table.init);