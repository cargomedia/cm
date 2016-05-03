var Media = require('../media');

/**
 * @class Audio
 * @extends Media
 */
var Audio = Media.extend({

  /**
   * @param {HTMLAudioElement} audio
   */
  constructor: function(audio) {
    audio = audio || document.createElement('audio');
    Media.call(this, audio);
    this.getElement().autoplay = true;
  }
});

module.exports = Audio;
