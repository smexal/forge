var forge_tinymce = {

    init : function() {
        tinymce.remove();
        tinymce.init({
            selector:'textarea.tinymce',
            plugins: "autoresize"
        });
    }
};

$(document).ready(forge_tinymce.init);
$(document).on("ajaxReload", forge_tinymce.init);
