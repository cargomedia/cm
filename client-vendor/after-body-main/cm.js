// explicitly define which object are accessible through the CM namespace
var cm = window.cm = window.cm || {};
var lib = cm.lib = window.cm.lib || {};

cm.logger = require('logger');

_.extend(lib, {
  Observer: require('cm/observer'),
  Media: {
    Video: require('cm/media/video'),
    Audio: require('cm/media/audio')
  },
  PersistentStorage: require('cm/storage')
});
