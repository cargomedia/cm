var Media = require('../media');

/**
 * @class Video
 * @extends Media
 */
var Video = Media.extend({

  constructor: function Video(video, options) {
    video = video || document.createElement('video');
    Media.call(this, video, options);
  }
});

module.exports = Video;
