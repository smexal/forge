var forge_tinymce = {

    init : function() {
        var url = $("textarea.tinymce").attr('data-style');
        var link_url = $("textarea.tinymce").attr('data-link-list');
        var styles = false;
        if( typeof($("textarea.tinymce").attr('data-formats')) != 'undefined') {
            styles = jQuery.parseJSON($("textarea.tinymce").attr('data-formats'))
        }
        /**
         * Available Editor Controllers: https://www.tinymce.com/docs/advanced/editor-control-identifiers/
         */
        tinymce.remove();
        tinymce.init({
            selector:'textarea.tinymce',
            toolbar: 'undo redo | styleselect | bold italic | link | alignleft aligncenter alignright alignjustify | numlist bullist',
            plugins: "link lists",
            height : "480",
            menu: {
              edit: {title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall'},
              format: {title: 'Format', items: 'bold italic link | formats | removeformat'},
              table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'}
            },
            content_css: url,
            style_formats: styles,
            convert_urls : false,
            link_list: function(success) {
                $.ajax({
                    method: 'GET',
                    url: link_url
                }).done(function(data) {
                    $(data).each(function() {
                        console.log($(this)[0].value);
                    });
                    success(data);
                });
            }
        });
    }
};

$(document).ready(forge_tinymce.init);
$(document).on("ajaxReload", forge_tinymce.init);

/*example from tinymce
style_formats: [
    { title: 'Bold text', inline: 'strong' },
    { title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
    { title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
    { title: 'Badge', inline: 'span', styles: { display: 'inline-block', border: '1px solid #2276d2', 'border-radius': '5px', padding: '2px 5px', margin: '0 2px', color: '#2276d2' } },
    { title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }
],
formats: {
    alignleft: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left' },
    aligncenter: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center' },
    alignright: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right' },
    alignfull: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full' },
    bold: { inline: 'span', 'classes': 'bold' },
    italic: { inline: 'span', 'classes': 'italic' },
    underline: { inline: 'span', 'classes': 'underline', exact: true },
    strikethrough: { inline: 'del' },
    customformat: { inline: 'span', styles: { color: '#00ff00', fontSize: '20px' }, attributes: { title: 'My custom format' }, classes: 'example1' },
}
*/
