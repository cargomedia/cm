var util = require('util');

/**
 * @class Recorder
 * @param {{retention: <Number>}} [options]
 */
var Recorder = function(options) {
  this._records = [];
  this._options = _.defaults(options || {}, {
    retention: 300,
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

  /**
   * @private
   */
  _cleanupRecords: function() {
    if (this._options.retention) {
      var retentionTime = this._getDate() - (this._options.retention * 1000);
      this._records = _.filter(this._records, function(record) {
        return record.date > retentionTime;
      });
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
    if (clone.length > 0 && _.isString(clone[0])) {
      clone[0] = clone[0].replace(/%o/g, '%j');
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
