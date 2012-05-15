_ready: function() {
	this.initDom(this.$());

	this.ready();
	_.each(this.getChildren(), function(child) {
		child._ready();
	});
},

/**
 * Called on popOut()
 */
repaint: function() {
},

/**
 * @return jQuery
 */
$: function(selector) {
	if (!selector) {
		return this.$el;
	}
	selector = selector.replace('#', '#'+this.getAutoId()+'-');
	return $(selector, this.el);
},

popOut: function(options) {
	this.$().floatOut(options);
	this.repaint();
},

popIn: function() {
	this.$().floatIn();
},

/**
 * @param string message
 */
error: function(message) {
	cm.window.hint(message);
},

/**
 * @param string message
 */
message: function(message) {
	cm.window.hint(message);
},

/**
 * @return XMLHttpRequest
 */
reload: function(params) {
	return this.ajaxModal('reload', params);
},

/**
 * @param string key
 * @param mixed key
 */
storageSet: function(key, value) {
	cm.storage.set(this.getClass() + '_' + key, value);
},

/**
 * @param string key
 * @return mixed
 */
storageGet: function(key) {
	return cm.storage.get(this.getClass() + '_' + key);
},

/**
 * @param string key
 */
storageDelete: function(key) {
	cm.storage.del(this.getClass() + '_' + key);
}