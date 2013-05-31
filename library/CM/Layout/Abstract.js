/**
 * @class CM_Layout_Abstract
 * @extends CM_View_Abstract
 */
var CM_Layout_Abstract = CM_View_Abstract.extend({

	/** @type String */
	_class: 'CM_Layout_Abstract',

	/** @type jQuery|Null */
	_$pagePlaceholder: null,

	/** @type jqXHR|Null */
	_pageRequest: null,

	/**
	 * @param {String} path
	 */
	loadPage: function(path) {
		this.trigger('navigate', path);

		if (!this._$pagePlaceholder) {
			this._$pagePlaceholder = $('<div class="router-placeholder" />');
			var page = this.findChild('CM_Page_Abstract');
			page.$el.replaceWith(this._$pagePlaceholder);
			page.remove(true);
			this._onPageTeardown();
		} else {
			this._$pagePlaceholder.removeClass('error').html('');
		}
		var timeoutLoading = this.setTimeout(function() {
			this._$pagePlaceholder.html('<div class="spinner" />')
		}, 750);

		if (this._pageRequest) {
			this._pageRequest.abort();
		}
		this._pageRequest = this.ajaxModal('loadPage', {path: path}, {
			success: function(response) {
				if (response.redirectExternal) {
					cm.router.route(response.redirectExternal);
					return;
				}
				var layout = this;
				this._injectView(response, function(response) {
					var fragment = response.url.substr(cm.getUrl().length);
					var reload = (layout.getClass() != response.layoutClass);
					if (reload) {
						window.location.replace(response.url);
						return;
					}
					layout._$pagePlaceholder.replaceWith(this.$el);
					layout._$pagePlaceholder = null;
					window.history.replaceState(null, null, fragment);
					layout._onPageSetup(this, response.title, response.url, response.menuEntryHashList);
				});
			},
			error: function(msg, type, isPublic) {
				this._$pagePlaceholder.addClass('error').html('<pre>' + msg + '</pre>');
				this._onPageError();
				return false;
			},
			complete: function() {
				window.clearTimeout(timeoutLoading);
			}
		});
	},

	_onPageTeardown: function() {
		$(document).scrollTop(0);
		$('.floatbox-layer').floatIn();
	},

	/**
	 * @param {CM_Page_Abstract} page
	 * @param {String} title
	 * @param {String} url
	 * @param {String[]} menuEntryHashList
	 */
	_onPageSetup: function(page, title, url, menuEntryHashList) {
		document.title = title;
		$('[data-menu-entry-hash]').removeClass('active');
		var menuEntrySelectors = _.map(menuEntryHashList, function(menuEntryHash) {
			return '[data-menu-entry-hash=' + menuEntryHash + ']';
		});
		$(menuEntrySelectors.join(',')).addClass('active');
	},

	_onPageError: function() {
		$('[data-menu-entry-hash]').removeClass('active');
	}
});
