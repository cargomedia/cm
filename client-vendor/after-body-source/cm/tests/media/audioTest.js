require(['cm/tests/media/common'], function(common) {
  require(["cm/media/audio"], function(Audio) {

    QUnit.module('cm/media/audio');

    if ('HTMLAudioElement' in window) {
      var audioUrl = 'client-vendor/after-body-source/cm/tests/resources/opus-48khz.weba';
      common.test(Audio, audioUrl);
    } else {  // skip on phantomjs
      common.skip();
    }
  });
});
