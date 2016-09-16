/**
 * @author cargomedia.ch
 */
(function() {
  if (jserror) {
    return;
  }

  var jserror = {

    /** @type {String} */
    _uid: null,

    /** @type {Function|Null} */
    _onerrorBackup: null,

    /** @type {String} */
    _url: null,

    /** @type {Number} */
    _counter: null,

    /**
     * @param {String} url
     * @param {Number} [counterMax]
     * @param {Boolean} [suppressErrors]
     * @param {Boolean} [suppressWithoutDetails]
     */
    install: function(url, counterMax, suppressErrors, suppressWithoutDetails) {
      this._url = url;
      this._counter = 0;
      this._uid = (Math.random() + 1).toString(36).substring(7);

      if ('function' == typeof window.onerror) {
        this._onerrorBackup = window.onerror;
      }

      /**
       * @param {String} message
       * @param {String} sourceUrl
       * @param {Number} line
       *@param {Number} col
       * @param {Error} error
       * @returns {Boolean}
       */
      window.onerror = function(message, sourceUrl, line, col, error) {
        error = error ? error : {};
        jserror._counter++;
        var originatesFromLogging = (sourceUrl.indexOf(jserror._url) >= 0);
        var detailsUnavailable = (0 === line);
        var counterMaxReached = (counterMax && jserror._counter > counterMax);
        var suppressLogging = originatesFromLogging || (suppressWithoutDetails && detailsUnavailable) || counterMaxReached;
        if (!suppressLogging) {
          var previousLog = null;
          if (cm && cm.logger) {
            previousLog = cm.logger.getFormattedRecords();
          }
          jserror.report({
            uid: jserror._uid,
            counter: jserror._counter,
            previousLog: previousLog,
            url: document.location.href,
            error: {
              message: error.message || message,
              type: error.name || null,
              stack: error.stack || null,
              metaInfo: error.metaInfo || null,
              source: {
                'url': sourceUrl,
                'line': line,
                'col': col
              }
            }
          });
        }
        if (jserror._onerrorBackup) {
          jserror._onerrorBackup(message, sourceUrl, line, col, error);
        }
        if (suppressErrors) {
          return true;
        }
      }
    },

    /**
     * @param {Object} data
     */
    report: function(data) {
      var req = new XMLHttpRequest();
      req.open("POST", jserror._url);
      req.setRequestHeader("Content-Type", "application/json");
      req.send(JSON.stringify(data));
    }
  };

  jserror.install('/jserror', 10, false, false);

}).call(this);
