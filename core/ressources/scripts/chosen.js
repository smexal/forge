var forge_chosen = {

    init : function() {
        $(".chosen-select").each(function() {
            $(this).attr('data-placeholder', $(this).closest('.form-group').find("label").filter(":first").text());
            $(this).chosen();
        });
    }
};

$(document).ready(forge_chosen.init);
$(document).on("ajaxReload", forge_chosen.init);