var Logger = require('./vendor/src/logger');
var Recorder = require('./handlers/recorder');

var consoleHandler = Logger.createDefaultHandler();
var logRecorder = new Recorder();

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
  consoleHandler(messages, context);
  logRecorder.addRecord(messages, context);
});

Logger.setLevel(Logger.DEBUG);

module.exports = Logger;
