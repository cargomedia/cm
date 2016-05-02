require(['bootstrap', 'tests/client/config'], function(bs, config) {
  bs.loadLibraries(function() {
    bs.addSuitePaths(config.suites);
    bs.run();
  });
});
