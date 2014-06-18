<?php

class CM_Frontend_JavascriptContainerTest extends CMTest_TestCase {

    public function testCompile() {
        $container = new CM_Frontend_JavascriptContainer();
        $container->append('foo()');
        $container->append('foo();;;;');
        $container->append("foo()\nfoo()");
        $container->prepend('bar()');

        $expected = join("\n", array(
            'bar();',
            'foo();',
            'foo();',
            'foo()',
            'foo()',
        ));
        $this->assertSame($expected, $container->compile());
    }

    public function testCompileWithScope() {
        $container = new CM_Frontend_JavascriptContainer();
        $container->append('foo()');
        $container->append('bar()');
        $this->assertSame("(function () { \n  foo();\n  bar()}).call(my_scope);", $container->compile('my_scope'));
    }
}
