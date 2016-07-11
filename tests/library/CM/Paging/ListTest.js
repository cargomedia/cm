define(["CM/Frontend/JsonSerializable", "CM/Paging/List"], function() {

  QUnit.module('CM/Paging/List:flat', {
    beforeEach: function() {
      var list = new CM_Paging_List([
        new CM_Frontend_JsonSerializable({id: 1, foo: 1}),
        new CM_Frontend_JsonSerializable({id: 2, foo: 2}),
        new CM_Frontend_JsonSerializable({id: 3, foo: 3})
      ]);
      var clone = new CM_Paging_List([
        new CM_Frontend_JsonSerializable({id: 1, foo: 1}),
        new CM_Frontend_JsonSerializable({id: 2, foo: 2}),
        new CM_Frontend_JsonSerializable({id: 3, foo: 3})
      ]);

      var synced = [];
      list.on('list:sync', function(collection, syncAttributes) {
        synced.push(syncAttributes);
      });

      this.list = list;
      this.clone = clone;
      this.synced = synced;
    },

    afterEach: function() {
      this.list.off();
      this.list = null;
      this.clone = null;
      this.synced = null;
    }
  });

  QUnit.test("default model", function(assert) {
    var list = this.list;
    list.add({id: 4, foo: 4});
    assert.ok(list.get(4) instanceof CM_Frontend_JsonSerializable);
    assert.deepEqual(list.get(4).toJSON(), {id: 4, foo: 4});

    var Foo = CM_Frontend_JsonSerializable.extend({});
    list.add(new Foo({id: 5, foo: 5}));
    assert.ok(list.get(5) instanceof Foo);
    assert.deepEqual(list.get(5).toJSON(), {id: 5, foo: 5});
  });

  QUnit.test("equals", function(assert) {
    var list = this.list;
    var clone = this.clone;
    assert.notOk(list.equals());
    assert.notOk(list.equals([]));
    assert.notOk(list.equals([{id: 1, foo: 1}, {id: 2, foo: 2}, {id: 3, foo: 3}]));
    assert.ok(list.equals(clone));
  });

  QUnit.test("toJSON", function(assert) {
    var list = this.list;
    assert.deepEqual(list.toJSON(), [{id: 1, foo: 1}, {id: 2, foo: 2}, {id: 3, foo: 3}]);
  });

  QUnit.test("sync: invalid", function(assert) {
    var list = this.list;
    assert.throws(function() {
      list.sync();
    }, /Failed to update the collection, incompatible parameter/);
    assert.throws(function() {
      list.sync([{id: 1, foo: 1}]);
    }, /Failed to update the collection, incompatible parameter/);
    assert.throws(function() {
      list.sync(new CM_Frontend_JsonSerializable());
    }, /Failed to update the collection, incompatible parameter/);
  });

  QUnit.test("sync: equals", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    var result = list.sync(clone);
    assert.equal(list.size(), 3);
    assert.equal(result, null);
    assert.deepEqual(synced, []);
  });

  QUnit.test("sync: add", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.add(new CM_Frontend_JsonSerializable({id: 4, foo: 4}));
    var result = list.sync(clone);
    assert.equal(list.size(), 4);
    assert.deepEqual(result.added[0].toJSON(), {id: 4, foo: 4});
    assert.deepEqual(synced[0].added[0].toJSON(), {id: 4, foo: 4});
  });

  QUnit.test("sync: change", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.at(1).set('foo', 9);
    var result = list.sync(clone);
    assert.equal(list.size(), 3);
    assert.deepEqual(result, {updated: {2: {updated: {foo: 9}}}});
    assert.deepEqual(synced[0], {updated: {2: {updated: {foo: 9}}}});
  });

  QUnit.test("sync: remove", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.remove(2);
    var result = list.sync(clone);
    assert.equal(list.size(), 2);
    assert.deepEqual(result.removed[0].toJSON(), {id: 2, foo: 2});
    assert.deepEqual(synced[0].removed[0].toJSON(), {id: 2, foo: 2});
  });


  QUnit.module('CM/Paging/List:nested', {
    beforeEach: function() {
      var list = new CM_Paging_List([
        new CM_Frontend_JsonSerializable({
          id: 1,
          foo: 1,
          '1.1': new CM_Paging_List([
            new CM_Frontend_JsonSerializable({id: 'a', bar: 1})
          ])
        })
      ]);
      var clone = new CM_Paging_List([
        new CM_Frontend_JsonSerializable({
          id: 1,
          foo: 1,
          '1.1': new CM_Paging_List([
            new CM_Frontend_JsonSerializable({id: 'a', bar: 1})
          ])
        })
      ]);

      var synced = [];
      list.on('list:sync', function(collection, syncAttributes) {
        synced.push(syncAttributes);
      });

      this.list = list;
      this.clone = clone;
      this.synced = synced;
    },

    afterEach: function() {
      this.list.off();
      this.list = null;
      this.clone = null;
      this.synced = null;
    }
  });

  QUnit.test("equals", function(assert) {
    var list = this.list;
    var clone = this.clone;

    assert.ok(list.equals(clone));
    clone.get(1).get('1.1').get('a').set('bar', 2);
    assert.notOk(list.equals(clone));
  });

  QUnit.test("toJSON", function(assert) {
    var list = this.list;
    assert.deepEqual(list.toJSON(), [{
      id: 1,
      foo: 1,
      '1.1': [{id: 'a', bar: 1}]
    }]);
  });

  QUnit.test("sync: equals", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    var result = list.sync(clone);
    assert.equal(list.size(), 1);
    assert.equal(list.get(1).get('1.1').size(), 1);
    assert.equal(result, null);
    assert.deepEqual(synced, []);
  });

  QUnit.test("sync: add to child", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.get(1).get('1.1').add(new CM_Frontend_JsonSerializable({id: 'b', bar: 2}));
    var result = list.sync(clone);
    assert.equal(list.size(), 1);
    assert.equal(list.get(1).get('1.1').size(), 2);
    assert.deepEqual(result.updated[1].updated['1.1'].added[0].toJSON(), {id: 'b', bar: 2});
    assert.deepEqual(synced[0].updated[1].updated['1.1'].added[0].toJSON(), {id: 'b', bar: 2});
  });

  QUnit.test("sync: change child", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.get(1).get('1.1').get('a').set('bar', 9);
    var result = list.sync(clone);
    assert.equal(list.size(), 1);
    assert.equal(list.get(1).get('1.1').size(), 1);
    assert.deepEqual(result.updated[1].updated['1.1'].updated['a'], {updated: {bar: 9}});
    assert.deepEqual(synced[0].updated[1].updated['1.1'].updated['a'], {updated: {bar: 9}});
  });

  QUnit.test("sync: remove", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    clone.get(1).get('1.1').remove('a');
    var result = list.sync(clone);
    assert.equal(list.size(), 1);
    assert.equal(list.get(1).get('1.1').size(), 0);
    assert.deepEqual(result.updated[1].updated['1.1'].removed[0].toJSON(), {id: 'a', bar: 1});
    assert.deepEqual(synced[0].updated[1].updated['1.1'].removed[0].toJSON(), {id: 'a', bar: 1});
  });
});
