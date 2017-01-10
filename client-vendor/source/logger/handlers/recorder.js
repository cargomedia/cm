var util = require('util');

/**
 * @class Recorder
 * @param {{retention: <Number>}} [options]
 */
var Recorder = function(options) {
  this._records = [];
  this._options = _.defaults(options || {}, {
    retention: 300,     // seconds
    recordMaxSize: 200, // nb records
    jsonMaxSize: 50,
    format: '[{date} {level}] {message}'
  });
};

Recorder.prototype = {
  /**
   * @returns {String}
   */
  getFormattedRecords: function() {
    return _.map(this.getRecords(), function(record) {
      return this._recordFormatter(record);
    }, this).join('\n');
  },

  /**
   * @returns {{date: {Date}, messages: *[], context: {Object}}[]}
   */
  getRecords: function() {
    return this._records;
  },

  /**
   * @param {*[]} messages
   * @param {Object} context
   */
  addRecord: function(messages, context) {
    var record = {
      date: this._getDate(),
      messages: messages,
      context: context
    };
    this._records.push(record);
    this._cleanupRecords();
  },

  flushRecords: function() {
    this._records = [];
  },

  /**
   * @private
   */
  _cleanupRecords: function() {
    var retention = this._options.retention;
    var recordMaxSize = this._options.recordMaxSize;
    if (retention > 0) {
      var retentionTime = this._getDate() - (retention * 1000);
      this._records = _.filter(this._records, function(record) {
        return record.date > retentionTime;
      });
    }
    if (recordMaxSize > 0 && this._records.length > recordMaxSize) {
      this._records = this._records.slice(-recordMaxSize);
    }
  },

  /**
   * @param {{date: {Date}, messages: *[], context: {Object}}} record
   * @returns {String}
   * @private
   */
  _recordFormatter: function(record) {
    var log = this._options.format;
    _.each({
      date: record.date.toISOString(),
      level: record.context.level.name,
      message: this._messageFormatter(record.messages)
    }, function(value, key) {
      var pattern = new RegExp('{' + key + '}', 'g');
      log = log.replace(pattern, value);
    });
    return log;
  },

  /**
   * @param {*[]} messages
   * @returns {String}
   * @private
   */
  _messageFormatter: function(messages) {
    var clone = _.toArray(messages);
    var index, value, encoded;
    for (index = 0; index < clone.length; index++) {
      encoded = value = clone[index];

      if (_.isString(value) && 0 === index) {
        // about console.log and util.format substitution,
        // see https://developers.google.com/web/tools/chrome-devtools/debug/console/console-write#string-substitution-and-formatting
        // and https://nodejs.org/api/util.html#util_util_format_format
        value = value.replace(/%[idfoO]/g, '%s');
      } else if (value instanceof RegExp) {
        value = value.toString();
      } else if (value instanceof Date) {
        value = value.toISOString();
      } else if (_.isObject(value) && value._class) {
        value = '[' + value._class + (value._id && value._id.id ? ':' + value._id.id : '') + ']';
      } else if (_.isObject(value) && /^\[object ((?!Object).)+\]$/.test(value.toString())) {
        value = value.toString();
      }

      try {
        if (_.isString(value) || _.isNumber(value)) {
          encoded = value;
        } else {
          encoded = JSON.stringify(value);
          if (encoded.length > this._options.jsonMaxSize) {
            encoded = encoded.slice(0, this._options.jsonMaxSize - 4) + '…' + encoded[encoded.length - 1];
          }
        }
      } catch (e) {
        if (_.isUndefined(value)) {
          encoded = 'undefined';
        } else if (_.isNull(value)) {
          encoded = 'null';
        } else {
          encoded = '[unknown]'
        }
      }
      clone[index] = encoded;
    }
    return util.format.apply(util.format, clone);
  },

  /**
   * @returns {Date}
   * @private
   */
  _getDate: function() {
    return new Date();
  }
};


module.exports = Recorder;
