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
        foo: 100,
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
      assert.equal(adapter.getItem('foo'), null);

      var logger = {
        callCount: 0,
        warn: function(message, key, error) {
          this.callCount++;
          assert.equal('Failed to parse the `%s` PersistentStorage', message);
          assert.equal('bar', key);
          assert.equal("SyntaxError", error.name);
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
      assert.equal(logger.callCount, 0);

      adapter.setItem('bar', 'invalid json string');
      assert.strictEqual(data.get('foo'), undefined);
      assert.equal(logger.callCount, 1);

      data.set('foo', 300);
      assert.equal(data.get('foo'), 300);
      assert.equal(logger.callCount, 2);

      data.set('foo', 400);
      assert.equal(data.get('foo'), 400);
      assert.equal(logger.callCount, 2);

      adapter.clear();
      assert.equal(adapter.getItem('foo'), null);
      assert.equal(adapter.getItem('bar'), null);
    });
  });

  QUnit.test("Storage: not supported adapter", function(assert) {
    assert.expect(10);
    var logger = {
      warn: function(message, error) {
        assert.equal('Storage adapter not supported', message);
        assert.ok(error instanceof TypeError);
      }
    };
    var data = new PersistentStorage('foo', null, logger);

    data.set({
      foo: 100
    });
    assert.strictEqual(data.has('foo'), true);
    assert.equal(data.get('foo'), 100);
    data.remove('foo');
    assert.equal(data.has('foobar'), false);

    var logger = {
      warn: function(message, error) {
        assert.equal('Storage adapter not supported', message);
        assert.equal("Error: Failed to retrieve data from storage adapter", error.toString());
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
    assert.equal(data.has('foobar'), false);
  });
});
