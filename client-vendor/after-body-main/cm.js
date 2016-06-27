// explicitly define which object are accessible through the VR namespace
var cm = window.cm = window.cm || {};
var lib = cm.lib = window.cm.lib || {};

_.extend(lib, {
  Observer: require('./observer'),
  Media: {
    Video: require('./media/video'),
    Audio: require('./media/audio')
  }
});
