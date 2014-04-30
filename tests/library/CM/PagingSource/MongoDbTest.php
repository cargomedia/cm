<?php

class CM_PagingSource_MongoDbTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCount() {
        $mongodb = CM_Services::getInstance()->getMongoDb();
        for ($i = 0; $i < 7; $i++) {
            $item = ['foo' => 12, 'bar' => [['sub' => $i], ['sub' => 'something-else']]];
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb(array('recipients'), 'my-collection', array('bar.sub' => 5));
        $this->assertSame(1, $source->getCount());

        $sourceEmpty = new CM_PagingSource_MongoDb(array('recipients'), 'my-collection', array('bar.sub' => 99));
        $this->assertSame(0, $sourceEmpty->getCount());
    }

    public function testGetItems() {
        $mongodb = CM_Services::getInstance()->getMongoDb();
        $itemsExpected = array();
        for ($i = 0; $i < 7; $i++) {
            $item = ['foo' => 12, 'bar' => [['sub' => $i], ['sub' => 'something-else']]];
            $itemsExpected[] = $item;
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb(array('recipients'), 'my-collection', array('bar.sub' => 5));
        $itemsActual = $source->getItems();
        $this->assertCount(1, $itemsActual);
        $itemActual = $itemsActual[0];
        unset($itemActual['_id']);
        $this->assertEquals($itemActual, $itemsExpected[5]);
    }

    public function testGetItemsOffsetCount() {
        $mongodb = CM_Services::getInstance()->getMongoDb();
        $itemsExpected = array();
        for ($i = 0; $i < 7; $i++) {
            $item = ['foo' => 12, 'bar' => [['sub' => $i], ['sub' => 'something-else']]];
            $itemsExpected[] = $item;
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb(array('messages'), 'my-collection');

        $this->assertSame(7, $source->getCount());
        $this->assertSame(2, $source->getCount(3, 2));
        $this->assertSame(5, $source->getCount(2));
    }
}
