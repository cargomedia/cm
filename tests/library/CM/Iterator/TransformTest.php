<?php

class CM_Iterator_TransformTest extends CMTest_TestCase {

    public function testIterate() {
        $list = [
            ['foo' => 1], ['foo' => 2], ['foo' => 3]
        ];

        $iterator = new CM_Iterator_Transform($list, function ($item) {
            return (object) $item;
        });

        $iterator->rewind();
        $item = $iterator->current();
        $this->assertInstanceOf('stdClass', $item);
        $this->assertSame(1, $item->foo);

        $iterator->next();
        $item = $iterator->current();
        $this->assertInstanceOf('stdClass', $item);
        $this->assertSame(2, $item->foo);

        $iterator->next();
        $item = $iterator->current();
        $this->assertInstanceOf('stdClass', $item);
        $this->assertSame(3, $item->foo);
    }
}
