(function(){
  "use strict";

  var $elem = jQuery('#my-send-email-wrap');
  var $iframe  = $elem.find('iframe');
  var $message = $elem.find('.my-send-email-message');

  var updatePreviewContent = function(content) {
    var src = $iframe.attr('src');
    src = src.replace(/\?message=.*/, '');
    src = src + '?message=' + encodeURIComponent(content);
    $iframe.attr('src', src);
  };

  var updatePreviewHeight = function() {
    $iframe.height($iframe.get(0).contentWindow.document.body.scrollHeight);
  };

  $iframe.on('load', function() {
    updatePreviewHeight();
  });

  $elem.on('keyup', ':input[name="message"]', function() {
    updatePreviewContent(jQuery(this).val());
  });

  $elem.on('submit', 'form', function(event){
    event.preventDefault();
    $message.slideUp();
    jQuery.post(ajaxurl, $elem.find('form').serialize(), function(response){
      $message.html(response).slideDown();
    });
  });

  updatePreviewHeight();

})();

