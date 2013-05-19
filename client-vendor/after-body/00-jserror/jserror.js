/**
 * @author cargomedia.ch
 */
(function() {
	if (jserror) {
		return;
	}
	var jserror = {
		/** @type Function|Null */
		onerrorBackup: null,

		/** @type String */
		logUrl: null,

		/** @type Number */
		index: 0,

		/**
		 * @param {String} logUrl
		 * @param {Boolean} [suppressFromBrowser]
		 */
		install: function(logUrl, suppressFromBrowser) {
			this.logUrl = logUrl;
			if (window.onerror) {
				this.onerrorBackup = window.onerror;
			}
			window.onerror = function(message, fileUrl, fileLine) {
				jserror.log(message, fileUrl, fileLine);
				if (jserror.onerrorBackup) {
					jserror.onerrorBackup(message, fileUrl, fileLine);
				}
				if (suppressFromBrowser) {
					return true;
				}
			}
		},

		/**
		 * @param {String} message
		 * @param {String} fileUrl
		 * @param {Number} fileLine
		 */
		log: function(message, fileUrl, fileLine) {
			var src = this.logUrl;
			src += '?index=' + (this.index++);
			src += "&url=" + encodeURIComponent(document.location.href);
			src += "&message=" + encodeURIComponent(message.trim().substr(0, 10000));
			src += "&fileUrl=" + encodeURIComponent(fileUrl);
			src += "&fileLine=" + fileLine;
			this._appendScript(src);
		},

		/**
		 * @param {String} src
		 */
		_appendScript: function(src) {
			var script = document.createElement('script');
			script.src = src;
			script.type = 'text/javascript';
			document.getElementsByTagName('head')[0].appendChild(script);
		}
	};

	jserror.install('/jserror/null');

}).call(this);
