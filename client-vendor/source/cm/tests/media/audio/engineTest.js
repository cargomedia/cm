require(["cm/media/audio/engine"], function(AudioEngine) {

  var audioUrl = 'client-vendor/after-body-source/cm/tests/resources/drums.ogg';

  QUnit.module('cm/media/audio/engine');
  var tester = AudioEngine.isAudioAPISupported() ? QUnit.test.bind(QUnit) : QUnit.skip.bind(QUnit);

  tester('instantiation', function(assert) {
    assert.expect(1);
    var done = assert.async();

    var engine = new AudioEngine(audioUrl);
    var source = engine.getAudioNode('source');

    engine
      .start()
      .then(function() {
        return new Promise(function(resolve) {
          source.addEventListener('ended', function() {
            assert.ok(true);
            resolve();
          });
        });
      })
      .timeout(1200)
      .finally(done);
  });
});
