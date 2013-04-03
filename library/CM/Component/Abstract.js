/**
 * @class CM_Component_Abstract
 * @extends CM_View_Abstract
 */
var CM_Component_Abstract = CM_View_Abstract.extend({
	_class: 'CM_Component_Abstract',

	_ready: function() {
		cm.dom.setup(this.$());

		CM_View_Abstract.prototype._ready.call(this);
	},

	/**
	 * Called on popOut()
	 */
	repaint: function() {
	},

	bindRepaintOnWindowResize: function() {
		var self = this;
		var callback = function() {
			self.repaint();
		};
		$(window).on('resize', callback);
		this.on('destruct', function() {
			$(window).off('resize', callback);
		});
	},

	/**
	 * @return jQuery
	 */
	$: function(selector) {
		if (!selector) {
			return this.$el;
		}
		selector = selector.replace('#', '#' + this.getAutoId() + '-');
		return $(selector, this.el);
	},

	popOut: function(options) {
		this.repaint();
		this.$el.floatOut(options);
		this.repaint();

		var self = this;
		this.$el.one('floatbox-close', function() {
			if (app.window.isHidden(self.el)) {
				self.remove();
			}
			return false;
		});
	},

	popIn: function() {
		this.$el.floatIn();
	},

	/**
	 * @param {String} message
	 */
	error: function(message) {
		cm.window.hint(message);
	},

	/**
	 * @param {String} message
	 */
	message: function(message) {
		cm.window.hint(message);
	},

	/**
	 * @return jqXHR
	 */
	reload: function(params) {
		return this.ajaxModal('reload', params);
	},

	/**
	 * @param {String} className
	 * @param {Object|Null} [params]
	 * @param {Object|Null} [options]
	 * @return jqXHR
	 */
	replaceWithComponent: function(className, params, options) {
		params = params || {};
		options = options || {};
		params.className = className;
		return this.ajax('replaceWithComponent', params, options);
	}
});
