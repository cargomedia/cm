module.exports = {
  suites: [
    'tests/client/suites/after-body.js',
    'tests/client/suites/after-body-source.js'
  ],

  dependencies: [
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
  ]
};
