/**
 * dropzone configuration for forge
 */

Dropzone.options.forgedropzone = {
    init: function () {
        this.on("complete", function (file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                redirect($("#forgedropzone").attr('data-redirect'));
            }
        });
    }
};

var forge_dropzone = {
    init : function() {
        $("#forgedropzone").dropzone();
    }
}
//$(document).ready(forge_dropzone.init);
$(document).on("ajaxReload", forge_dropzone.init);
