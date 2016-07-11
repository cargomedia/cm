define(["CM/Frontend/AbstractTrait", "CM/Frontend/SynchronizableTrait"], function() {

  QUnit.module('CM/Frontend/SynchronizableTrait');

  QUnit.test("isSynchronizable", function(assert) {

    var Foo = Backbone.Model.extend({
      equals: function() {
        return true;
      },
      sync: function() {
      },
      toJSON: function() {
      }
    });
    CM_Frontend_SynchronizableTrait.applyImplementation(Foo.prototype);

    var notSync = new Backbone.Model();
    var foo = new Foo();

    assert.notOk(foo.isSynchronizable(notSync));
    assert.ok(foo.isSynchronizable(new Foo()));
    assert.ok(foo.equals());

    var FooBar = Foo.extend({
      equals: function() {
        return false;
      }
    });
    var fooBar = new FooBar();

    assert.notOk(fooBar.isSynchronizable(notSync));
    assert.ok(fooBar.isSynchronizable(foo));
    assert.ok(fooBar.isSynchronizable(fooBar));
    assert.ok(!fooBar.equals());

  });
});
