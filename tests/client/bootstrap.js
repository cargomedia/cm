QUnit.config.autostart = false;

define('bootstrap', function(require) {

  var Bootstrapper = function() {
    this._suitePaths = [];
    this._libraries = [];
    this._baseUrl = '';
  };

  Bootstrapper.prototype = {

    /**
     * @param {...String[]} suites
     */
    addSuitePaths: function(suites) {
      this._suitePaths = this._suitePaths.concat(suites);
    },

    /**
     * @param {...{name: String, path: String, init: [Function]}} libraries
     */
    addLibraries: function(libraries) {
      this._libraries = this._libraries.concat(Array.prototype.slice.call(arguments));
    },

    /**
     * @param {String} url
     */
    setBaseUrl: function(url) {
      this._baseUrl = url;
    },

    /**
     * @param {Function} callback
     * @param {*} scope
     */
    loadLibraries: function(callback, scope) {
      var libraries = this._libraries;
      var baseUrl = this._baseUrl;
      var paths = {};


      libraries.forEach(function(library) {
        paths[library.name] = library.path;
      });

      requirejs.config({
        baseUrl: baseUrl,
        paths: paths
      });

      var initializedLibs = [];
      libraries.forEach(function(library, index) {
        require([library.name], function(lib) {
          initializedLibs.push(lib);
          if (library.init) {
            library.init(lib);
          }
          if (index == libraries.length - 1) {
            callback.apply(scope || this, initializedLibs);
          }
        });
      });

      if (0 == libraries.length) {
        callback.apply(scope || this, initializedLibs);
      }
    },

    run: function() {
      var suitePaths = this._suitePaths;
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
          require(suite.dependencies || [], function() {
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

  var bootstrapper = new Bootstrapper();

  bootstrapper.addLibraries(
    {
      name: 'jquery',
      path: 'client-vendor/after-body/10-jquery/jquery'
    },
    {
      name: 'underscore',
      path: 'client-vendor/after-body/20-underscore/underscore'
    },
    {
      name: 'backbone',
      path: 'client-vendor/after-body/30-backbone/backbone'
    },
    {
      name: 'bluebird',
      path: 'client-vendor/before-body/01-bluebird/01-bluebird',
      init: function(Promise) {
        window.Promise = Promise;
        Promise.config({
          cancellation: true
        });
      }
    }
  );

  return bootstrapper;
});
