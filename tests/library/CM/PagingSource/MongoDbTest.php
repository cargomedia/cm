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

        $sourceEmpty = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 99), array('bar' => 1, '_id' => 0), [['$unwind' => '$bar']]);
        $this->assertSame(0, $sourceEmpty->getCount());
    }

    public function testGetItems() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $items = array();
        for ($i = 0; $i < 7; $i++) {
            $item = array('foo' => $i, 'bar' => array(array('sub' => $i), array('sub' => 'something-else')));
            $items[] = $item;
            $mongodb->insert('my-collection', $item);
        }

        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 5));
        $itemsActual = $source->getItems();
        $this->assertCount(1, $itemsActual);
        $itemActual = $itemsActual[0];
        unset($itemActual['_id']);
        $this->assertEquals($itemActual, $items[5]);

        $source = new CM_PagingSource_MongoDb('my-collection');
        $this->assertEquals($items, Functional\map($source->getItems(), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $this->assertEquals(array_slice($items, 1), \Functional\map($source->getItems(1), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $this->assertEquals(array_slice($items, 1, 2), \Functional\map($source->getItems(1, 2), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $source = new CM_PagingSource_MongoDb('my-collection', null, null, null, ['foo' => -1]);
        $this->assertEquals(array_reverse($items), \Functional\map($source->getItems(), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));
        $this->assertEquals(array_slice(array_reverse($items), 1, 2), \Functional\map($source->getItems(1, 2), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));
    }

    public function testGetItemsAggregation() {
        $mongodb = CM_Service_Manager::getInstance()->getMongoDb();
        $items = array();
        for ($i = 0; $i < 2; $i++) {
            $j = $i*2;
            $item = array('foo' => 12, 'bar' => array(array('sub' => $j), array('sub' => $j+1)));
            $items = array_merge($items, \Functional\map($item['bar'], function ($bar) use ($item, $i) {
                return ['foo' => $item['foo'], 'bar' => $bar];
            }));
            $mongodb->insert('my-collection', $item);
        }
        $source = new CM_PagingSource_MongoDb('my-collection', array('bar.sub' => 2), array('bar' => 1, '_id' => 0), [['$unwind' => '$bar']]);
        $this->assertEquals([['bar' => ['sub' => 2]], ['bar' => ['sub' => 3]]], $source->getItems());

        $source = new CM_PagingSource_MongoDb('my-collection', null, null, [['$unwind' => '$bar']]);
        $result = \Functional\map($source->getItems(), function ($doc) {
            unset($doc['_id']);
            return $doc;
        });
        $this->assertEquals($items, $result);

        $this->assertEquals(array_slice($items, 1), \Functional\map($source->getItems(1), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $this->assertEquals(array_slice($items, 1, 2), \Functional\map($source->getItems(1, 2), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $source = new CM_PagingSource_MongoDb('my-collection', null, null, [['$unwind' => '$bar']], ['bar.sub' => -1]);
        $this->assertEquals(array_reverse($items), \Functional\map($source->getItems(), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        $this->assertEquals(array_slice(array_reverse($items), 2, 1), \Functional\map($source->getItems(2, 1), function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));
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
