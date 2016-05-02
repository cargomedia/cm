/**
 * Strongly inspired by https://github.com/jonkemp/qunit-phantomjs-runner/
 */

/*global phantom:false, require:false, console:false, window:false, QUnit:false */

(function() {
  'use strict';

  var system = require('system');
  var failures = [];

  var url, page, timeout,
    args = require('system').args;

  // arg[0]: scriptName, args[1...]: arguments
  if (args.length < 2) {
    console.error('Usage:\n  phantomjs [phantom arguments] runner-list.js [url-of-your-qunit-testsuite] [timeout-in-seconds] [page-properties]');
    exit(1);
  }

  url = args[1];

  if (args[2] !== undefined) {
    timeout = parseInt(args[2], 10);
  }

  page = require('webpage').create();

  if (args[3] !== undefined) {
    try {
      var pageProperties = JSON.parse(args[3]);

      if (pageProperties) {
        for (var prop in pageProperties) {
          if (pageProperties.hasOwnProperty(prop)) {
            page[prop] = pageProperties[prop];
          }
        }
      }
    } catch (e) {
      console.error('Error parsing "' + args[3] + '": ' + e);
    }
  }

  page.onInitialized = function() {
    page.evaluate(addLogging);
  };

  page.onCallback = function(message) {
    var result,
      failed;

    var logError = function(text) {
      system.stderr.write(text + "\n");
    };

    if (message) {
      if (message.name === 'QUnit.moduleStart') {
        console.log('\n> ' + message.data.name);
      }
      if (message.name === 'QUnit.testStart') {
        system.stdout.write(message.data.name + ': ');
      }
      if (message.name === 'QUnit.testDone') {
        if (message.data.skipped) {
          console.log('skipped.');
        } else {
          system.stdout.write('\n');
        }
      }
      if (message.name === 'QUnit.log') {
        if (message.data.result) {
          system.stdout.write('✔ ');
        } else {
          system.stderr.write('✖ ');
          failures.push(message.data);
        }
      }
      if (message.name === 'QUnit.done') {
        result = message.data;
        failed = !result || !result.total || result.failed;

        console.log('\n' + 'Took ' + result.runtime + 'ms to run ' + result.total + ' tests. ' + result.passed + ' passed, ' + result.failed + ' failed.');

        if (!result.total) {
          logError('No tests were executed. Are you loading tests asynchronously?');
        }

        if (failures.length > 0) {
          failures.forEach(function(failure) {
            logError('');
            logError('> ' + failure.module + ': ' + failure.name);
            logError('    actual: ' + failure.actual);
            logError('  expected: ' + failure.expected);
            // could not work as expected with bluebird because of https://github.com/petkaantonov/bluebird/issues/942
            logError('  trace:');
            logError(failure.source);
          });
        }

        exit(failed ? 1 : 0);
      }
    }
  };

  page.open(url, function(status) {
    if (status !== 'success') {
      console.error('Unable to access network: ' + status);
      exit(1);
    } else {
      // Cannot do this verification with the 'DOMContentLoaded' handler because it
      // will be too late to attach it if a page does not have any script tags.
      var qunitMissing = page.evaluate(function() {
        return (typeof QUnit === 'undefined' || !QUnit);
      });
      if (qunitMissing) {
        console.error('The `QUnit` object is not present on this page.');
        exit(1);
      }

      // Set a default timeout value if the user does not provide one
      if (typeof timeout === 'undefined') {
        timeout = 5;
      }

      // Set a timeout on the test running, otherwise tests with async problems will hang forever
      setTimeout(function() {
        console.error('The specified timeout of ' + timeout + ' seconds has expired. Aborting...');
        exit(1);
      }, timeout * 1000);

      // Do nothing... the callback mechanism will handle everything!
    }
  });

  function addLogging() {

    window.console.error = window.console.warning = window.console.warn = window.console.log = window.console.debug = function() {
    };

    window.document.addEventListener('DOMContentLoaded', function() {

      QUnit.log(function(details) {
        if (typeof window.callPhantom === 'function') {
          window.callPhantom({
            'name': 'QUnit.log',
            'data': details
          });
        }
      });
      QUnit.moduleStart(function(details) {
        if (typeof window.callPhantom === 'function') {
          window.callPhantom({
            'name': 'QUnit.moduleStart',
            'data': details
          });
        }
      });
      QUnit.testStart(function(details) {
        if (typeof window.callPhantom === 'function') {
          window.callPhantom({
            'name': 'QUnit.testStart',
            'data': details
          });
        }
      });
      QUnit.testDone(function(details) {
        if (typeof window.callPhantom === 'function') {
          window.callPhantom({
            'name': 'QUnit.testDone',
            'data': details
          });
        }
      });
      QUnit.done(function(result) {
        if (typeof window.callPhantom === 'function') {
          window.callPhantom({
            'name': 'QUnit.done',
            'data': result
          });
        }
      });
    }, false);
  }

  function exit(code) {
    if (page) {
      page.close();
    }
    setTimeout(function() {
      phantom.exit(code);
    }, 0);
  }
})();
