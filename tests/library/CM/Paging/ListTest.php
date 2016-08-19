<?php

class CM_Paging_ListTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructorValidation() {
        $exception = $this->catchException(function () {
            $pagingSource = $this->mockClass('CM_PagingSource_Abstract')->newInstanceWithoutConstructor();
            new CM_Paging_List($pagingSource);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('CM_Paging_List should be instantiated with either an array or CM_PagingSource_Array instance.', $exception->getMessage());
    }

    public function testAccess() {
        $paging = new CM_Paging_List(['foo', 'bar', 'foobar']);
        $items = $paging->getItems();
        $this->assertSame(3, count($items));
        $this->assertSame(['foo', 'bar', 'foobar'], $items);
        $this->assertSame(3, $paging->count());
        $this->assertSame('bar', $paging->getItem(1));
    }

    public function testJsonSerialize() {
        $paging = new CM_Paging_List(['foo', 'bar', 'foobar']);
        $items = $paging->getItems();
        $this->assertSame(['items' => $items], $paging->jsonSerialize());
    }
}
