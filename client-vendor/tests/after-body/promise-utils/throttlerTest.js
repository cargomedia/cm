define(["after-body/promise-utils/promise-throttler"], function() {

  QUnit.module('promise-utils/promise-throttler');

  QUnit.test("cancel trailing", function(assert) {
    assert.expect(1);
    var done = assert.async();

    var check = [];

    var throttler = promiseThrottler(function(name) {
      check.push('call:' + name);
      return Promise
        .delay(100)
        .then(function() {
          check.push('resolve:' + name);
        })
        .finally(function() {
          check.push('finally:' + name);
        });
    });

    Promise
      .all([
        throttler('foo'),
        throttler('bar')
      ])
      .finally(function() {
        assert.deepEqual(check, ['call:foo', 'resolve:foo', 'finally:foo']);
        done();
      });
  });

  QUnit.test("cancel leading", function(assert) {
    assert.expect(1);
    var done = assert.async();

    var check = [];

    var throttler = promiseThrottler(function(name) {
      check.push('call:' + name);
      return Promise
        .delay(100)
        .then(function() {
          check.push('resolve:' + name);
        })
        .finally(function() {
          check.push('finally:' + name);
        });
    }, {cancelLeading: true});

    throttler('foo').then(function() {
      assert.ok(false);
    });
    throttler('bar').finally(function() {
      assert.deepEqual(check, ['call:foo', 'finally:foo', 'call:foo', 'resolve:bar', 'finally:bar']);
      done();
    });
  });

  QUnit.test("cancel queue", function(assert) {
    assert.expect(1);
    var done = assert.async();

    var check = [];

    var throttler = promiseThrottler(function(name) {
      check.push('call:' + name);
      return Promise
        .delay(100)
        .then(function() {
          check.push('resolve:' + name);
        })
        .finally(function() {
          check.push('finally:' + name);
        });
    }, {queue: true});

    Promise
      .all([
        throttler('foo'),
        throttler('bar')
      ])
      .finally(function() {
        assert.deepEqual(check, ['call:foo', 'resolve:foo', 'finally:foo', 'call:bar', 'resolve:bar', 'finally:bar']);
        done();
      });
  });
});
