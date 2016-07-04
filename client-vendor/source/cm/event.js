/**
 * @class Event
 * @constructor
 */
var Event = function() {
};

_.extend(Event.prototype, Backbone.Events);

Event.prototype.constructor = Event;
Event.extend = Backbone.View.extend;

module.exports = Event;
