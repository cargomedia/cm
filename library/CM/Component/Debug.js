/**
 * @class CM_Component_Debug
 * @extends CM_Component_Abstract
 */
var CM_Component_Debug = CM_Component_Abstract.extend({
	_class: 'CM_Component_Debug',

	ready: function() {
		var handler = this;
		this.$('.buttons > a').each(function() {
			$(this).click(function() {
				var name = $(this).attr('class');
				handler.$('.containers div:not(.' + name + ')').hide();
				handler.$('.containers div.' + name).toggle();
			});
		});
		this.$('.clearCache').click(function() {
			handler.ajax('clearCache', {
				'CM_Cache': handler.$('#CM_Cache').is(':checked'),
				'CM_CacheLocal': handler.$('#CM_CacheLocal').is(':checked')
			}, {
				success: function() {
					location.reload();
				}
			});
		});

		if (cm.stream) {
			var _subscribe = cm.stream._subscribe;
			var _unsubscribe = cm.stream._unsubscribe;
			cm.stream._subscribe = function() {
				var params = ['SUBSCRIBE:'];
				params.push.apply(params, arguments);
				handler.log.apply(handler, params);
				_subscribe.apply(this, arguments);
			};
			cm.stream._unsubscribe = function() {
				var params = ['UNSUBSCRIBE:'];
				params.push.apply(params, arguments);
				handler.log.apply(handler, params);
				_unsubscribe.apply(this, arguments);
			};
		}

		if (cm.options.stream.channel) {
			_.each(cm.model.types, function(modelType, modelName) {
				_.each(cm.action.verbs, function(actionVerb, actionName) {
					handler.bindAction(actionVerb, modelType, cm.options.stream.channel.key, cm.options.stream.channel.type, function(action, model, data) {
						var messages = [];
						messages.push("ACTION: <[ACTOR:" + (action.actor ? action.actor.id : null) + "] , " + actionName + " , " + "[" + modelName + ":" + JSON.stringify(model._id) + "]>");
						messages.push("(");
						messages.push(data);
						messages.push(")");
						handler.log.apply(handler, messages);
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
	}
});
