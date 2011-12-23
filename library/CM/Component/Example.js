uname: null,
	profile: null,

	events: {
	"click .reload": "reloadChinese",
		"click .popout": "popOut",
		"click .popin": "popIn",
		"click .load": "loadSelector",
		"click .load2": "loadList",
		"click .call": "callAjax",
		"click .rpc": "callRpc",
		"click .error_500_text_callback": "error_500_text_callback",
		"click .error_599_text": "error_599_text",
		"click .error_CM_Exception_public_callback": "error_CM_Exception_public_callback",
		"click .error_CM_Exception_public": "error_CM_Exception_public",
		"click .error_CM_Exception": "error_CM_Exception",
		"click .error_CM_Exception_AuthRequired_public_callback": "error_CM_Exception_AuthRequired_public_callback",
		"click .error_CM_Exception_AuthRequired_public": "error_CM_Exception_AuthRequired_public",
		"click .error_CM_Exception_AuthRequired": "error_CM_Exception_AuthRequired"
},

ready: function() {
	this.message("Component ready, uname: " + this.uname);
},

reloadChinese: function() {
	this.reload({foo:'some chinese.. 百度一下，你就知道 繁體字!'});
},

loadSelector: function() {
	this.load('FB_Component_Selector_User_Friends', {}, {
		success: function() {
			this.bind("select", function(friend) {
				this.message("Selected: " + friend.username);
				this.popIn();
			});
			this.popOut();
			this._ready();
		}
	});
},

loadList: function() {
	this.load('FB_Component_UserList_Friends', {template:'mini'});
},

callAjax: function() {
	this.ajaxCall('ajax_test', {x:'myX'}, {
		success: function(data) {
			this.message('ajax_test(): ' + data);
		}
	});
},

callRpc: function() {
	cm.rpc('FB_Component_Example.time', [], {
		success: function(timestamp) {
			cm.window.hint.message("Time: "+timestamp);
		}
	});
},

error_500_text_callback: function() {
	this.ajaxCall('ajax_error', {status:500, text:'Errortext'}, {
		error: function(msg, type) {
			this.error('Error callback, type:'+type + ', msg:'+msg);
			return false;
		}
	});
},
error_599_text: function() {
	this.ajaxCall('ajax_error', {status:599, text:'Errortext'});
},
error_CM_Exception_public_callback: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception', text:'Errortext', 'public':true}, {
		error: function(msg, type) {
			this.error('Error callback, type:'+type + ', msg:'+msg);
			return false;
		}
	});
},
error_CM_Exception_public: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception', text:'Errortext', 'public':true});
},
error_CM_Exception: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception', text:'Errortext'});
},
error_CM_Exception_AuthRequired_public_callback: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception_AuthRequired', text:'Errortext', 'public':true}, {
		error: function(msg, type) {
			this.error('Error callback, type:'+type + ', msg:'+msg);
			return false;
		}
	});
},
error_CM_Exception_AuthRequired_public: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception_AuthRequired', text:'Errortext', 'public':true});
},
error_CM_Exception_AuthRequired: function() {
	this.ajaxCall('ajax_error', {exception:'CM_Exception_AuthRequired', text:'Errortext'});
}
