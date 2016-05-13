require(['cm/tests/media/common'], function(common) {
  require(["cm/media/video"], function(Video) {

    QUnit.module('cm/media/video');

    if ('HTMLVideoElement' in window) {
      var videoUrl = 'client-vendor/after-body-source/cm/tests/resources/vp9-32x9.webm';
      common.test(Video, videoUrl);
    } else {  // skip on phantomjs
      common.skip();
    }
  });
});
