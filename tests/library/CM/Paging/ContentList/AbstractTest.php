<?php

class CM_Paging_ContentList_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        $this->_getPaging()->removeAll();
    }

    public function testAdd() {
        $paging = $this->_getPaging();
        $paging->add('foo');
        $paging->addMultiple(['bar', 'zoo']);
        $this->assertSame(['bar', 'foo', 'zoo'], $paging->getItems());
        $paging->add('bar');
        $this->assertSame(['bar', 'foo', 'zoo'], $paging->getItems());
    }

    public function testRemove() {
        $paging = $this->_getPaging();
        $paging->addMultiple(['foo', 'bar']);

        $this->assertSame(['bar', 'foo'], $paging->getItems());
        $paging->remove('foo');
        $this->assertSame(['bar'], $paging->getItems());

        $paging->remove('foo');
        $this->assertSame(['bar'], $paging->getItems());
    }

    public function testRemoveAll() {
        $paging = $this->_getPaging();
        $paging->addMultiple(['foo', 'bar', 'zoo']);
        $this->assertSame(3, $paging->getCount());
        $paging->removeAll();
        $this->assertSame(0, $paging->getCount());
    }

    public function testContains() {
        $paging = $this->_getPaging();
        $paging->addMultiple(['foo']);
        $this->assertTrue($paging->contains('foo'));
        $this->assertTrue($paging->contains('FoO'));
        $this->assertFalse($paging->contains('foo-suffixed'));

        $this->assertTrue($paging->contains('foo-suffixed', '/^\Q$item\E/'));
    }

    /**
     * @return CM_Paging_ContentList_Abstract|\Mocka\AbstractClassTrait
     */
    private function _getPaging() {
        return $this->mockObject('CM_Paging_ContentList_Abstract', [1]);
    }
}
