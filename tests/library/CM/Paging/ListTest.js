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

  QUnit.test("get models", function(assert) {
    var list1 = new CM_Paging_List([
      new Backbone.Model({foo: 1}),
      new Backbone.Model({foo: 1}),
      new Backbone.Model({foo: 2})
    ]);
    assert.notOk(list1.get(1));
    assert.notOk(list1.get({foo: 1}));
    assert.ok(list1.get(list1.at(0)));
    assert.ok(list1.get(list1.at(0).cid));
    assert.ok(list1.get(new Backbone.Model({foo: 1})));
    assert.ok(list1.get(new Backbone.Model({foo: 2})));

    var list2 = new CM_Paging_List([
      new Backbone.Model({id: 1, foo: 1}),
      new Backbone.Model({id: 2, foo: 1})
    ]);
    assert.ok(list2.get({id: 1, foo: 2}));
    assert.ok(list2.get(1));
    assert.ok(list2.get(list2.at(0)));
    assert.ok(list2.get(list2.at(0).cid));
    assert.ok(list2.get(new Backbone.Model({id: 1, foo: 1})));
    assert.ok(list2.get(new Backbone.Model({id: 2, foo: 1})));

    var list3 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({
        foo: 1,
        bar: new CM_Frontend_JsonSerializable({
          val: 1
        })
      }),
      new CM_Frontend_JsonSerializable({foo: 1, bar: 2}),
      new CM_Frontend_JsonSerializable({foo: 2})
    ]);
    assert.notOk(list3.get(1));
    assert.notOk(list3.get(new CM_Frontend_JsonSerializable({
        foo: 1,
        bar: new CM_Frontend_JsonSerializable({
          val: 2
        })
      })
    ));
    assert.ok(list3.get(new CM_Frontend_JsonSerializable({
        foo: 1,
        bar: new CM_Frontend_JsonSerializable({
          val: 1
        })
      })
    ));
    assert.ok(list3.get(list3.at(0)));
    assert.ok(list3.get(list3.at(0).cid));

    var list4 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({
        id: 1,
        bar: new CM_Frontend_JsonSerializable({
          val: 1
        })
      }),
      new CM_Frontend_JsonSerializable({id: 1, bar: 2}),
      new CM_Frontend_JsonSerializable({id: 2})
    ]);
    assert.ok(list4.get(1));
    assert.ok(list4.get({id: 1}));
    assert.ok(list4.get(new CM_Frontend_JsonSerializable({id: 1})));
    assert.ok(list4.get(new CM_Frontend_JsonSerializable({
        id: 1,
        bar: new CM_Frontend_JsonSerializable({
          val: 2
        })
      })
    ));
    assert.ok(list4.get(list4.at(0)));
    assert.ok(list4.get(list4.at(0).cid));
  });

  QUnit.test("equals", function(assert) {
    var list = this.list;
    var clone = this.clone;
    assert.notOk(list.equals());
    assert.notOk(list.equals([]));
    assert.notOk(list.equals([{id: 1, foo: 1}, {id: 2, foo: 2}, {id: 3, foo: 3}]));
    assert.ok(list.equals(clone));
  });

  QUnit.test("equals: native Backbone.Model", function(assert) {
    var list1 = new CM_Paging_List([
      new Backbone.Model({foo: 2}),
      new Backbone.Model({foo: 3})
    ]);

    var list2 = new CM_Paging_List([
      new Backbone.Model({foo: 2}),
      new Backbone.Model({foo: 3})
    ]);

    assert.ok(list1.size(), 2);
    assert.ok(list2.size(), 2);
    assert.ok(list1.equals(list2));
  });

  QUnit.test("equals: native Backbone.Model with redundant data", function(assert) {
    var list1 = new CM_Paging_List([
      new Backbone.Model({foo: 2}),
      new Backbone.Model({foo: 2}),
      new Backbone.Model({foo: 3})
    ]);

    var list2 = new CM_Paging_List([
      new Backbone.Model({foo: 2}),
      new Backbone.Model({foo: 3}),
      new Backbone.Model({foo: 3})
    ]);

    assert.ok(list1.size(), 3);
    assert.ok(list2.size(), 3);
    assert.notOk(list1.equals(list2));
  });

  QUnit.test("equals: same list without ids", function(assert) {
    var list1 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({foo: 2}),
      new CM_Frontend_JsonSerializable({foo: 3})
    ]);

    var list2 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({foo: 2}),
      new CM_Frontend_JsonSerializable({foo: 3})
    ]);

    assert.ok(list1.size(), 2);
    assert.ok(list2.size(), 2);
    assert.ok(list1.equals(list2));
  });

  QUnit.test("equals: redundant data", function(assert) {
    var list1 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({foo: 2}),
      new CM_Frontend_JsonSerializable({foo: 2}),
      new CM_Frontend_JsonSerializable({foo: 3})
    ]);

    var list2 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({foo: 3}),
      new CM_Frontend_JsonSerializable({foo: 3}),
      new CM_Frontend_JsonSerializable({foo: 2})
    ]);

    assert.ok(list1.size(), 3);
    assert.ok(list2.size(), 3);
    assert.notOk(list1.equals(list2));
  });

  QUnit.test("equals: redundant ids", function(assert) {
    var list1 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({id: 2}),
      new CM_Frontend_JsonSerializable({id: 2}),
      new CM_Frontend_JsonSerializable({id: 3})
    ]);

    var list2 = new CM_Paging_List([
      new CM_Frontend_JsonSerializable({id: 3}),
      new CM_Frontend_JsonSerializable({id: 3}),
      new CM_Frontend_JsonSerializable({id: 2})
    ]);

    assert.ok(list1.size(), 2);
    assert.ok(list2.size(), 2);
    assert.ok(list1.equals(list2));
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
        }),
        new CM_Frontend_JsonSerializable({id: 2})
      ]);
      var clone = new CM_Paging_List([
        new CM_Frontend_JsonSerializable({
          id: 1,
          foo: 1,
          '1.1': new CM_Paging_List([
            new CM_Frontend_JsonSerializable({id: 'a', bar: 1})
          ])
        }),
        new CM_Frontend_JsonSerializable({id: 2})
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
    }, {
      id: 2
    }]);
  });

  QUnit.test("sync: equals", function(assert) {
    var list = this.list;
    var clone = this.clone;
    var synced = this.synced;

    var result = list.sync(clone);
    assert.equal(list.size(), 2);
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
    assert.equal(list.size(), 2);
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
    assert.equal(list.size(), 2);
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
    assert.equal(list.size(), 2);
    assert.equal(list.get(1).get('1.1').size(), 0);
    assert.deepEqual(result.updated[1].updated['1.1'].removed[0].toJSON(), {id: 'a', bar: 1});
    assert.deepEqual(synced[0].updated[1].updated['1.1'].removed[0].toJSON(), {id: 'a', bar: 1});
  });
});
