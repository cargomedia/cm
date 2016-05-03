define(["cm/tests/media/common"], function() {

  var tests = {

    'play/stop/release': function(assert, Media, source) {
      assert.expect(41);
      var done = assert.async();

      var media = new Media();
      var el = media.getElement();
      var events = {};

      media.on('media', function(eventName) {
        events[eventName] = events[eventName] ? events[eventName] + 1 : 1;
      });

      assert.equal(el.networkState, HTMLMediaElement.NETWORK_EMPTY);
      assert.equal(el.readyState, HTMLMediaElement.HAVE_NOTHING);
      assert.equal(media.isPlaying(), false);
      assert.equal(media.getPromiseLoaded().isFulfilled(), false);

      media
        .getPromiseLoaded()
        .then(function() {
          assert.equal(events['media:setSource'], 1);
          assert.equal(el.networkState, HTMLMediaElement.NETWORK_IDLE);
          assert.equal(el.readyState, HTMLMediaElement.HAVE_ENOUGH_DATA);
          assert.equal(media.isPlaying(), false);
          return media.play();
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
          assert.equal(media.isPlaying(), true);
          return media.stop();
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
          assert.equal(media.isPlaying(), false);
          return media.play();
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
          assert.equal(media.isPlaying(), true);
          return media.release();
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
          assert.equal(media.isPlaying(), false);
          assert.equal(media.getPromiseLoaded().isFulfilled(), false);
        })
        .finally(done);

      media.setSource(source);

      // useful to run these tests on mobile (gesture required to play a media...)
      $('body').on('click', function() {
        media.play();
      });

      assert.equal(el.networkState, HTMLMediaElement.NETWORK_NO_SOURCE);
      assert.equal(el.readyState, HTMLMediaElement.HAVE_NOTHING);
      assert.equal(media.isPlaying(), false);
      assert.equal(media.getPromiseLoaded().isFulfilled(), false);
    },

    'options loop:true': function(assert, Media, source) {
      var done = assert.async();

      var media = new Media();
      media.setOptions({
        loop: true
      });
      var el = media.getElement();

      var seeking = 0;
      var seeked = 0;
      var error = null;

      media.setSource(source);

      media
        .play()
        .then(function() {
          el.addEventListener('seeking', function() {
            seeking++;
          });
          el.addEventListener('seeked', function() {
            seeked++;
          });
          return media
            ._getPromisePlaying()
            .timeout(1500);
        })
        .catch(function(reason) {
          error = reason;
        })
        .finally(function() {
          assert.equal(seeking, 1);
          assert.equal(seeked, 1);
          assert.equal(error, null);
          done();
        });
    },


    'options loop:false': function(assert, Media, source) {
      var done = assert.async();

      var media = new Media();
      media.setOptions({
        loop: false
      });
      var el = media.getElement();

      var seeking = 0;
      var seeked = 0;
      var error = null;

      media.setSource(source);

      media
        .play()
        .then(function() {
          return new Promise(function(resolve, reject) {
            el.addEventListener('seeking', function() {
              reject(new Error('media seek in loop:false mode.'));
            });
            el.addEventListener('ended', resolve);
          }).timeout(1500)
        })
        .catch(function(reason) {
          error = reason;
        })
        .finally(function() {
          assert.equal(error, null);
          done();
        });
    }
  };

  return {
    test: function(Media, source) {
      _.each(tests, function(fn, name) {
        QUnit.test(name, function(assert) {
          fn(assert, Media, source);
        });
      });
    },
    skip: function() {
      _.each(tests, function(fn, name) {
        QUnit.skip(name);
      });
    }
  };
});
