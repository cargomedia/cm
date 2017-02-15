(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.SocketRedis = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
var SockJS;

/**
 * @class SocketRedis
 *
 * @param {String} url
 * @constructor
 */
function Client(url) {
  /** @type {String} */
  this._url = url;
  /** @type {SockJS} */
  this._sockJS = null;
  /** @type {Number} */
  this._closeStamp = null;
  /** @type {Object} */
  this._subscribes = {};
  /** @type {Number} */
  this._heartbeatTimeout = null;
  /** @type {Number} */
  this._reopenTimeout = null;
}

Client.prototype.open = function() {
  clearTimeout(this._reopenTimeout);
  this._reopenTimeout = null;

  SockJS = (typeof window !== "undefined" ? window['SockJS'] : typeof global !== "undefined" ? global['SockJS'] : null);
  this._sockJS = new SockJS(this._url);
  this._sockJS.onopen = this._onopen.bind(this);
  this._sockJS.onclose = this._reopen.bind(this);
};

Client.prototype._reopen = function() {
  this._stopHeartbeat();
  this._closeStamp = new Date().getTime();
  this._sockJS.onopen = null;
  this._sockJS.onclose = null;
  this._sockJS = null;

  this._reopenTimeout = setTimeout(function() {
    this.open();
  }.bind(this), 1000);
};

Client.prototype._onopen = function() {
  var self = this;
  Object.keys(this._subscribes).forEach(function(channel) {
    self._subscribe(channel, self._closeStamp);
  });

  this._closeStamp = null;
  this._sockJS.onmessage = function(event) {
    var data = JSON.parse(event.data);
    var subscribe = self._subscribes[data.channel];
    if (subscribe && subscribe.callback) {
      subscribe.callback.call(self, data.event, data.data);
    }
  };
  this._startHeartbeat();
  this.onopen.call(this);
};

Client.prototype.close = function() {
  this._stopHeartbeat();
  this._sockJS.onclose = null;

  this._sockJS.close();
  this.onclose.call(this);
};

/**
 * @param {String} channel
 * @param {Number} [start]
 * @param {Object} [data]
 * @param {Function} [onmessage] fn(data)
 */
Client.prototype.subscribe = function(channel, start, data, onmessage) {
  if (this._subscribes[channel]) {
    throw 'Channel `' + channel + '` is already subscribed';
  }
  this._subscribes[channel] = {event: {channel: channel, start: start, data: data}, callback: onmessage};
  if (this.isOpen()) {
    this._subscribe(channel);
  }
};

/**
 * @param {String} channel
 */
Client.prototype.unsubscribe = function(channel) {
  if (this._subscribes[channel]) {
    delete this._subscribes[channel];
  }
  if (this.isOpen()) {
    this._sockJS.send(JSON.stringify({event: 'unsubscribe', data: {channel: channel}}));
  }
};

Client.prototype.isOpen = function() {
  return this._sockJS && this._sockJS.readyState === SockJS.OPEN;
};

/**
 * @param {Object} data
 */
Client.prototype.send = function(data) {
  this._sockJS.send(JSON.stringify({event: 'message', data: {data: data}}));
};

/**
 * @param {String} channel
 * @param {String} event
 * @param {Object} data
 */
Client.prototype.publish = function(channel, event, data) {
  this._sockJS.send(JSON.stringify({event: 'publish', data: {channel: channel, event: event, data: data}}));
};

Client.prototype.onopen = function() {
};

Client.prototype.onclose = function() {
};

/**
 * @param {String} channel
 * @param {Number} [startStamp]
 */
Client.prototype._subscribe = function(channel, startStamp) {
  var event = this._subscribes[channel].event;
  if (!startStamp) {
    startStamp = event.start || new Date().getTime();
  }
  var eventData = event.data || {};
  this._sockJS.send(JSON.stringify({event: 'subscribe', data: {channel: event.channel, data: eventData, start: startStamp}}));
};

Client.prototype._startHeartbeat = function() {
  this._heartbeatTimeout = setTimeout(function() {
    this._sockJS.send(JSON.stringify({event: 'heartbeat'}));
    this._startHeartbeat();
  }.bind(this), 25 * 1000);
};

Client.prototype._stopHeartbeat = function() {
  clearTimeout(this._heartbeatTimeout);
};

module.exports = Client;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}]},{},[1])(1)
});