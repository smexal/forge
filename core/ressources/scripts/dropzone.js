/**
 * dropzone configuration for forge
 */

Dropzone.options.forgedropzone = {
    init: function () {
        this.on("complete", function (file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                var inOverlay = $(this).attr('data-overlay');
                console.log(inOverlay);
                redirect($(this).attr('data-redirect'), inOverlay);
            }
        });
    }
};

var forge_dropzone = {
    init : function() {
        if($('.overlay-container').length > 0) {
            $('.overlay-container').find(".dropzone").each(function() {
                try {
                    $(this).dropzone();
                } catch(e) { /* ignore this error... */ }
            });
        }
        try {
            $("#forgedropzone").dropzone();
        } catch(e) { /* not important error, that it is already initiated. */ }
    }
}
//$(document).ready(forge_dropzone.init);
$(document).on("ajaxReload", forge_dropzone.init);
