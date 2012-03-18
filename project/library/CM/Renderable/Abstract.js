_children: [],

initialize: function() {
	this._children = [];

	if (this.getParent()) {
		this.getParent().registerChild(this);
	}

	if (this.actions) {
		this._bindActions(this.actions);
	}
	if (this.streams) {
		this._bindStreams(this.streams);
	}
},

ready: function() {
},

_ready: function() {
	this.ready();
	_.each(this.getChildren(), function(child) {
		child._ready();
	});
},

/**
 * @param CM_Renderable_Abstract child
 */
registerChild: function(child) {
	this._children.push(child);
},

/**
 * @return CM_Renderable_Abstract[]
 */
getChildren: function() {
	return this._children;
},

/**
 * @param string className
 * @return CM_Renderable_Abstract|null
 */
findChild: function(className){
	return _.find(this.getChildren(), function(child) {
		return child._class == className;
	}) || null;
},

/**
 * @return CM_Renderable_Abstract|null
 */
getParent: function() {
	if (this.options.parent) {
		return this.options.parent;
	}
	return null;
},

/**
 * @return string
 */
getAutoId: function() {
	return this.el.id;
},

/**
 * @param bool skipDomRemoval OPTIONAL
 */
remove: function(skipDomRemoval) {
	this.trigger("destruct");

	if (this.getParent()) {
		var siblings = this.getParent().getChildren();
		for (var i = 0, sibling; sibling = siblings[i]; i++) {
			if (sibling.getAutoId() == this.getAutoId()) {
				siblings.splice(i, 1);
			}
		}
	}

	_.each(this.getChildren(), function(child) {
		child.remove();
	});

	delete cm.views[this.getAutoId()];

	if (!skipDomRemoval) {
		this.$().remove();
	}
},

/**
 * @param int actionTypes
 * @param int modelType
 * @param function callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
 */
bindAction: function(actionType, modelType, callback) {
	cm.action.bind(actionType, modelType, callback, this);
	this.on('destruct', function() {
		cm.action.unbind(actionType, modelType, callback, this);
	});
},

/**
 * @param string event
 * @param function callback fn(array data)
 */
bindStream: function(event, callback) {
	var namespace = this._class + ':' + event;
	cm.stream.bind(namespace, callback, this);
	this.on('destruct', function() {
		cm.stream.unbind(namespace, callback, this);
	});
},

/**
 * @param object
 */
_bindActions: function(actions) {
	for (key in actions) {
		var callback = actions[key];
		var match = key.match(/^(\S+)\s+(.+)$/);
		var modelType = cm.model.types[match[1]];
		var actionNames = match[2].split(/\s*,\s*/);
		_.each(actionNames, function(actionName) {
			var actionType = cm.action.types[actionName];
			this.bindAction(actionType, modelType, callback);
		}, this);
	}
},

/**
 * @param object
 */
_bindStreams: function(streams) {
	for (key in streams) {
		var callback = streams[key];
		this.bindStream(key, callback);
	}
}
