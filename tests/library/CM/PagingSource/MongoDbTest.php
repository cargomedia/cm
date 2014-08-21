<?php

class CM_PagingSource_MongoDbTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCount() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        for ($i = 0; $i < 7; $i++) {
            $item = array('foo' => 12, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 5));
        $this->assertSame(1, $source->getCount());

        $sourceEmpty = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 99));
        $this->assertSame(0, $sourceEmpty->getCount());
    }

    public function testCountAggregation() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        for ($i = 0; $i < 7; $i++) {
            $item = array('foo' => 12, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 5), array('bar' => 1, '_id' => 0), [['$unwind' => '$bar']]);
        $this->assertSame(2, $source->getCount());

        $sourceEmpty= new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 99), array('bar' => 1, '_id' => 0), [['$unwind' => '$bar']]);
        $this->assertSame(0, $sourceEmpty->getCount());
    }

    public function testGetItems() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $itemsExpected = array();
        for ($i = 0; $i < 7; $i++) {
            $item = array('foo' => 12, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $itemsExpected[] = $item;
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 5));
        $itemsActual = $source->getItems();
        $this->assertCount(1, $itemsActual);
        $itemActual = $itemsActual[0];
        unset($itemActual['_id']);
        $this->assertEquals($itemActual, $itemsExpected[5]);

        $source = new CM_PagingSource_MongoDb('my-collection');
        $this->assertEquals($itemsExpected, Functional\map($source->getItems(), function($doc) {
            unset($doc['_id']);
            return $doc;
        }));
    }

    public function testGetItemsAggregation() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $itemsExpected = array();
        for ($i = 0; $i < 2; $i++) {
            $item = array('foo' => 12, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $itemsExpected = array_merge($itemsExpected, \Functional\map($item['bar'], function($bar) use ($item, $i) {
                return ['foo' => $item['foo'], 'bar' => $bar];
            }));
            $mongodb->insert('my-collection', $item);
        }
        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 1), array('bar' => 1, '_id' => 0), [['$unwind' => '$bar']]);
        $this->assertEquals([['bar' => ['sub' => 1]], ['bar' => ['sub' => 'something-else']]], $source->getItems());

        $source = new CM_PagingSource_MongoDb('my-collection', null, null, [['$unwind' => '$bar']]);
        $result = \Functional\map($source->getItems(), function ($doc) {
            unset($doc['_id']);
            return $doc;
        });
        $this->assertEquals($itemsExpected, $result);
    }

    public function testGetCountOffsetCount() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $itemsExpected = array();
        for ($i = 0; $i < 7; $i++) {
            $item = array('foo' => 12, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $itemsExpected[] = $item;
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb('my-collection');

        $this->assertSame(7, $source->getCount());
        $this->assertSame(4, $source->getCount(3, 2));
        $this->assertSame(5, $source->getCount(2));
    }

    public function testCaching() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $itemExpected = array('foo' => 1);
        $mongodb->insert('my-collection', $itemExpected);

        $source = new CM_PagingSource_MongoDb('my-collection');
        $source->enableCache(600);

        $this->assertSame(1, $source->getCount());

        $itemsActual = $source->getItems();
        $this->assertCount(1, $itemsActual);
        $itemActual = $itemsActual[0];
        unset($itemActual['_id']);
        $this->assertEquals($itemActual, $itemExpected);

        $itemNotCached = array('bar' => 1);
        $mongodb->insert('my-collection', $itemNotCached);

        $itemsActual = $source->getItems();
        $this->assertCount(1, $itemsActual);
        $itemActual = $itemsActual[0];
        unset($itemActual['_id']);
        $this->assertEquals($itemActual, $itemExpected);
    }

    public function testProjection() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongodb->insert('foo', array('firstname' => 'John', 'lastname' => 'Doe'));

        $source = new CM_PagingSource_MongoDb('foo', null, array('firstname' => true));
        $items = $source->getItems();
        $this->assertSame(array('_id', 'firstname'), array_keys($items[0]));

        $source = new CM_PagingSource_MongoDb('foo', null, array('firstname' => false));
        $items = $source->getItems();
        $this->assertSame(array('_id', 'lastname'), array_keys($items[0]));

        $source = new CM_PagingSource_MongoDb('foo', null, array('firstname' => true, '_id' => false));
        $items = $source->getItems();
        $this->assertSame(array('firstname'), array_keys($items[0]));
    }
}
