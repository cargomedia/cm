define(["cm/media"], function(Media) {

  var videoUrl = 'client-vendor/after-body-source/cm/tests/resources/mini-pattern.webm';

  QUnit.module('cm/media');

  QUnit.test("play/stop/release source file", function(assert) {
    assert.expect(41);
    var done = assert.async();
    var video = new Media(document.createElement('video'));
    var el = video.getElement();
    el.loop = true;

    for (name in el) {
      if (/^on/.test(name)) {
        (function(event) {
          el.addEventListener(event, function() {
            console.debug(event, arguments);
          });
        })(name.match(/^on(.*)/)[1]);
      }
    }

    var events = {};
    video.on('media', function(eventName) {
      events[eventName] = events[eventName] ? events[eventName] + 1 : 1;
    });

    assert.equal(el.networkState, HTMLMediaElement.NETWORK_EMPTY);
    assert.equal(el.readyState, HTMLMediaElement.HAVE_NOTHING);
    assert.equal(video.isPlaying(), false);
    assert.equal(video.getPromiseLoaded().isFulfilled(), false);

    video
      .getPromiseLoaded()
      .then(function() {
        assert.equal(events['media:setSource'], 1);
        assert.equal(el.networkState, HTMLMediaElement.NETWORK_IDLE);
        assert.equal(el.readyState, HTMLMediaElement.HAVE_ENOUGH_DATA);
        assert.equal(video.isPlaying(), false);
        return video.play();
      })
      .then(function() {
        assert.equal(events['media:play'], 1);
        assert.equal(events['media:playing'], 1);
        // desktop: el.networkState == HTMLMediaElement.NETWORK_IDLE
        // mobile: el.networkState == HTMLMediaElement.NETWORK_LOADING
        assert.ok(
          el.networkState == HTMLMediaElement.NETWORK_IDLE ||
          el.networkState == HTMLMediaElement.NETWORK_LOADING
        );
        assert.equal(el.readyState, HTMLMediaElement.HAVE_ENOUGH_DATA);
        assert.equal(video.isPlaying(), true);
        return video.stop();
      })
      .then(function() {
        assert.equal(events['media:play'], 1);
        assert.equal(events['media:playing'], 1);
        assert.equal(events['media:stop'], 1);
        assert.equal(events['media:stopped'], 1);
        // desktop: el.networkState == HTMLMediaElement.NETWORK_IDLE
        // mobile: el.networkState == HTMLMediaElement.NETWORK_LOADING
        assert.ok(
          el.networkState == HTMLMediaElement.NETWORK_IDLE ||
          el.networkState == HTMLMediaElement.NETWORK_LOADING
        );
        // firefox: el.readyState == HTMLMediaElement.HAVE_ENOUGH_DATA
        //  chrome: el.readyState == HTMLMediaElement.HAVE_METADATA
        assert.ok(
          el.readyState == HTMLMediaElement.HAVE_METADATA ||
          el.readyState == HTMLMediaElement.HAVE_ENOUGH_DATA
        );
        assert.equal(video.isPlaying(), false);
        return video.play();
      })
      .then(function() {
        assert.equal(events['media:play'], 2);
        assert.equal(events['media:playing'], 2);
        assert.equal(events['media:stop'], 1);
        assert.equal(events['media:stopped'], 1);
        // desktop: el.networkState == HTMLMediaElement.NETWORK_IDLE
        // mobile: el.networkState == HTMLMediaElement.NETWORK_LOADING
        assert.ok(
          el.networkState == HTMLMediaElement.NETWORK_IDLE ||
          el.networkState == HTMLMediaElement.NETWORK_LOADING
        );

        // firefox: el.readyState == HTMLMediaElement.HAVE_METADATA
        //  chrome: el.readyState == HTMLMediaElement.HAVE_ENOUGH_DATA
        assert.ok(
          el.readyState == HTMLMediaElement.HAVE_METADATA ||
          el.readyState == HTMLMediaElement.HAVE_ENOUGH_DATA
        );
        assert.equal(video.isPlaying(), true);
        return video.release();
      })
      .then(function() {
        assert.equal(events['media:play'], 2);
        assert.equal(events['media:playing'], 2);
        assert.equal(events['media:stop'], 2);
        assert.equal(events['media:stopped'], 2);
        assert.equal(events['media:release'], 1);
        assert.equal(events['media:released'], 1);
        assert.equal(el.networkState, HTMLMediaElement.NETWORK_EMPTY);
        assert.equal(el.readyState, HTMLMediaElement.HAVE_NOTHING);
        assert.equal(video.isPlaying(), false);
        assert.equal(video.getPromiseLoaded().isFulfilled(), false);
      })
      .finally(done);

    video.setSource(videoUrl);

    // useful to run these tests on mobile (gesture required to play a media...)
    $('body').on('click', function() {
      video.play();
    });

    assert.equal(el.networkState, HTMLMediaElement.NETWORK_NO_SOURCE);
    assert.equal(el.readyState, HTMLMediaElement.HAVE_NOTHING);
    assert.equal(video.isPlaying(), false);
    assert.equal(video.getPromiseLoaded().isFulfilled(), false);
  });
});
