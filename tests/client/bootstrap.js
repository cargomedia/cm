(function() {

  var config = {
    paths: {
      config: "/tests/client/config",
      jquery: "client-vendor/after-body/10-jquery/jquery",
      underscore: "client-vendor/after-body/20-underscore/underscore",
      backbone: "client-vendor/after-body/30-backbone/backbone",
      bluebird: "client-vendor/before-body/01-bluebird/01-bluebird"
    }
  };

  require.config(config);

  require(['config', 'jquery', 'underscore', 'backbone', 'bluebird'], function(testConfig, $, _, Backbone, Promise) {

    window.Promise = Promise;
    Promise.config({
      cancellation: true
    });

    require(testConfig.suites, function() {
      var executor = Promise.resolve();

      _.each(arguments, function(tests) {
        executor = executor.then(function() {
          return new Promise(function(resolve) {
            var total = tests.modules.length, count = 0;
            QUnit.moduleDone(function() {
              if (++count == total) {
                resolve();
              }
            });
            require.config(_.defaults({}, config || {}, tests.config));
            require(tests.dependencies || [], function() {
              require(tests.modules);
            });

          });
        });
      });
    });
  });

}());
