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
                overlay.setContent($(data).find(".ajax-reload-container"), $(".overlay-container"));
            } else {
                overlay.hide();
                container.html($(data).find(".ajax-reload-container").html());
                container.append($(data).find(".message-container"));
                $(document).trigger("ajaxReload");
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