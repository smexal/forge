var helpers = {
    init : function() {
      $('.tipster, .tooltip').tooltipster({
        debug : false,
        delay: 10,
        contentAsHTML : true
      });

      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            // since reveal elements don't get triggered on a tab change.
            // make sure the scroll event is triggered on change of to reveal.
            $(window).scroll();
      });

      $(document).find("body").addClass('js-active');

      $('.tipster-ajax').tooltipster({
        debug: false,
        contentAsHTML : true,
        content: '...',
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data('loaded') !== true) {
                $.get($origin.attr("tip-url"), function(data) {
                    instance.content(data.content);
                    $origin.data('loaded', true);
                });
            }
        }
      });

      helpers.subnavigationPosition();
      helpers.searchField();
    },

    searchField : function() {
        var timeout = false;
        $("input[name='search_field']").on('keyup', function() {
            clearTimeout(timeout);
            var input = $(this);
            timeout = setTimeout(function() {
                $.ajax({
                    method: "GET",
                    url: input.attr('data-base'),
                    data: { q: input.val() }
                }).done(function(result) {
                    input.closest("form").next().replaceWith($(result).find(".results"));
                });
            }, 400);
        });
    },

    setCookie : function(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },

    getCookie : function(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    },

    subnavigationPosition : function() {
        $(".navbar.navbar-default ul li a").on("click", function() {
            console.log($(this).offset().top+10)
            $(this).next().css({
                paddingTop: $(this).offset().top+10
            });
        });
    }
};

function gCaptchaSubmit(token) {
    $("button.g-recaptcha").closest('form').submit();
}

$(document).ready(helpers.init);
$(document).on("ajaxReload", helpers.init);


/**
 * raw helper functions
 */
function setLoading(element, revert) {
    element.addClass("loading");
    var loading = $('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
    if(revert === "revert") {
        element.attr("data-original-content", encodeURIComponent(element.html()));
    }
    element.html(loading);
}

function hideLoading(element, callback) {
    $("body").find(".spinner").each(function() {
        var spinner = $(this);
        spinner.addClass("fadeOut");
        var attr = spinner.parent().attr('data-original-content');
        if(typeof attr !== typeof undefined
            && attr !== false
            && attr !== 'undefined') {
            var content = decodeURIComponent(spinner.parent().attr('data-original-content'));
            spinner.parent().attr('data-original-content', 'undefined');
            spinner.parent().html(content);
        }
    });
    setTimeout(callback, 250);
}

function updateProgressBar(updater) {
    $("#"+updater.data('id')).width(updater.data('value')+"%");
    updater.remove();
}

function redirect(target, special) {
    var container = $(".ajax-reload-container");
    if(special=="in_overlay") {
        setLoading($(".overlay-container").find(".content"));
    } else {
        setLoading(container);
    }
    $.ajax({
        method: 'POST',
        url: target
    }).done(function(data) {
        hideLoading(container, function() {
            if(special=="in_overlay") {
                if(typeof(overlay) !== 'undefined') {
                    overlay.setContent($(data).find(".ajax-reload-container"), $(".overlay-container"));
                }
                $(document).trigger("ajaxReload");
            } else {
                if(typeof(overlay) !== 'undefined') {
                    overlay.hide();
                }
                container.html($(data).find(".ajax-reload-container").html());
                container.append($(data).find(".message-container"));
                $(document).trigger("ajaxReload");
                container.removeClass('loading');
            }
        });
    });
}

var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substringRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        matches.push(str);
      }
    });

    cb(matches);
  };
};
