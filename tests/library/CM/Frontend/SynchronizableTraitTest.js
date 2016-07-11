define(["CM/Frontend/AbstractTrait", "CM/Frontend/SynchronizableTrait"], function() {

  QUnit.module('CM/Frontend/SynchronizableTrait');

  QUnit.test("isCompatible", function(assert) {

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

    assert.notOk(foo.isCompatible(notSync));
    assert.ok(foo.isCompatible(new Foo()));
    assert.ok(foo.equals());

    var FooBar = Foo.extend({
      equals: function() {
        return false;
      }
    });
    var fooBar = new FooBar();

    assert.notOk(fooBar.isCompatible(notSync));
    assert.ok(fooBar.isCompatible(foo));
    assert.ok(fooBar.isCompatible(fooBar));
    assert.ok(!fooBar.equals());

  });
});
