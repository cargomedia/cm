/**
 * @class CM_Component_Example
 * @extends CM_Component_Abstract
 */
var CM_Component_Example = CM_Component_Abstract.extend({
	_class: 'CM_Component_Example',

	uname: null,
	profile: null,
	pingCount: 0,

	events: {
		'click .reloadComponent': 'reloadChinese',
		'click .popoutComponent': 'popOut',
		'click .popinComponent': 'popIn',
		'click .loadComponent': 'loadExample',
		'click .loadComponent_callback': 'loadExampleInline',
		'click .removeComponent': 'myRemove',
		'click .callRpcTime': 'callRpc',
		'click .callAjaxTest': 'callAjax',
		'click .throwError_500_text_callback': 'error_500_text_callback',
		'click .throwError_599_text': 'error_599_text',
		'click .throwError_CM_Exception_public_callback': 'error_CM_Exception_public_callback',
		'click .throwError_CM_Exception_public': 'error_CM_Exception_public',
		'click .throwError_CM_Exception': 'error_CM_Exception',
		'click .throwError_CM_Exception_AuthRequired_public_callback': 'error_CM_Exception_AuthRequired_public_callback',
		'click .throwError_CM_Exception_AuthRequired_public': 'error_CM_Exception_AuthRequired_public',
		'click .throwError_CM_Exception_AuthRequired': 'error_CM_Exception_AuthRequired',
		'click .callAjaxPing': 'ping',
		'click .toggleWindow': function(e) {
			var $opener = $(e.currentTarget);
			this.toggleWindow($opener);
		}
	},

	streams: {
		'ping': function(response) {
			this.$('.stream .output').append(response.number + ': ' + response.message + '<br />').scrollBottom();
		}
	},

	ready: function() {
		this.message("Component ready, uname: " + this.uname);
	},

	reloadChinese: function() {
		this.reload({foo: 'some chinese.. 百度一下，你就知道 繁體字!'});
	},

	myRemove: function() {
		this.remove();
	},

	loadExample: function() {
		this.loadComponent('CM_Component_Example', {foo: 'value2', site: this.getParams().site});
	},

	loadExampleInline: function() {
		var handler = this;
		this.getParent().loadComponent('CM_Component_Example', {foo: 'value3', site: this.getParams().site}, {
			'success': function() {
				this.$el.hide().insertBefore(handler.$el).slideDown(600);
			}
		});
	},

	callAjax: function() {
		this.ajax('test', {x: 'myX'}, {
			success: function(data) {
				this.message('ajax_test(): ' + data);
			}
		});
	},

	callRpc: function() {
		cm.rpc('CM_Component_Example.time', [], {
			success: function(timestamp) {
				cm.window.hint("Time: " + timestamp);
			}
		});
	},

	error_500_text_callback: function() {
		this.ajax('error', {status: 500, text: 'Errortext'}, {
			error: function(msg, type) {
				this.error('callback( type:' + type + ', msg:' + msg + ' )');
				return false;
			}
		});
	},
	error_599_text: function() {
		this.ajax('error', {status: 599, text: 'Errortext'});
	},
	error_CM_Exception_public_callback: function() {
		this.ajax('error', {exception: 'CM_Exception', text: 'Errortext', 'public': true}, {
			error: function(msg, type) {
				this.error('callback( type:' + type + ', msg:' + msg + ' )');
				return false;
			}
		});
	},
	error_CM_Exception_public: function() {
		this.ajax('error', {exception: 'CM_Exception', text: 'Errortext', 'public': true});
	},
	error_CM_Exception: function() {
		this.ajax('error', {exception: 'CM_Exception', text: 'Errortext'});
	},
	error_CM_Exception_AuthRequired_public_callback: function() {
		this.ajax('error', {exception: 'CM_Exception_AuthRequired', text: 'Errortext', 'public': true}, {
			error: function(msg, type) {
				this.error('callback( type:' + type + ', msg:' + msg + ' )');
				return false;
			}
		});
	},
	error_CM_Exception_AuthRequired_public: function() {
		this.ajax('error', {exception: 'CM_Exception_AuthRequired', text: 'Errortext', 'public': true});
	},
	error_CM_Exception_AuthRequired: function() {
		this.ajax('error', {exception: 'CM_Exception_AuthRequired', text: 'Errortext'});
	},

	ping: function() {
		this.ajax('ping', {number: this.pingCount});
		this.pingCount++;
	},

	toggleWindow: function($opener) {
		var $button = $opener.children('.panel');
		var $window = $opener.children('.window');
		$window.toggleModal(function() {
			$window.toggle();
			$button.toggleClass('active');
		});
	}
});
