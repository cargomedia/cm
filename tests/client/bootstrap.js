QUnit.config.autostart = false;

define('bootstrap', function(require) {

  var Bootstrapper = function() {
  };

  Bootstrapper.prototype = {

    /**
     * @param {String} url
     */
    setBaseUrl: function(url) {
      requirejs.config({
        baseUrl: url
      });
    },

    /**
     * @param {{name: String, path: String, init: [Function]}[]} dependencies
     * @param {Function} callback
     * @param {*} scope
     */
    load: function(dependencies, callback, scope) {
      var paths = {};
      dependencies.forEach(function(dependency) {
        paths[dependency.name] = dependency.path;
      });
      requirejs.config({
        paths: paths
      });

      var dependenciesLoaded = [];
      dependencies.forEach(function(dependency, index) {
        require([dependency.name], function(lib) {
          dependenciesLoaded.push(lib);
          if (dependency.init) {
            dependency.init(lib);
          }
          if (index == dependencies.length - 1) {
            callback.apply(scope || this, dependenciesLoaded);
          }
        });
      });

      if (0 == dependencies.length) {
        callback.apply(scope || this, dependenciesLoaded);
      }
    },

    /**
     * @param {String[]} suitePaths
     */
    run: function(suitePaths) {
      var self = this;
      require(suitePaths, function() {
        var index = 0;
        var suites = Array.prototype.slice.call(arguments);
        var loadCurrentSuite = function() {
          if (index < suites.length) {
            loadSuite(suites[index]);
          } else {
            QUnit.start();
          }
        };
        var loadSuite = function(suite) {
          requirejs.config(suite.config || {});
          self.load(suite.dependencies || [], function() {
            require(suite.modules || [], function() {
              index++;
              loadCurrentSuite();
            });
          });
        };
        loadCurrentSuite();
      });
    }
  };

  return new Bootstrapper();
});
