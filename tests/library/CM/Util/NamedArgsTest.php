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

    public function testMatchNamedArgsMissing() {
        $function = function ($foo) {
        };
        $namedArgs = new CM_Util_NamedArgs();

        $exception = $this->catchException(function () use ($namedArgs, $function) {
            $namedArgs->matchNamedArgs($function, []);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Cannot find value for parameter', $exception->getMessage());
        $this->assertSame(['parameter' => 'foo'], $exception->getMetaInfo());
    }

    public function testMatchNamedArgsTooMany() {
        $function = function ($foo) {
        };
        $namedArgs = new CM_Util_NamedArgs();
        $exception = $this->catchException(function () use ($namedArgs, $function) {
            $namedArgs->matchNamedArgs($function, ['foo' => 'foo', 'bar' => 'bar', 'numeric']);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Unmatched arguments', $exception->getMessage());
        $this->assertSame(['argNames' => 'bar, 0'], $exception->getMetaInfo());
    }
}
