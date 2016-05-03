require(['cm/tests/media/common'], function(common) {
  require(["cm/media/video"], function(Video) {

    QUnit.module('cm/media/video');

    if (!!window.HTMLVideoElement) {
      var videoUrl = 'client-vendor/after-body-source/cm/tests/resources/vp9-32x9.webm';
      common.test(Video, videoUrl);
    } else {
      common.skip();
    }
  });
});
