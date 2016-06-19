var forms = {
    init : function() {
        forms.ajax();
        forms.tags();
        forms.helperlinks();
    },

    tags : function() {
      $("input.tags").each(function() {
        var values = $(this).data('values');
        var getter = $(this).data('getter');
        if( Object.prototype.toString.call( values ) === '[object Array]') {
            forms.tagsInputByValues($(this), values);
        } else if (typeof getter === "string") {
            forms.tagsInputByGetter($(this));
        }
      });
    },

    tagsInputByGetter : function(element) {
        var engine = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.whitespace,
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          remote: {
            url: element.data('getter') + "/search/%QUERY",
            wildcard : '%QUERY'
          }
        });
        engine.initialize();

        element.tagsinput({
            allowDuplicates: false,
            freeInput: false,
            itemValue: element.data('getter-value'),
            itemText: element.data('getter-name'),
            inputSize: 10,
            maxTags : element.data('data-single'),
            typeaheadjs: {
              hint: true,
              highlight: true,
              minLength: 2,
              name: "name_"+element.data('getter-name'),
              displayKey: element.data('getter-name'),
              source: engine.ttAdapter()
            }
        });
      },

    tagsInputByValues : function(element, values) {
      console.log(values);
      element.tagsinput({
          allowDuplicates: false,
          freeInput: false,
          typeaheadjs: {
              source: substringMatcher(values)
          }
      });
    },

    helperlinks : function() {
        $("a.set-value").unbind("click").on('click', function() {
            $("input#" + $(this).data('target')).val($(this).data('value'));
            $(this).parent().find(".active").removeClass('active');
            $(this).addClass('active');
        });
    },

    ajax : function() {
        $("form.ajax").each(function() {
            $(this).unbind('submit').on("submit", function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var button = $(this).find("button");
                // don't go any further if the button is still loading...
                if(button.hasClass('loading')) {
                  return;
                }
                button.addClass('loading');
                e.preventDefault();
                var target = $(this).closest($(this).data('target'));
                setLoading(target);
                var form_data = $(this).serialize();
                $.ajax({
                    method: 'POST',
                    data: form_data,
                    url: $(this).attr("action"),
                    error: function (jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }
                        console.error(msg);
                    },
                }).done(function(data) {
                  // if the form has a callback defined, run it with the response
                  var callback = button.closest('form').attr('callback');
                  if(typeof(callback) !== 'undefined') {
                    eval(callback+'('+JSON.stringify(data)+')');
                  }
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
                }).complete(function() {
                    button.removeClass('loading');
                });
            });
        });
    }
};

$(document).ready(forms.init);
$(document).on("ajaxReload", forms.init);
