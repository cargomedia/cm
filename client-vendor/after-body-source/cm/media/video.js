var Media = require('../media');

/**
 * @class Video
 * @extends Media
 */
var Video = Media.extend({

  /**
   * @param {HTMLVideoElement} video
   */
  constructor: function Video(video) {
    video = video || document.createElement('video');
    Media.call(this, video);
    this.getElement().loop = true;
  }
});

module.exports = Video;
