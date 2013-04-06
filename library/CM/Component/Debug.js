/**
 * @class CM_Component_Debug
 * @extends CM_Component_Abstract
 */
var CM_Component_Debug = CM_Component_Abstract.extend({
	_class: 'CM_Component_Debug',

	/** @type Boolean */
	active: false,

	events: {
		'click .toggleDebugBar': 'toggleDebugBar',
		'click .clearCache': 'clearCache',
		'click .panel': function(e) {
			this.toggleWindow($(e.currentTarget).data('name'));
		}
	},

	ready: function() {
		var self = this;

		$(window).bind('keydown.debugBar', function(event) {
			if (event.which === 68) { // d Key
				var tagName = event.srcElement.tagName.toLowerCase();
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
			_.each(cm.model.types, function(modelType, modelName) {
				_.each(cm.action.verbs, function(actionVerb, actionName) {
					self.bindAction(actionVerb, modelType, cm.options.stream.channel.key, cm.options.stream.channel.type, function(action, model, data) {
						var messages = [];
						messages.push("ACTION: <[ACTOR:" + (action.actor ? action.actor.id : null) + "] , " + actionName + " , " + "[" + modelName + ":" + JSON.stringify(model._id) + "]>");
						messages.push("(");
						messages.push(data);
						messages.push(")");
						self.log.apply(self, messages);
					});
				});
			});
		}
	},

	/**
	 * @param message1
	 * @param [message2]
	 * @param [message3]
	 */
	log: function(message1, message2, message3) {
		var messages = Array.prototype.slice.call(arguments);
		if (console && console.log) {
			console.log.apply(console, messages);
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

	toggleWindow: function(name) {
		this.$('.window:not(.' + name + ')').hide();
		this.$('.window.' + name).toggle();
	},

	clearCache: function() {
		this.ajax('clearCache', {
			'CM_Cache': this.$('.CM_Cache').is(':checked'),
			'CM_CacheLocal': this.$('.CM_CacheLocal').is(':checked')
		}, {
			success: function() {
				location.reload();
			}
		});
	}
});
