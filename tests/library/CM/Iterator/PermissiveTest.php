<?php

class CM_Iterator_PermissiveTest extends CMTest_TestCase {

    public function testIterate() {
        $list = [
            ['foo' => 1], ['foo' => 2], ['foo' => 3]
        ];
        $transformer = function (array $item) {
            if (2 == $item['foo']) {
                throw new Exception('Invalid item');
            }
            return (object) $item;
        };
        $iterator = new CM_Iterator_Permissive($list, $transformer);

        $fooValues = [];
        foreach ($iterator as $item) {
            $this->assertInstanceOf('stdClass', $item);
            $fooValues[] = $item->foo;
        }
        $this->assertSame([1, 3], $fooValues);

        $iterator->rewind();
        $item = $iterator->current();
        $this->assertInstanceOf('stdClass', $item);
        $this->assertSame(1, $item->foo);

        $iterator->next();
        $item = $iterator->current();
        $this->assertInstanceOf('stdClass', $item);
        $this->assertSame(3, $item->foo);
    }

    public function test_handleError() {
        $list = [
            ['foo' => 1], ['foo' => 2], ['foo' => 3]
        ];
        $transformer = function (array $item) {
            if (2 == $item['foo']) {
                throw new Exception('Invalid item');
            }
            return (object) $item;
        };

        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Iterator_Permissive $iterator */
        $iterator = $this
            ->getMockBuilder('CM_Iterator_Permissive')
            ->setMethods(['_handleError'])
            ->setConstructorArgs([$list, $transformer])
            ->getMock();

        $iterator
            ->expects($this->once())
            ->method('_handleError')
            ->with(
                $this->equalTo(['foo' => 2]),
                $this->callback(function (Exception $e) {
                    return 'Invalid item' === $e->getMessage();
                })
            );

        $iterator->rewind();
        $iterator->next();
    }
}
