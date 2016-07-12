define(["CM/Frontend/AbstractTrait"], function() {

  QUnit.module('CM/Frontend/AbstractTrait', {
    beforeEach: function() {
      var FooTrait = _.clone(CM_Frontend_AbstractTrait);
      FooTrait.traitProperties = {
        foo: function() {
          return true;
        },
        bar: CM_Frontend_AbstractTrait.abstractMethod
      };

      this.FooTrait = FooTrait;
    },

    afterEach: function() {
      this.FooTrait = null;
    }
  });

  QUnit.test("isImplementedBy", function(assert) {
    var FooTrait = this.FooTrait;

    assert.notOk(FooTrait.isImplementedBy());
    assert.notOk(FooTrait.isImplementedBy(null));
    assert.notOk(FooTrait.isImplementedBy(1));
    assert.notOk(FooTrait.isImplementedBy(''));
    assert.notOk(FooTrait.isImplementedBy('foo'));
    assert.notOk(FooTrait.isImplementedBy(/foo/));
    assert.notOk(FooTrait.isImplementedBy([]));
    assert.notOk(FooTrait.isImplementedBy({}));

    var Foo = function() {
    };
    Foo.prototype = {};
    Foo.prototype.constructor = Foo;
    assert.notOk(FooTrait.isImplementedBy(new Foo()));
  });

  QUnit.test("applyImplementation", function(assert) {
    var FooTrait = this.FooTrait;

    var Foo = function() {
    };
    Foo.prototype = {};
    Foo.prototype.constructor = Foo;
    assert.notOk(FooTrait.isImplementedBy(new Foo()));
    assert.throws(function() {
      FooTrait.applyImplementation(Foo.prototype);
    }, /bar not implemented./);

    Foo.prototype.bar = function() {
      return true;
    };
    FooTrait.applyImplementation(Foo.prototype);
    var foo = new Foo();
    assert.ok(FooTrait.isImplementedBy(foo));
    assert.ok(foo.bar());
    assert.ok(foo.bar());

    var Bar = function() {
    };
    Bar.prototype = Object.create(Foo.prototype);
    Bar.prototype.constructor = Bar;
    var bar = new Bar();
    assert.ok(FooTrait.isImplementedBy(bar));
    assert.ok(bar.foo());
    assert.ok(bar.bar());
  });
});
