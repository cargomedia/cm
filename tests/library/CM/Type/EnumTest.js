define(["CM/Class/Abstract", "CM/Type/Enum"], function() {

  QUnit.module("CM/Type/Enum");

  QUnit.test("instantiation", function(assert) {

    assert.throws(function() {
      new CM_Type_Enum();
    }, /Enum values are not defined for CM_Type_Enum enum class/);

    var Test_Enum_Foo = CM_Type_Enum.extend({
      _class: 'Test_Enum_Foo'
    }, {
      FOO: 'FOO',
    });

    assert.throws(function() {
      new Test_Enum_Foo();
    }, /Default value in not defined for Test_Enum_Foo enum class/);

    var Test_Enum_Foo = CM_Type_Enum.extend({
      _class: 'Test_Enum_Foo'
    }, {
      FOO: 'FOO',
      getDefaultValue: function() {
        return 'bar';
      }
    });

    assert.throws(function() {
      new Test_Enum_Foo();
    }, /Invalid value `bar` for Test_Enum_Foo enum class/);

    var Test_Enum_FooBar = CM_Type_Enum.extend({
      _class: 'Test_Enum_Foo'
    }, {
      FOO: 'FOO',
      BAR: 1,
      getDefaultValue: function() {
        return 'FOO';
      }
    });

    assert.throws(function() {
      new Test_Enum_FooBar('BAR');
    }, 'Invalid value `BAR` for Test_Enum_Foo enum class');

    assert.equal('FOO', String(new Test_Enum_FooBar()));
    assert.equal('FOO', String(new Test_Enum_FooBar(Test_Enum_FooBar.FOO)));
    assert.equal('1', String(new Test_Enum_FooBar(Test_Enum_FooBar.BAR)));
    assert.equal('FOO', String(new Test_Enum_FooBar('FOO')));
    assert.equal('1', String(new Test_Enum_FooBar(1)));
    assert.equal('FOO', String(new Test_Enum_FooBar({value: Test_Enum_FooBar.FOO})));
  });
});

