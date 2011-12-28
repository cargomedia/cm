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
		handler.ajaxCall('ajax_clearCache', {
			'CM_Cache': handler.$('#CM_Cache').is(':checked'),
			'CM_CacheLocal': handler.$('#CM_CacheLocal').is(':checked')
		}, {
			success: function() {
				location.reload();
			}
		});
	});

	_.each(cm.entity.types, function(entityType, entityName) {
		_.each(cm.action.types, function(actionType, actionName) {
			handler.bindAction(actionType, entityType, function(action, entity, data) {
				var msg = "ACTION: <[USER:" + action.actor.id + "] , " + actionName + " , " + "[" + entityName + ":" + entity.id + "]>";
				msg += " (" + JSON.stringify(data) + ")";
				handler.alert(msg);
			});
		});
	});
}
,

alert: function(msg) {
	var $msg = $("<li>" + msg + "</li>");
	this.$(".alerts").append($msg);
	$msg.delay(8000).slideUp(200, function() {
		$(this).remove();
	});
}
