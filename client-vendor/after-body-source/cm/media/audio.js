var Media = require('../media');

/**
 * @class Audio
 * @extends Media
 */
var Audio = Media.extend({

  constructor: function(audio, options) {
    audio = audio || document.createElement('audio');
    Media.call(this, audio, options);
  }
});

module.exports = Audio;
