/*
 * Author: CM
 */
(function($, global) {

  /**
   * @param {String} category
   * @param {String} action
   * @param {String} label
   */
  function trackEvent(category, action, label) {
    if (global.ga) {
      global.ga('send', {
        'hitType': 'event',
        'eventCategory': category,
        'eventAction': action,
        'eventLabel': label,
      });
    }
  }

  $.fn.epom = function() {
    return this.each(function() {
      var zoneId = $(this).data('zone-id');
      var variables = $(this).data('variables');
      var src = (location.protocol == 'https:' ? 'https:' : 'http:') + '//n181adserv.com\/ads-api';
      var $element = $(this);

      var loadCallback = function(result) {
        if (result.success) {
          $element.html(result.code);
          var hasContent = !$element.is(':empty');
          $element.trigger('epom-loaded', {hasContent: hasContent});

          if (hasContent) {
            $element.addClass('advertisement-hasContent');
            trackEvent('Banner', 'Impression', 'zone-' + zoneId);
            var $link = $element.find('a[href]');
            if ($element.is(':visible') && $link.length > 0) {
              trackEvent('Banner', 'Impression-Clickable', 'zone-' + zoneId);
              $link.on('click', function() {
                trackEvent('Banner', 'Click', 'zone-' + zoneId);
              });
            }
          }
        }
      };

      variables['format'] = 'jsonp';
      $.getJSON(src + '?callback=?', variables, loadCallback);
    });
  };
})(jQuery, window);
