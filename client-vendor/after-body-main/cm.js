// explicitly define which object are accessible through the VR namespace
var cm = window.cm = window.cm || {};
var lib = cm.lib = window.cm.lib || {};

_.extend(lib, {
  Observer: require('cm/observer'),
  Media: {
    Video: require('cm/media/video'),
    Audio: require('cm/media/audio')
  }
});
