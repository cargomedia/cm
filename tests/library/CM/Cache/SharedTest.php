<?php

class CM_Cache_SharedTest extends CMTest_TestCase {

	public function testKeys() {
		$cache = CM_Cache_Shared::getInstance();
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
		$cache = CM_Cache_Shared::getInstance();
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
}
