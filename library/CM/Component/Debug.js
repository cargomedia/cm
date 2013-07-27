/**
 * @class CM_Component_Debug
 * @extends CM_Component_Abstract
 */
var CM_Component_Debug = CM_Component_Abstract.extend({
	_class: 'CM_Component_Debug',

	/** @type Boolean */
	active: false,

	/** @type Object */
	cacheNames: null,

	events: {
		'click .toggleDebugBar': 'toggleDebugBar',
		'click .clearCache': 'clearCache',
		'click .toggleWindow': function(e) {
			this.toggleWindow($(e.currentTarget).data('name'));
		}
	},

	ready: function() {
		var self = this;

		$(window).bind('keydown.debugBar', function(event) {
			if (event.which === 68) { // d Key
				var tagName = event.target.tagName.toLowerCase();
				if (tagName === 'input' || tagName === 'textarea') {
					return;
				}
				self.toggleDebugBar();
			}
		});

		this.on('destruct', function() {
			$(window).unbind('keydown.debugBar');
		});

		if (cm.options.stream.channel) {
			_.each(cm.action.types, function(actionType, actionTypeName) {
				_.each(cm.action.verbs, function(actionVerb, actionVerbName) {
					self.bindAction(actionVerb, actionType, cm.options.stream.channel.key, cm.options.stream.channel.type, function(action, model, data) {
						cm.debug.log(actionTypeName + ': <[ACTOR:' + (action.actor ? action.actor.id : null) + '] , ' + actionVerbName + ' , ' + '[' + model._class + ':' + JSON.stringify(model._id) + ']>', '(', data, ')');
					});
				});
			});
		}
	},

	toggleDebugBar: function() {
		var debugBar = this.$('.debugBar');

		if (this.active) {
			debugBar.stop().transition({x: '-100%'}, '400ms', 'snap');
			this.active = false;
		} else {
			debugBar.stop().transition({x: 0}, '400ms', 'snap');
			this.active = true;
		}
	},

	/**
	 * @param {String} name
	 */
	toggleWindow: function(name) {
		this.$('.panel:not([data-name="' + name + '"])').removeClass('active');
		this.$('.panel[data-name="' + name + '"]').toggleClass('active');
		this.$('.window:not(.' + name + ')').hide();
		this.$('.window.' + name).toggle();
	},

	clearCache: function() {
		var clearCacheArguments = {};
		_.each(this.cacheNames, function(cacheName) {
			clearCacheArguments[cacheName] = this.$('.' + cacheName).is(':checked');
		});
		this.ajax('clearCache', clearCacheArguments, {
			success: function() {
				location.reload();
			}
		});
	}
});
