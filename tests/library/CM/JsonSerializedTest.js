define(["CM/JsonSerialized"], function() {

  QUnit.module('CM/JsonSerialized');

  QUnit.test("compatible", function(assert) {
    var foo = new CM_JsonSerialized_Abstract({val1: 1});
    var bar = new CM_JsonSerialized_Abstract({val1: 2});

    assert.equal(foo.get('val1'), 1);
    assert.equal(bar.get('val1'), 2);
    assert.ok(foo.compatible(bar) && bar.compatible(foo));
    assert.ok(!foo.compatible({val1: 1}));
    assert.ok(!bar.compatible({val1: 1}));
  });

  QUnit.test("toJSON", function(assert) {
    var foo = new CM_JsonSerialized_Abstract({val1: 1, val2: {val: 2}});

    assert.deepEqual(foo.toJSON(), {val1: 1, val2: {val: 2}});

    foo.set({
      val1: 1,
      val2: new CM_JsonSerialized_Abstract({val: 2})
    });

    assert.deepEqual(foo.toJSON(), {val1: 1, val2: {val: 2}});
  });

  QUnit.test("equals", function(assert) {
    var foo = new CM_JsonSerialized_Abstract({val1: 1});
    var bar = new CM_JsonSerialized_Abstract({val1: 1});

    assert.ok(foo.equals(bar) && bar.equals(foo));
    assert.ok(!foo.equals());
    assert.ok(!foo.equals(null));
    assert.ok(!foo.equals({val1: 1}));
    assert.ok(!foo.equals(new CM_JsonSerialized_Abstract()));

    foo.set({val1: 2});
    assert.ok(!foo.equals(bar) && !bar.equals(foo));

    foo.set({
      val1: 1,
      val2: new CM_JsonSerialized_Abstract({val: 2})
    });
    bar.set({
      val1: 1,
      val2: new CM_JsonSerialized_Abstract({val: 2})
    });
    assert.ok(foo.equals(bar) && bar.equals(foo));

    foo.get('val2').set({val: 1});
    assert.ok(!foo.equals(bar) && !bar.equals(foo));

    foo.get('val2').set({val: 2});
    foo.set('val3', 3);
    assert.ok(!foo.equals(bar) && !bar.equals(foo));

    foo.unset('val1');
    foo.unset('val3');
    assert.ok(!foo.equals(bar) && !bar.equals(foo));

    foo.set('val1', 1);
    assert.ok(foo.equals(bar) && bar.equals(foo));

    bar.set('val3', 3);
    assert.ok(!foo.equals(bar) && !bar.equals(foo));

    bar.unset('val1');
    bar.unset('val3');
    assert.ok(!foo.equals(bar) && !bar.equals(foo));
  });


  QUnit.test("sync (flat)", function(assert) {
    var foo = new CM_JsonSerialized_Abstract({val1: 1});
    var bar = new CM_JsonSerialized_Abstract({val1: 1});

    var synced = [];

    foo.on('sync', function(model, syncAttributes) {
      synced.push(syncAttributes);
    });

    assert.throws(function() {
      foo.sync({val1: 1});
    }, /Failed to update the model, incompatible parameter/);

    foo.sync(bar);
    assert.deepEqual(synced, []);

    bar.set({val1: 3, val2: 1});
    foo.sync(bar);
    assert.equal(foo.get('val1'), 3);
    assert.equal(foo.get('val2'), 1);
    assert.deepEqual(synced, [{val1: 3, val2: 1}]);
  });

  QUnit.test("sync (nested)", function(assert) {
    var foo001 = new CM_JsonSerialized_Abstract({name: '1'});
    var foo011 = new CM_JsonSerialized_Abstract({name: '1.1'});
    var foo111 = new CM_JsonSerialized_Abstract({name: '1.1.1'});
    foo001.set('1.1', foo011);
    foo011.set('1.1.1', foo111);

    var clone001 = foo001.clone();
    var clone011 = foo011.clone();
    var clone111 = foo111.clone();
    clone001.set('1.1', clone011);
    clone011.set('1.1.1', clone111);

    assert.deepEqual(foo001.toJSON(), {
      'name': '1',
      '1.1': {
        'name': '1.1',
        '1.1.1': {
          'name': '1.1.1'
        }
      }
    });

    var synced = {foo001: null, foo011: null, foo111: null};

    foo001.on('sync', function(model, syncAttributes) {
      synced.foo001 = syncAttributes;
    });
    foo011.on('sync', function(model, syncAttributes) {
      synced.foo011 = syncAttributes;
    });
    foo111.on('sync', function(model, syncAttributes) {
      synced.foo111 = syncAttributes;
    });

    clone111.set('name', 'clone 1.1.1');
    foo001.sync(clone001);
    assert.equal(foo001.get('1.1').get('1.1.1').get('name'), 'clone 1.1.1');
    assert.deepEqual(synced.foo001, {
      '1.1': {
        '1.1.1': {
          'name': 'clone 1.1.1'
        }
      }
    });
    assert.deepEqual(synced.foo011, {
      '1.1.1': {
        'name': 'clone 1.1.1'
      }
    });
    assert.deepEqual(synced.foo111, {'name': 'clone 1.1.1'});


    synced = {foo001: null, foo011: null, foo111: null};

    clone011.set({
      'name': 'clone 1.1',
      'val1': 1
    });

    foo001.sync(clone001);
    assert.equal(foo001.get('1.1').get('name'), 'clone 1.1');
    assert.equal(foo001.get('1.1').get('val1'), 1);
    assert.deepEqual(synced.foo001, {
      '1.1': {
        'name': 'clone 1.1',
        'val1': 1
      }
    });
    assert.deepEqual(synced.foo011, {'name': 'clone 1.1', 'val1': 1});
    assert.deepEqual(synced.foo111, null);
  });
});
