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
	
		_.each(cm.model.types, function(modelType, modelName) {
			_.each(cm.action.verbs, function(actionVerb, actionName) {
				handler.bindAction(actionVerb, modelType, null, function(action, model, data) {
					var msg = "ACTION: <[ACTOR:" + (action.actor ? action.actor.id : null) + "] , " + actionName + " , " + "[" + modelName + ":" + JSON.stringify(model._id) + "]>";
					msg += " (" + JSON.stringify(data) + ")";
					handler.alert(msg);
				});
			});
		});
	},
	
	alert: function(msg) {
		var $msg = $("<li>" + msg + "</li>");
		this.$(".alerts").append($msg);
		$msg.delay(8000).slideUp(200, function() {
			$(this).remove();
		});
	}
});