require(['bootstrap', 'tests/client/config'], function(bs, config) {
  bs.load(config.dependencies || [], function() {
    bs.run(config.suites);
  });
});
