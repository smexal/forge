var forge_dragsort = {
    init : function() {
        $(".dragsort .items").sortable({
            connectWith: ".items",
            placeholder: "placeholder",
            stop: function(event, ui) {

                // correct indetion for too big gaps ( e.g. level-0 > level-3 to level-0 > level-1)
                var previousElementLevel = null;
                ui.item.closest(".ui-sortable").find(">li").each(function() {
                    if(previousElementLevel !== null) {
                        // lower the level if it gaps more then one level
                        if(parseInt($(this).data('level')) - parseInt(previousElementLevel) > 1) {
                            $(this).removeClass(function (index, css) {
                                return (css.match (/(^|\s)level-.+/g) || []).join(' ');
                            });
                            $(this).addClass('level-' +  parseInt(parseInt(previousElementLevel)+1));
                            $(this).data('level', parseInt(previousElementLevel)+1);
                        }
                    } else {
                        $(this).removeClass(function (index, css) {
                            return (css.match (/(^|\s)level-.+/g) || []).join(' ');
                        });
                        $(this).addClass('level-0');
                        $(this).data('level', 0);
                    }
                    previousElementLevel = $(this).data('level');
                });

                // prepare new order for server
                var dataset = [];
                var parent = 0;
                var parentElement = null;
                var orderNo = 0;

                ui.item.closest(".ui-sortable").find(">li").each(function() {
                    if($(this).data('level') > 0) {
                        parent = $(this).prevAll(".level-" + parseInt(parseInt($(this).data('level'))-1)).data('id');
                    } else {
                        parent = 0;
                    }

                    dataset.push({
                        'id' : $(this).data('id'),
                        'order' : orderNo,
                        'parent' : parent
                    });
                    orderNo++;
                });

                // send new data to callback url
                $.ajax({
                    method: 'POST',
                    url: ui.item.closest(".ui-sortable").data('callback'),
                    data : {itemset : dataset}
                }).done(function(data) {
                });
            },
            sort: function(event, ui) {

                var pos;
                if(ui.helper.hasClass('level-1')){
                    pos = ui.position.left+30;
                } else if(ui.helper.hasClass('level-2')) {
                    pos = ui.position.left+60;
                } else if(ui.helper.hasClass('level-3')) {
                    pos = ui.position.left+90;
                } else {
                    pos = ui.position.left;
                }

                // remove level classes
                ui.helper.data('level', '');
                ui.helper.removeClass(function (index, css) {
                    return (css.match (/(^|\s)level-.+/g) || []).join(' ');
                });

                ui.placeholder.removeClass(function (index, css) {
                    return (css.match (/(^|\s)level-.+/g) || []).join(' ');
                });

                // set new level classes
                if(ui.placeholder.index() == 1 || pos <= 50) {
                    ui.placeholder.addClass('level-0');
                    ui.helper.addClass('level-0');
                    ui.helper.data('level', 0);
                } else if(
                    (pos > 50 && pos < 100 && ui.placeholder.index() > 1) ||
                    (pos > 50 && ui.helper.prev().hasClass("level-0"))
                ) {
                    ui.placeholder.addClass('level-1');
                    ui.helper.addClass('level-1');
                    ui.helper.data('level', 1);
                } else if(
                    pos >= 100 && pos < 150 && ui.placeholder.index() > 2 &&
                    (ui.helper.prev().hasClass("level-1") || ui.helper.prev().hasClass("level-2"))
                ) {
                    ui.placeholder.addClass('level-2');
                    ui.helper.addClass('level-2');
                    ui.helper.data('level', 2);
                } else if(
                    pos >= 150 && ui.placeholder.index() > 3 &&
                    (ui.helper.prev().hasClass("level-2") || ui.helper.prev().hasClass("level-3"))
                ) {
                    ui.placeholder.addClass('level-3');
                    ui.helper.addClass('level-3');
                    ui.helper.data('level', 3);
                } else {
                    ui.placeholder.addClass('level-0');
                    ui.helper.addClass('level-0');
                    ui.helper.data('level', 0);
                }
            }
        });
    }
}


$(document).ready(forge_dragsort.init);
$(document).on("ajaxReload", forge_dragsort.init);
