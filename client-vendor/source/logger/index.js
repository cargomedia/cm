var Logger = require('./vendor/src/logger');
var Recorder = require('./handlers/recorder');

var consoleHandler = Logger.createDefaultHandler();
var logRecorder = new Recorder();

var options = {
  dev: false
};

/**
 * @returns {String}
 */
Logger.getFormattedRecords = function() {
  return logRecorder.getFormattedRecords();
};

/**
 * @returns {{date: {Date}, messages: *[], context: {Object}}[]}
 */
Logger.getRecords = function() {
  return logRecorder.getRecords();
};

Logger.setHandler(function(messages, context) {
  var isDev = context.level.value < Logger.WARN.value;
  if (!isDev || (options.dev && isDev)) {
    consoleHandler(messages, context);
  }
  logRecorder.addRecord(messages, context);
});

Logger.configure = function(newOptions) {
  _.extend(options, newOptions);
};

Logger.setLevel(Logger.DEBUG);

module.exports = Logger;
