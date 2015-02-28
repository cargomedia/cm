<?php

class CM_Util_NamedArgsTest extends CMTest_TestCase {

    public function testInstantiateReflection() {
        $namedArgs = new CM_Util_NamedArgs();
        $results = [
            $namedArgs->instantiateReflection(function () {
                }),
            $namedArgs->instantiateReflection(new ReflectionFunction(function () {
                    })),
            $namedArgs->instantiateReflection(new ReflectionMethod('DateTime', 'format')),
            $namedArgs->instantiateReflection(array(new DateTime(), 'format')),
            $namedArgs->instantiateReflection('sprintf'),
        ];
        $this->assertContainsOnlyInstancesOf('ReflectionFunctionAbstract', $results);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Cannot instantiate reflection
     */
    public function testInstantiateReflectionInvalid() {
        $namedArgs = new CM_Util_NamedArgs();
        $namedArgs->instantiateReflection(array('foo', 'bar'));
    }

    public function testMatchNamedArgs() {
        $function = function ($foo, $bar, $zoo = 'zoo') {
        };
        $args = ['bar' => 'bar', 'foo' => 'foo'];

        $namedArgs = new CM_Util_NamedArgs();
        $finalArgs = $namedArgs->matchNamedArgs($function, $args);
        $this->assertSame(['foo', 'bar', 'zoo'], $finalArgs);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Cannot find value for `foo`
     */
    public function testMatchNamedArgsMissing() {
        $function = function ($foo) {
        };
        $namedArgs = new CM_Util_NamedArgs();
        $namedArgs->matchNamedArgs($function, []);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Unmatched arguments: `bar`
     */
    public function testMatchNamedArgsTooMany() {
        $function = function ($foo) {
        };
        $namedArgs = new CM_Util_NamedArgs();
        $namedArgs->matchNamedArgs($function, ['foo' => 'foo', 'bar' => 'bar']);
    }
}
