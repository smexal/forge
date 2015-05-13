var forms = {
    init : function() {
        forms.ajax();
        forms.tags();
    },

    tags : function() {
      $("input.tags").each(function() {
        var values = $(this).data('values');
        if( Object.prototype.toString.call( values ) === '[object Array]') {
          forms.tagsInputByValues($(this), values);
        }
      });
    },

    tagsInputByValues : function(element, values) {
      element.tagsinput({
          allowDuplicates: false,
          freeInput: false,
          typeaheadjs: {
              source: substringMatcher(values)
          }
      });
    },

    ajax : function() {
        $("form.ajax").each(function() {
            $(this).on("submit", function(e) {
                e.preventDefault();
                var target = $(this).closest($(this).data('target'));
                setLoading(target);
                var form_data = $(this).serialize();
                $.ajax({
                    method: 'POST',
                    data: form_data,
                    url: $(this).attr("action")
                }).done(function(data) {
                    hideLoading(target, function() {
                        try {
                            json = $.parseJSON(data);
                            if(json.action == 'redirect') {
                                redirect(json.target);
                            }
                        } catch (e) {
                            target.addClass("fadeIn");
                            target.html(data);
                            $(document).trigger("ajaxReload");
                            setTimeout(function() {
                                target.addClass("now");
                            }, 30);
                        }
                    });
                });
            });
        });
    }
};

$(document).ready(forms.init);
$(document).on("ajaxReload", forms.init);
