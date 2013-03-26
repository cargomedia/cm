/*
 * Author: CM
 */
(function($) {

	/**
	 * @param {Object} paramList
	 * @return {String}
	 */
	function encodeUrlQuery(paramList) {
		var parts = [];
		for (var param in paramList) {
			parts.push(encodeURIComponent(param) + '=' + encodeURIComponent(paramList[param]));
		}
		return parts.join('&');
	}

	$.fn.openx = function() {
		return this.each(function() {
			var zoneId = $(this).data('zone-id');
			var host = $(this).data('host');
			var variables = $(this).data('variables');

			var m3_u = (location.protocol=='https:'?'https://' + host + '/delivery/ajs-proxy.php':'http://' + host + '/delivery/ajs-proxy.php');
			var m3_r = Math.floor(Math.random()*99999999999);
			if (!document.MAX_used) document.MAX_used = ',';
			var src = '';
			src += m3_u;
			src += "?zoneid=" + zoneId;
			src += '&cb=' + m3_r;
			if (document.MAX_used != ',') {
				src += "&exclude=" + document.MAX_used;
			}
			src += document.charset ? '&charset='+document.charset : (document.characterSet ? '&charset='+document.characterSet : '');
			src += "&loc=" + escape(window.location);
			if (document.referrer) {
				src += "&referer=" + escape(document.referrer);
			}
			if (document.context) {
				src += "&context=" + escape(document.context);
			}
			if (document.mmm_fo) {
				src += "&mmm_fo=1";
			}

			var variablesQuery = encodeUrlQuery(variables)
			if ('' !== variablesQuery) {
				src += '&' + variablesQuery;
			}

			var $element = $(this);
			$.getJSON(src + '&callback=?', function(html) {
				$element.html(html);
			});
		});
	};
})(jQuery);
