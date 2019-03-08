var tablebar = {

    init : function() {

        $(".table-editbar").each(function() {
            if(! $(this).data('searchValue') )
                $(this).data('searchValue', '');

            if(! $(this).data('sortValue'))
                $(this).data('sortValue', '');

            if(! $(this).data('filters'))
                $(this).data('filters', []);

            tablebar.search($(this));
            tablebar.sorting($(this));
            tablebar.filters($(this));
        });

    },

    buildQuery : function(bar) {
        var filterVal = '';

        for (var key in bar.data('filters')) {
            if (bar.data('filters').hasOwnProperty(key)) {
                var value = bar.data('filters')[key];
                filterVal+= '&' + value.field + '=' + value.value;
            }
        }

        return '/search' + '?t=' + bar.data('searchValue') + '&s=' + bar.data('sortValue') + filterVal + bar.data('get-string');
    },

    sorting : function(bar) {
        bar.find("select[name='sorting']").unbind('change').on('change', function () {
            bar.data('sortValue', $(this).val());
            tablebar.updateTable(bar);
        });
    },

    search : function(bar) {
        var timeout = false;
        bar.find("input[name='search']").unbind('input').on('input', function () {
            clearTimeout(timeout);
            var input = $(this);
            timeout = setTimeout(function() {
                bar.data('searchValue', input.val());
                tablebar.updateTable(bar);
            }, 400);
        });
    },

    filters : function(bar) {
        bar.find("select[name^='filter__']").each(function() {
            $(this).unbind('change').on('change', function() {
                var val = $(this).val();
                if($.isNumeric($(this).val())) {
                    val = $(this).find("option[value='"+$(this).val()+"']").text();
                }
                if($(this).val() == 0) {
                    val = '';
                }
                bar.data('filters')[$(this).attr('name')] = {
                    field : $(this).attr('name'),
                    value : val
                };
                tablebar.updateTable(bar);
            });
        })
    },

    updateTable : function(bar) {
        $.ajax({
            method: 'GET',
            url: bar.data('api') + tablebar.buildQuery(bar)
        }).done(function(data) {
            $('#' + bar.data('target')).find("tbody").html(data.newTable);
            $(document).trigger("ajaxReload");
        });
    }

};

$(document).ready(tablebar.init);
$(document).on("ajaxReload", tablebar.init);