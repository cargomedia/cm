<?php

class CM_Cache_AbstractTest extends CMTest_TestCase {

    public function testKeys() {
        $cache = $this->mockClass('CM_Cache_Abstract')->newInstance([new CM_Cache_Storage_Runtime(), 30]);
        /** @var CM_Cache_Abstract $cache */

        $cache->set('key1', 'data1');
        $cache->set('key2', 'data2');
        $this->assertEquals('data1', $cache->get('key1'));
        $this->assertEquals('data2', $cache->get('key2'));

        $this->assertFalse($cache->get('keyNonexistent'));

        $cache->delete('key1');
        $this->assertFalse($cache->get('key1'));
        $this->assertEquals('data2', $cache->get('key2'));
    }

    public function testTagged() {
        $cache = $this->mockClass('CM_Cache_Abstract')->newInstance([new CM_Cache_Storage_Runtime(), 30]);
        /** @var CM_Cache_Abstract $cache */

        $cache->setTagged('tag1', 'key1', 'data1');
        $cache->setTagged('tag1', 'key2', 'data2');
        $cache->setTagged('tag2', 'key3', 'data3');
        $this->assertEquals('data1', $cache->getTagged('tag1', 'key1'));
        $this->assertEquals('data2', $cache->getTagged('tag1', 'key2'));
        $this->assertEquals('data3', $cache->getTagged('tag2', 'key3'));

        $this->assertFalse($cache->getTagged('tag1', 'keyNonexistent'));
        $this->assertFalse($cache->getTagged('tagNonexistent', 'key1'));

        $cache->deleteTag('tag1');
        $this->assertFalse($cache->getTagged('tag1', 'key1'));
        $this->assertFalse($cache->getTagged('tag1', 'key2'));
        $this->assertEquals('data3', $cache->getTagged('tag2', 'key3'));
    }

    public function testGetWithGetter() {
        $cache = $this->mockClass('CM_Cache_Abstract')->newInstance([new CM_Cache_Storage_Runtime(), 30]);
        /** @var CM_Cache_Abstract $cache */

        $getterCallCount = 0;

        $getter = function ($key) use (&$getterCallCount) {
            $this->assertSame('foo', $key);
            $getterCallCount++;
            return 12;
        };

        $this->assertSame(12, $cache->get('foo', $getter));
        $this->assertSame(12, $cache->get('foo', $getter));
        $this->assertSame(1, $getterCallCount);
    }

    public function testGetWithGetterReturningFalse() {
        $cache = $this->mockClass('CM_Cache_Abstract')->newInstance([new CM_Cache_Storage_Runtime(), 30]);
        /** @var CM_Cache_Abstract $cache */

        $getterCallCount = 0;

        $getter = function () use (&$getterCallCount) {
            $getterCallCount++;
            return false;
        };

        $this->assertSame(false, $cache->get('foo', $getter));
        $this->assertSame(false, $cache->get('foo', $getter));
        $this->assertSame(2, $getterCallCount);
    }
}
