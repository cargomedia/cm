define(["cm/storage", "cm/adapter/memory"], function(PersistentStorage, AdapterMemory) {

  QUnit.module('cm/storage');

  QUnit.test("Storage: get/set/del", function(assert) {
    var adapters = [
      window.sessionStorage,
      window.localStorage,
      new AdapterMemory()
    ];

    adapters.forEach(function(adapter) {
      var data = new PersistentStorage('foo', adapter);

      data.set({
        foo: 100
      });
      assert.strictEqual(data.has('foo'), true);
      assert.equal(data.get('foo'), 100);
      assert.deepEqual(data.get(), {foo: 100});
      assert.equal(adapter.getItem('foo'), '{\"foo\":100}');

      data.set('bar', '10');
      assert.deepEqual(data.get(), {foo: 100, bar: "10"});
      assert.equal(adapter.getItem('foo'), '{\"foo\":100,\"bar\":"10"}');

      data.set({
        bar: '100'
      });
      assert.deepEqual(data.get(), {foo: 100, bar: "100"});
      assert.equal(adapter.getItem('foo'), '{\"foo\":100,\"bar\":"100"}');

      data.remove('foobar');
      assert.strictEqual(data.has('foobar'), false);
      assert.strictEqual(data.get('foobar'), undefined);
      assert.equal(adapter.getItem('foo'), '{\"foo\":100,\"bar\":"100"}');

      data.clear();
      assert.equal(adapter.getItem('foo'), '{}');

      data.delete();
      assert.strictEqual(adapter.getItem('foo'), null);

      var logger = {
        callCount: 0,
        warn: function(message, key) {
          this.callCount++;
          assert.equal(message, 'Invalid value stored in `%s`, reset as an empty Object');
          assert.equal(key, 'bar');
        }
      };

      adapter.setItem('bar', '{\"foo\":100}');
      data = new PersistentStorage('bar', adapter, logger);
      assert.strictEqual(data.has('foo'), true);
      assert.equal(data.get('foo'), 100);
      assert.deepEqual(data.get(), {foo: 100});

      adapter.setItem('bar', '{\"foo\":200}');
      assert.equal(data.get('foo'), 200);
      assert.deepEqual(data.get(), {foo: 200});

      adapter.setItem('bar', '{\"foo\":300}');
      data.set('foo2', 100);
      assert.deepEqual(data.get(), {foo: 300, foo2: 100});

      adapter.setItem('bar', '{\"foo3\":100}');
      data.set({
        'foo4': 100
      });
      assert.deepEqual(data.get(), {foo3: 100, foo4: 100});

      assert.equal(logger.callCount, 0);

      adapter.setItem('bar', 'invalid json string');
      assert.strictEqual(data.get('foo'), undefined);
      assert.deepEqual(data.get(), {});
      assert.equal(logger.callCount, 1);

      adapter.setItem('bar', 'null');
      assert.strictEqual(data.get('foo'), undefined);
      assert.deepEqual(data.get(), {});
      assert.equal(logger.callCount, 2);

      adapter.setItem('bar', '[]');
      assert.strictEqual(data.get('foo'), undefined);
      assert.deepEqual(data.get(), {});
      assert.equal(logger.callCount, 3);

      data.set('foo', 400);
      assert.equal(data.get('foo'), 400);
      assert.equal(logger.callCount, 3);

      adapter.clear();
      assert.strictEqual(adapter.getItem('foo'), null);
      assert.strictEqual(adapter.getItem('bar'), null);
    });
  });

  QUnit.test("Storage: not supported adapter", function(assert) {
    assert.expect(10);
    var logger = {
      warn: function(message, error) {
        assert.equal(message, 'Storage adapter not supported');
        assert.ok(error instanceof Error);
      }
    };
    var data = new PersistentStorage('foo', {
      setItem: function() {
        throw new Error('Not Supported');
      },
      getItem: function() {
        throw new Error('Not Supported');
      },
      removeItem: function() {
        throw new Error('Not Supported');
      }
    }, logger);

    data.set({
      foo: 100
    });
    assert.strictEqual(data.has('foo'), true);
    assert.equal(data.get('foo'), 100);
    data.remove('foo');
    assert.strictEqual(data.has('foobar'), false);

    var logger = {
      warn: function(message, error) {
        assert.equal(message, 'Storage adapter not supported');
        assert.equal(error.toString(), "Error: Failed to retrieve data from storage adapter");
      }
    };

    var data = new PersistentStorage('foo', {
      setItem: function() {
      },
      getItem: function() {
      },
      removeItem: function() {
      }
    }, logger);

    data.set({
      foo: 100
    });
    assert.strictEqual(data.has('foo'), true);
    assert.equal(data.get('foo'), 100);
    data.remove('foo');
    assert.strictEqual(data.has('foobar'), false);
  });
});
