var ajaxlinks = {

    init : function() {
      ajaxlinks.updateContainer();
      ajaxlinks.ajaxLinks();
    },

    updateContainer : function() {
      $(".update-container").each(function() {
        if($(this).hasClass("done"))
          return;
        var container = $(this);
        container.css({
          "maxHeight": $(window).height()/2,
          "overflow": "auto"
        });
        var xhr = new XMLHttpRequest();
        xhr.open('GET', $(this).data('url') + "?forceAjax=true&buffer=false", true);
        xhr.send(null);
        var timer;
        timer = window.setInterval(function() {
          container.html('data: ' + xhr.responseText + '<br />');
          if (xhr.readyState == XMLHttpRequest.DONE) {
            container.addClass("done");
            window.clearTimeout(timer);
            redirect(container.data('finished-target'));
          }
        }, 500);
      });
    },

    ajaxLinks : function() {
      $("a.ajax").each(function() {
          $(this).unbind("click").on("click", function(e) {
              e.preventDefault();
              setLoading($(this), "revert");
              if($(this).hasClass("confirm")) {
                  // load sidebar with confirm dialog
                  $(this).data("open", $(this).attr('href'));
                  var the_overlay = overlay.prepare();
                  overlay.open($(this), the_overlay);
              } else {
                  $.ajax({
                      method: 'POST',
                      url: $(this).attr("href")
                  }).done(function(data) {
                      try {
                          json = $.parseJSON(data);
                          if(json.action == 'redirect') {
                              redirect(json.target);
                          }
                          if(json.action == 'refresh' || json.action == "update") {
                              if($('#'+json.target).length > 0) {
                                  if(json.action == "update") {
                                      $('#'+json.target).html(json.content);
                                  } else {
                                      $('#'+json.target).replaceWith(json.content);
                                  }
                              }
                              $(document).trigger("ajaxReload");
                          }
                      } catch (e) {
                          console.error("requires json action", e);
                      }
                  });
              }
          });
      });
    }

};

$(document).ready(ajaxlinks.init);
$(document).on("ajaxReload", ajaxlinks.init);
