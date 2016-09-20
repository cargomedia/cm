define(["cm/storage"], function(PersistentStorage) {

  QUnit.module('cm/storage');

  QUnit.test("Storage: sessionStorage adapter", function(assert) {
    var adapter = window.sessionStorage;
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

    adapter.setItem('bar', '{\"foo\":100}');
    data = new PersistentStorage('bar', adapter);
    assert.strictEqual(data.has('foo'), true);
    assert.equal(data.get('foo'), 100);
    assert.deepEqual(data.get(), {foo: 100});

    sessionStorage.clear();
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
